<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('America/Panama');
    }

    public function register(Request $request)
    {

        // Recoger los datos del usuario por post
        //
        $json = $request->input('json', null);
        // json_decode transforma el json a un objeto si no le pasamos el true
        $params = json_decode($json);
        // json_decode con el true transforma el json a un array
        $params_array = json_decode($json, true);

        // Valida si se recibieron los datos
        if (! empty($params) && ! empty($params_array)) {

            // Limpiar datos - limpia los espacios de los puntos extremos
            $params_array = array_map('trim', $params_array);

            // Validar Datos
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha:ascii',
                'surname' => 'required|alpha:ascii',
                'email' => 'required|email|unique:users', // unique:users comprueba si el usuario existe
                'password' => 'required',
            ]);

            if ($validate->fails()) {
                // Validacion a fallado
                $data = [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors(),
                ];
            } else {
                // Validacion pasada correctamente

                // Cifrar la contraseña
                $pwd = hash('sha256', $params->password);

                // Comprobar si el usuario existe(duplicado)
                // Esta validacion se realiza en el Validator donde le indicamos que para el dato
                // recibido de email valide con el ORM que en la tabla de usuarios ese email sea único

                // Crear el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                $user->save();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado correctamente',
                    'user' => $user,
                ];
            }

        } else {
            // Envio incorrecto de los datos
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos enviados no son los correctos',
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $jwtAuth = new JwtAuth();

        // Recibir datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        // Validar los datos
        if (! empty($params) && ! empty($params_array)) {

            // Limpiar datos - limpia los espacios de los puntos extremos
            $params_array = array_map('trim', $params_array);

            // Validar Datos
            $validate = Validator::make($params_array, [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validate->fails()) {
                // Validacion a fallado
                $signup = [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha logueado',
                    'errors' => $validate->errors(),
                ];
            } else {
                // Validacion pasada correctamente

                $email = $params->email;
                // Cifrar la contraseña
                $pwd = hash('sha256', $params->password);

                $signup = $jwtAuth->signup($email, $pwd);
                if (! empty($params->getToken)) {
                    $signup = $jwtAuth->signup($email, $pwd, true);
                }

            }

        } else {
            // Envio incorrecto de los datos
            $signup = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Favor introduccir los datos correctos',
            ];
        }
        // Cifrar la contraseña
        // Devolver token o datos

        return response()->json($signup, $signup['code']);
    }

    public function update(Request $request)
    {

        // Comprobar si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($checkToken && ! empty($params_array)) {
            // Recoger los datos por post

            // Sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);
            // Validar los datos
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha:ascii',
                'surname' => 'required|alpha:ascii',
                'email' => 'required|email|unique:users'.$user->sub, // unique:users comprueba si el usuario existe
            ]);

            // Quitar los campos  que no quiero Actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remeber_token']);

            // Actualizar usuario
            $user_update = User::where('id', $user->sub)->update($params_array);
            // Devolver array con los datos
            $data = [
                'status' => 'success',
                'code' => 200,
                'user' => $user,
                'changes' => $params_array,
            ];

            // Limpia la carpeta de users de las imagenes que ya no esten en la base de datos
            $cleanUsersFolder = function ($file): void {
                $user_check = User::where('image', $file)->first();

                if (! is_object($user_check)) {
                    Storage::disk('users')->delete($file);
                }
            };

            $list = Storage::disk('users')->allfiles();

            if ($list) {
                array_map($cleanUsersFolder, $list);
            }

        } else {
            // Mensaje de error
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Usuario no esta autentificado',
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request)
    {
        // Recoger datos de la peticion
        $image = $request->file('file0');

        $validate = Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif',
        ]);

        if (!$image || $validate->fails()) {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Error al subir la imagen',
                'detail' => $image,
            ];
        } else {
            // Guardar imagen
            $token = $request->header('Authorization');
            $jwtAuth = new JwtAuth();
            $user = $jwtAuth->checkToken($token, true);
            $image_name = 'avatar_'.$user->sub.'_'.time().'.'.$image->getClientOriginalExtension();
            Storage::disk('users')->put($image_name, File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name,
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {
        $isset = Storage::disk('users')->exists($filename);

        if ($isset) {
            $file = Storage::disk('users')->get($filename);

            return new response($file, 200);
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Imagen no existe',
            ];

            return response()->json($data, $data['code']);
        }

    }

    public function detail($id)
    {

        $user = User::find($id);

        if (is_object($user)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'user' => $user,
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe',
            ];
        }

        return response()->json($data, $data['code']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('America/Panama');
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index()
    {
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories,
        ]);
    }

    public function show($id)
    {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $category,
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La categoria no existe',
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request)
    {
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (! empty($params_array)) {
            // Validar los datos
            $validate = Validator::make($params_array, [
                'name' => 'required',
            ]);

            // Guardar la categoria
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la categoria',
                ];
            } else {
                $category = new Category();
                $category->name = ucfirst($params_array['name']);
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Categoria << '.ucfirst($params_array['name']).' >> Se ha gurdado correctamente',
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria',
            ];
        }

        // Devolver el resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request)
    {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (! empty($params_array) && ! empty($id)) {
            // Validar los datos
            $validate = Validator::make($params_array, [
                'name' => 'required',
            ]);

            // Guardar la categoria
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha actualizado la categoria',
                ];
            } else {
                unset($params_array['id']);
                unset($params_array['created_at']);
                $params_array['name'] = ucfirst($params_array['name']);

                $category = Category::where('id', $id)->update($params_array);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Categoria actualizada correctamente',
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se estan recibiendo los datos esperados',
            ];
        }

        // Devolver el resultado
        return response()->json($data, $data['code']);

    }

    public function destroy($id, Request $request)
    {
        // Conseguir el post que deseamos eliminar
        $category = Category::find($id);
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $userToken = $jwtAuth->checkToken($token, true);

        $user = User::find($userToken->sub);

        if (! empty($category) && $user->role === 'Admin') {
            $category->delete();
            // Devolver respuesta
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $category,
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'Error',
                'message' => 'La categoria no existe o no tiene autorización para eliminarlo',
            ];
        }

        return response()->json($data, $data['code']);
    }
}

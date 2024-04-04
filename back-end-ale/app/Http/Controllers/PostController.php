<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('America/Panama');
        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
            'getImage',
            'getPostByCategory',
            'getPostByUser',
        ]]);
    }

    public function index()
    {
        $posts = Post::all();

        if (! $posts->isEmpty()) {
            $posts->load('category');
            $data = [
                'code' => 200,
                'status' => 'success',
                'posts' => $posts,
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No hay registrado ningun post',
            ];
        }

        return response()->json($data, $data['code']);

    }

    public function show($id)
    {
        $post = Post::find($id);

        if (is_object($post)) {
            $post->load('category')
                ->load('user');
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post,
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El post no existe',
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request)
    {
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (! empty($params_array)) {
            // Validar los datos
            $user = $this->getIdentity($request);

            $validate = Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required',
            ]);

            // Guardar la categoria
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la post',
                ];
            } else {
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;

                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Post se ha gurdado correctamente',
                ];

                // Limpia la carpeta de users de las imagenes que ya no esten en la base de datos
                $cleanImageFolder = function ($file): void {
                    $post_check = Post::where('image', $file)->first();

                    if (! is_object($post_check)) {
                        Storage::disk('images')->delete($file);
                    }
                };

                $list = Storage::disk('images')->allfiles();

                if ($list) {
                    array_map($cleanImageFolder, $list);
                }
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna post',
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
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required',
            ]);

            // Guardar la categoria
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha actualizado el post',
                ];
            } else {
                unset($params_array['id']);
                unset($params_array['created_at']);
                unset($params_array['user_id']);
                unset($params_array['user']);

                $user = $this->getIdentity($request);

                $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->update($params_array);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Post actualizado correctamente',
                    'post' => $post,
                    'change' => $params_array,
                ];

                $cleanImageFolder = function ($file): void {
                    $post_check = Post::where('image', $file)->first();

                    if (! is_object($post_check)) {
                        Storage::disk('images')->delete($file);
                    }
                };

                $list = Storage::disk('images')->allfiles();

                if ($list) {
                    array_map($cleanImageFolder, $list);
                }

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
        $post = Post::find($id);

        $userToken = $this->getIdentity($request);

        $user = User::find($userToken->sub);

        if (! empty($post) && ($user->id === $post->user_id || $user->role === 'ROLE_ADMIN')) {
            $post->delete();
            // Devolver respuesta
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post,
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'Error',
                'message' => 'El Post no existe o no tiene autorización para eliminarlo',
            ];
        }

        return response()->json($data, $data['code']);
    }

    private function getIdentity($request)
    {

        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function upload(Request $request)
    {
        // Recoger datos de la peticion
        $image = $request->file('file0');

        $validate = Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif',
        ]);

        if (! $image || $validate->fails()) {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Error al subir la imagen',
            ];
        } else {
            // Guardar imagen
            $image_name = time().'.'.$image->getClientOriginalExtension();
            Storage::disk('images')->put($image_name, File::get($image));

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
        $isset = Storage::disk('images')->exists($filename);

        if ($isset) {
            $file = Storage::disk('images')->get($filename);

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

    public function getPostByCategory($id)
    {
        $posts = Post::where('category_id', $id)->get();

        if (! $posts->isEmpty()) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'posts' => $posts,
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No hay post para esta categoria',
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function getPostByUser($id)
    {
        $posts = Post::where('user_id', $id)->get();

        if (! $posts->isEmpty()) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'posts' => $posts,
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no ha realizado ningun posteo',
            ];
        }

        return response()->json($data, $data['code']);
    }
}

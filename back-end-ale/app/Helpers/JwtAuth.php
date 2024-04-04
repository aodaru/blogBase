<?php

namespace App\Helpers;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuth
{
    public $key;

    public function __construct()
    {
        $this->key = 'La llave super secreta que vamos a utilizar para generar el token-1231231234239';
    }

    public function signup($email, $password, $getToken = null)
    {
        // Buscar si existe el usuario con sus credenciales
        $user = User::where([
            'email' => $email,
            'password' => $password,
        ])->first();

        // Comprobar si son correctas(objeto)
        $signup = false;
        if (is_object($user)) {
            $signup = true;
        }

        // Generar el token con los datos del usuario identificado
        if ($signup) {
            $token = [
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'description' => $user->description,
                'image' => $user->image,
                'iat' => time(),
                'exp' => time() + (3600),
            ];

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decode = JWT::decode($jwt, new Key($this->key, 'HS256'));

            // Devolver los datos decodificados o el token, en funcion de un parametro
            if (is_null($getToken)) {
                $data = $jwt;
                $data = [
                    'status' => 'Ok',
                    'code' => 200,
                    'message' => $jwt,
                ];
            } else {
                $data = $decode;
                $data = [
                    'status' => 'Ok',
                    'code' => 200,
                    'message' => $decode,
                ];
            }

        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Login incorrecto',
            ];
        }

        return $data;
    }

    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;

        try {
            $jwt = str_replace('"', '', $jwt);
            $decode = JWT::decode($jwt, new Key($this->key, 'HS256'));
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if (! empty($decode) && is_object($decode) && isset($decode->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decode;
        }

        return $auth;

    }
}

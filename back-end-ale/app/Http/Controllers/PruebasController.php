<?php

namespace App\Http\Controllers;
use App\Models\Post;

use Illuminate\Http\Request;

class PruebasController extends Controller
{
    public function index(){
        $titulo = 'Animales';
        $animales = ['Perro','Gato','Tigre'];

        return view('pruebas.index', array(
            'titulo' => $titulo,
            'animales' => $animales
        ));
    }

    public function testOrm(){
        $posts = Post::all();
        foreach ($posts as $post){
        var_dump($post->title);
    }
        die();
    }
}

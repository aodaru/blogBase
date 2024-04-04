<?php

use App\Http\Middleware\ApiAuthMiddleware;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// Rutas de usuarios
Route::post('/api/login',[UserController::class,'login'])->name('user.login');
Route::post('/api/register',[UserController::class, 'register'])->name('user.register');
Route::put('/api/user/update',[UserController::class, 'update'])->name('user.update');
Route::post('/api/user/upload',[UserController::class, 'upload'])->middleware(ApiAuthMiddleware::class)->name('user.upload');
Route::get('/api/user/avatar/{filename}',[UserController::class, 'getImage'])->name('user.avatar');
Route::get('/api/user/detail/{id}',[UserController::class, 'detail'])->name('user.detail');

// Rutas del controlador de categorias

Route::resource('/api/category', CategoryController::class);

// Rutas del controlador post
Route::resource('/api/post', PostController::class);
Route::post('/api/post/upload',[PostController::class, 'upload'])->middleware(ApiAuthMiddleware::class);
Route::get('/api/post/image/{filename}',[PostController::class, 'getImage']);
Route::get('/api/post/category/{id}',[PostController::class, 'getPostByCategory']);
Route::get('/api/post/user/{id}',[PostController::class, 'getPostByUser']);

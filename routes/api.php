<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MusicController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//auth
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/register', [UserController::class, 'store'])->name('register');

//user 
Route::get('/user', [UserController::class, 'index']);
Route::get('/user/{id}', [UserController::class, 'show']);
Route::put('/user/{id}', [UserController::class, 'update']);
Route::delete('/user/{id}', [UserController::class, 'destroy']);
Route::get('/user', [UserController::class, 'count']);

//master music
Route::get('/musicDatatable', [MusicController::class, 'datatable']);
// Route::post('/music', [MusicController::class, 'store']);
Route::get('/music/{id}', [MusicController::class, 'show']);
Route::put('/music/{id}', [MusicCOntroller::class, 'update']);
Route::delete('/produk/{id}', [MusicController::class, 'destroy']);
Route::post('upload-cover', [MusicController::class, 'uploadCover']);
Route::post('upload-audio', [MusicController::class, 'uploadAudio']);
Route::post('music',       [MusicController::class, 'store']);


Route::get('/songs', [MusicController::class, 'index']);


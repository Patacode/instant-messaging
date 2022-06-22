<?php

use Illuminate\Support\Facades\Route;

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

Auth::routes(); // routes for authentication (login, logout, register...)

// root (same as home page)
Route::get('/', [App\Http\Controllers\UserController::class, 'index']);

// home page (contact and user lists)
Route::get('/home', [App\Http\Controllers\UserController::class, 'index']);

// process get requests for the chat (messages...)
Route::get('/chat/{action}/{param}', [App\Http\Controllers\ChatsController::class, 'processChatRequest']);

// process post requests for the chat (adding messages...)
Route::post('/pchat/{action}', [App\Http\Controllers\ChatsController::class, 'processChatPostRequest']);

// process get requests for the users (user informations...)
Route::get('/user/{action}/{id?}', [App\Http\Controllers\UserController::class, 'processUserRequest']);

// process post requests for the users (deleting or adding a user...)
Route::post('/puser/{action}/{param?}', [App\Http\Controllers\UserController::class, 'processUserPostRequest']);


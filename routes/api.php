<?php

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


//Route::resource('users', 'API\RegisterController',['only' => ['index','show']]);
Route::post('login', 'API\RegisterController@login');

Route::middleware('auth:api')->group( function () {
    //Route::resource('users', 'API\RegisterController',['except' => ['index', 'show']]);
    Route::resource('users', 'API\RegisterController');
    Route::resource('projects', 'API\ProjectController');
});
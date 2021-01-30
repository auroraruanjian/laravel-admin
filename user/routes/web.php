<?php

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
    return view('index');
});

Auth::routes();

Route::get('login/wechat','Auth\LoginController@wechat');
Route::get('login/wechatCallback','Auth\LoginController@wechatCallback');

// Route::get('/user/info', 'UserController@getInfo');
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
Auth::routes();

Route::get('/', function () {
    $os = new apanly\BrowserDetector\Os();
    $os_name = $os->getIsmobile();

    if ($os_name) {
        return '移动端开发中....';
        //return view('index-m');
    }
    return view('index');
});

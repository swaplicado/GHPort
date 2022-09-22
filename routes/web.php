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
    // return view('auth.login');
    return redirect(route('login'));
});

Auth::routes();

Route::middleware(['auth'])->group( function () {
    Route::get('/logout', 'Auth\LoginController@logout');
    Route::get('home', 'Pages\HomeController@index')->name('home');
    Route::get('orgChart', 'Pages\OrgChartController@index')->name('orgChart');
});
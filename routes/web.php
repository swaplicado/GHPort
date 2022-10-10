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

Route::middleware(['auth', 'menu'])->group( function () {
    Route::get('/logout', 'Auth\LoginController@logout');
    Route::get('home', 'Pages\HomeController@index')->name('home');
    Route::get('orgChart', 'Adm\OrgChartController@index')->name('orgChart');
    Route::get('assignArea', 'Adm\OrgChartController@assignArea')->name('assignArea');
    Route::post('updateArea', 'Adm\OrgChartController@updateAssignArea')->name('update_assignArea');

    Route::get('myEmplVacations', 'Pages\EmployeesVacationsController@employeesDirectIndex')->name('myEmplVacations');
    Route::get('allEmplVacations', 'Pages\EmployeesVacationsController@allEmployeesIndex')->name('allEmplVacations');
    Route::get('getlEmployees/{OrgjobId}', 'Pages\EmployeesVacationsController@getDirectEmployees')->name('getlEmployees');
    
    Route::get('requestVacations', 'Pages\RequestVacationsController@index')->name('RequestVacations');
});
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
    Route::get('allVacations', 'Pages\EmployeesVacationsController@allVacationsIndex')->name('allVacations');
    Route::post('allVacations/getPeriod', 'Pages\EmployeesVacationsController@allVacations')->name('allVacations_getPeriod');
    
    Route::get('myVacations', 'Pages\myVacationsController@index')->name('myVacations');
    Route::post('myVacations/setRequestVac', 'Pages\myVacationsController@setRequestVac')->name('myVacations_setRequestVac');
    Route::post('myVacations/updateRequestVac', 'Pages\myVacationsController@updateRequestVac')->name('myVacations_updateRequestVac');
    Route::post('myVacations/filterYear', 'Pages\myVacationsController@filterYear')->name('myVacations_filterYear');
    Route::post('myVacations/deleteRequest', 'Pages\myVacationsController@deleteRequestVac')->name('myVacations_delete_requestVac');
    Route::post('myVacations/sendRequest', 'Pages\myVacationsController@sendRequestVac')->name('myVacations_send_requestVac');
    Route::post('myVacations/checkMail', 'Pages\myVacationsController@checkMail')->name('myVacations_checkMail');
    
    Route::get('requestVacations/{id?}', 'Pages\requestVacationsController@index')->name('requestVacations');
    Route::post('requestVacations/accept', 'Pages\requestVacationsController@acceptRequest')->name('requestVacations_acceptRequest');
    Route::post('requestVacations/reject', 'Pages\requestVacationsController@rejectRequest')->name('requestVacations_rejectRequest');
    Route::post('requestVacations/filterYear', 'Pages\requestVacationsController@filterYear')->name('requestVacations_filterYear');
    Route::post('requestVacations/checkMail', 'Pages\requestVacationsController@checkMail')->name('requestVacations_checkMail');
});
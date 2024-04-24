<?php

use Illuminate\Http\Request;

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

Route::post('login', 'api\\AuthController@login');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'auth:api'], function() {
    Route::post('syncUser', [
        'uses' => 'api\\apiGlobalUsersController@syncUser'
    ]);

    Route::post('updateGlobal', [
        'uses' => 'api\\apiGlobalUsersController@updateGlobalPassword'
    ]);

    Route::post('insertUserVsSystem', [
        'uses' => 'api\\apiGlobalUsersController@insertUserVsSystem'
    ]);
});

Route::group(['middleware' => 'auth:api'], function() {
    Route::get('getPendingUser', [
        'uses' => 'api\\GlobalComunicationController@getPendingUser'
    ]);
});

Route::get('getUsersFromGU', [
    'uses' => 'Sys\\SyncController@getUsersFromGU'
]);
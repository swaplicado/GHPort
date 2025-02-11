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
Route::get('validate-token', 'api\\AuthController@isTokenValid');

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


/**
 * Grupo de rutas para App PGH
 * Prefijo: api/
 * middleware: auth:api
 */
 Route::group(['middleware' => 'authApi:api'], function() {
    Route::get('events', [
        'uses' => 'api\\AppPghController@events'
    ]);
    Route::get('incidents', [
        'uses' => 'api\\AppPghController@incidents'
    ]);
    Route::get('permissions', [
        'uses' => 'api\\AppPghController@permissions'
    ]);
    Route::put('authorization', [
        'uses' => 'api\\AppPghController@authorization'
    ]);
    Route::post('incidents/status', [
        'uses' => 'api\\AppPghController@checkIncidentsStatus'
    ]);
    Route::post('incidents/is-authorized', [
        'uses' => 'api\\AppPghController@incidentIsAuthorized'
    ]);
    Route::post('incidents/is-rejected', [
        'uses' => 'api\\AppPghController@incidentIsRejected'
    ]);
    Route::post('incidents/authorize', [
        'uses' => 'api\\AppPghController@authorizeIncidents'
    ]);
    Route::post('incidents/reject', [
        'uses' => 'api\\AppPghController@rejectIncidents'
    ]);
    Route::get('logout', [
        'uses' => 'api\\AuthController@logout'
    ]);
    Route::post('createAndSendIncident', [
        'uses' => 'api\\AppPghController@createAndSendIncident'
    ]);
    Route::get('employees', [
        'uses' => 'api\\AppPghController@employees'
    ]);
});
Route::get('event-types', [
    'uses' => 'api\\AppPghController@eventsType'
]);
Route::get('holidays', [
    'uses' => 'api\\AppPghController@holidays'
]);
Route::post('loginBridge', 'api\\AuthController@loginBridge');
Route::post('logoutBridge', 'api\\AuthController@logoutBridge');
Route::get('getDirectManager/{id}', 'api\\apiGlobalUsersController@getDirectManager');

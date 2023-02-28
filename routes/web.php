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

Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');

Route::middleware(['auth', 'menu'])->group( function () {
    Route::get('/logout', 'Auth\LoginController@logout');
    Route::get('home', 'Pages\HomeController@index')->name('home');
    Route::get('orgChart', 'Adm\OrgChartController@index')->name('orgChart');
    // Route::get('orgChart/altIndex', 'Adm\OrgChartController@altIndex')->name('orgChart_altIndex');
    Route::get('assignArea', 'Adm\OrgChartController@assignArea')->name('assignArea');
    Route::post('updateArea', 'Adm\OrgChartController@updateAssignArea')->name('update_assignArea');
    Route::get('profile', 'Pages\profileController@index')->name('profile');
    Route::post('profile/update', 'Pages\profileController@updatePass')->name('profile_update');

    Route::get('myEmplVacations', 'Pages\EmployeesVacationsController@employeesDirectIndex')->name('myEmplVacations');
    Route::post('myEmplVacations/getVacationHistory', 'Pages\EmployeesVacationsController@getVacationHistory')->name('myEmplVacations_getVacationHistory');
    Route::post('myEmplVacations/hiddeHistory', 'Pages\EmployeesVacationsController@hiddeHistory')->name('myEmplVacations_hiddeHistory');

    Route::get('allEmplVacations', 'Pages\EmployeesVacationsController@allEmployeesIndex')->name('allEmplVacations');
    Route::post('allEmplVacations/getVacationHistory', 'Pages\EmployeesVacationsController@getVacationHistory')->name('allEmplVacations_getVacationHistory');
    Route::post('allEmplVacations/hiddeHistory', 'Pages\EmployeesVacationsController@hiddeHistory')->name('allEmplVacations_hiddeHistory');
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
    Route::post('myVacations/getEmpApplicationsEA', 'Pages\myVacationsController@getEmpApplicationsEA')->name('myVacations_getEmpApplicationsEA');
    Route::post('myVacations/getHistory', 'Pages\myVacationsController@getMyVacationHistory')->name('myVacations_getMyVacationHistory');
    Route::post('myVacations/hiddeHistory', 'Pages\myVacationsController@hiddeHistory')->name('myVacations_hiddeHistory');
    Route::post('myVacations/getlDays', 'Pages\myVacationsController@getlDays')->name('myVacations_getlDays');
    
    Route::get('requestVacations/{id?}', 'Pages\requestVacationsController@index')->name('requestVacations');
    Route::post('requestVacations/accept', 'Pages\requestVacationsController@acceptRequest')->name('requestVacations_acceptRequest');
    Route::post('requestVacations/reject', 'Pages\requestVacationsController@rejectRequest')->name('requestVacations_rejectRequest');
    Route::post('requestVacations/filterYear', 'Pages\requestVacationsController@filterYear')->name('requestVacations_filterYear');
    Route::post('requestVacations/checkMail', 'Pages\requestVacationsController@checkMail')->name('requestVacations_checkMail');
    Route::post('requestVacations/getEmpApplicationsEA', 'Pages\requestVacationsController@getEmpApplicationsEA')->name('requestVacations_getEmpApplicationsEA');
    Route::post('requestVacations/getDataManager', 'Pages\requestVacationsController@getDataManager')->name('requestVacations_getDataManager');
    Route::post('requestVacations/getlDays', 'Pages\requestVacationsController@getlDays')->name('requestVacations_getlDays');

    Route::get('mailLog', 'Pages\MailsLogscontroller@index')->name('mailLog');
    Route::post('mailLog/sendMail', 'Pages\MailsLogscontroller@sendMail')->name('mailLog_sendMail');
    Route::post('mailLog/delete', 'Pages\MailsLogscontroller@delete')->name('mailLog_delete');
    Route::post('mailLog/filterYear', 'Pages\MailsLogscontroller@filterYear')->name('mailLog_filterYear');

    Route::get('vacationPlans', 'Adm\VacationPlansController@index')->name('vacationPlans');
    Route::post('vacationPlans/saveVacationPlan', 'Adm\VacationPlansController@saveVacationPlan')->name('vacationPlans_save');
    Route::post('vacationPlans/showVacationPlan', 'Adm\VacationPlansController@getVacationPlanDays')->name('vacationPlans_show');
    Route::post('vacationPlans/deleteVacationPlan', 'Adm\VacationPlansController@deleteVacationPlan')->name('vacationPlans_delete');
    Route::post('vacationPlans/updateVacationPlan', 'Adm\VacationPlansController@updateVacationPlan')->name('vacationPlans_update');
    Route::post('vacationPlans/getUsersAssigned', 'Adm\VacationPlansController@getUsersAssigned')->name('vacationPlans_getUsersAssigned');
    Route::post('vacationPlans/saveAssignVacationPlan', 'Adm\VacationPlansController@saveAssignVacationPlan')->name('vacationPlans_saveAssignVacationPlan');

    Route::get('bitacoras', 'Adm\logsController@index')->name('bitacoras');
    Route::get('bitacoras/VacationPlanDaysLogs', 'Adm\logsController@indexVacationPlanDaysLogs')->name('bitacoras_VacationPlanDaysLogs');
    Route::get('bitacoras/VacationUsersLogs', 'Adm\logsController@indexVacationUsersLogs')->name('bitacoras_VacationUsersLogs');
    Route::get('bitacoras/AdmissionUserLogs', 'Adm\logsController@indexAdmissionUserLogs')->name('bitacoras_AdmissionUserLogs');
    Route::get('bitacoras/ApplicationLogs', 'Adm\logsController@indexApplicationLogs')->name('bitacoras_ApplicationLogs');
    Route::post('bitacoras/getApplicationLogsData', 'Adm\logsController@getApplicationLogsData')->name('bitacoras_getApplicationLogsData');

    Route::get('specialSeasonTypes', 'Pages\SpecialSeasonTypesController@index')->name('specialSeasonTypes');
    Route::post('specialSeasonTypes/save', 'Pages\SpecialSeasonTypesController@saveSeasonType')->name('specialSeasonTypes_save');
    Route::post('specialSeasonTypes/update', 'Pages\SpecialSeasonTypesController@updateSeasonType')->name('specialSeasonTypes_update');
    Route::post('specialSeasonTypes/delete', 'Pages\SpecialSeasonTypesController@deleteSeasonType')->name('specialSeasonTypes_delete');

    Route::get('seasons', 'Pages\SpecialSeasonsController@index')->name('specialSeasons');
    Route::post('seasons/getSpecialSeason', 'Pages\SpecialSeasonsController@getSpecialSeason')->name('specialSeasons_getSpecialSeason');
    Route::post('seasons/saveSpecialSeason', 'Pages\SpecialSeasonsController@saveSpecialSeason')->name('specialSeasons_saveSpecialSeason');
    Route::post('seasons/copyToNextYear', 'Pages\SpecialSeasonsController@copyToNextYear')->name('specialSeasons_copyToNextYear');

    Route::post('getEmployeeData', 'Pages\vacationManagementController@getEmployeeData')->name('vacationManagement_getEmployeeData');
    Route::get('getDirectEmployees', 'Pages\vacationManagementController@getDirectEmployees')->name('vacationManagement_getDirectEmployees');
    Route::get('getAllEmployees', 'Pages\vacationManagementController@getAllEmployees')->name('vacationManagement_getAllEmployees');

    Route::get('delegation', 'Pages\delegationController@index')->name('delegation');
    Route::post('delegation/saveDelegation', 'Pages\delegationController@saveDelegation')->name('delegation_saveDelegation');
    Route::post('delegation/updateDelegation', 'Pages\delegationController@updateDelegation')->name('delegation_updateDelegation');
    Route::post('delegation/deleteDelegation', 'Pages\delegationController@deleteDelegation')->name('delegation_deleteDelegation');
    Route::post('delegation/setDelegation', 'Pages\delegationController@setDelegation')->name('delegation_setDelegation');
    Route::post('delegation/recoverDelegation', 'Pages\delegationController@recoverDelegation')->name('delegation_recoverDelegation');

    Route::get('specialVacations', 'Pages\specialVacationsController@index')->name('specialVacations');
    Route::post('specialVacations/setRequestVac', 'Pages\specialVacationsController@setRequestVac')->name('specialVacations_setRequestVac');
    Route::post('specialVacations/updateRequestVac', 'Pages\specialVacationsController@updateRequestVac')->name('specialVacations_updateRequestVac');
    Route::post('specialVacations/getEmpApplicationsEA', 'Pages\specialVacationsController@getEmpApplicationsEA')->name('specialVacations_getEmpApplicationsEA');
    Route::post('specialVacations/deleteRequestVac', 'Pages\specialVacationsController@deleteRequestVac')->name('specialVacations_deleteRequestVac');
    Route::post('specialVacations/filterYear', 'Pages\specialVacationsController@filterYear')->name('specialVacations_filterYear');
    Route::post('specialVacations/sendRequestVac', 'Pages\specialVacationsController@sendRequestVac')->name('specialVacations_sendRequestVac');
    
    Route::get('jobVsOrgChartJob', 'Adm\jobVsOrgChartJobController@index')->name('jobVsOrgChartJob');
    Route::post('jobVsOrgChartJob/update', 'Adm\jobVsOrgChartJobController@update')->name('jobVsOrgChartJob_update');

    Route::get('tutorial', 'TutorialController@index')->name('tutorialUsuarios');
    Route::get('lideres', 'TutorialController@lideres')->name('tutorialLideres');

    Route::get('specialType', 'Adm\SpecialTypeController@index')->name('specialType');
    Route::post('specialType/save', 'Adm\SpecialTypeController@save')->name('specialType_save');
    Route::post('specialType/update', 'Adm\SpecialTypeController@update')->name('specialType_update');
    Route::post('specialType/delete', 'Adm\SpecialTypeController@delete')->name('specialType_delete');

    Route::get('SpecialTypeVsOrgChart', 'Adm\SpecialTypeVsOrgChartController@index')->name('SpecialTypeVsOrgChart');
    Route::post('SpecialTypeVsOrgChart/save', 'Adm\SpecialTypeVsOrgChartController@save')->name('SpecialTypeVsOrgChart_save');
    Route::post('SpecialTypeVsOrgChart/update', 'Adm\SpecialTypeVsOrgChartController@update')->name('SpecialTypeVsOrgChart_update');
    Route::post('SpecialTypeVsOrgChart/delete', 'Adm\SpecialTypeVsOrgChartController@delete')->name('SpecialTypeVsOrgChart_delete');
});
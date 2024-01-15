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
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    // return view('auth.login');
    return redirect(route('login'));
});

Auth::routes();

Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');

Route::get('login/{idRoute?}/{idApp?}', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login/{idRoute?}/{idApp?}', 'Auth\LoginController@login')->name('login');

Route::middleware(['auth', 'menu'])->group( function () {
    Route::get('/logout', 'Auth\LoginController@logout');
    Route::get('home', 'Pages\HomeController@index')->name('home');
    Route::get('orgChart', 'Adm\OrgChartController@index')->name('orgChart');
    // Route::get('orgChart/altIndex', 'Adm\OrgChartController@altIndex')->name('orgChart_altIndex');
    Route::get('assignArea', 'Adm\OrgChartController@assignArea')->name('assignArea');
    Route::post('createArea', 'Adm\OrgChartController@createAssignArea')->name('create_assignArea');
    Route::post('updateArea', 'Adm\OrgChartController@updateAssignArea')->name('update_assignArea');
    Route::post('orgChart/getUsers', 'Adm\OrgChartController@getUsers')->name('orgChart_getUsers');
    Route::post('deleteArea', 'Adm\OrgChartController@deleteAssignArea')->name('delete_assignArea');

    //Rutas para tipos de incidencias
    Route::get('TpIncidence', 'Adm\TpIncidencesController@index')->name('index_tpincidence');
    Route::post('createTpIncidence', 'Adm\TpIncidencesController@store')->name('create_tpIncidence');
    Route::post('updateTpIncidence', 'Adm\TpIncidencesController@update')->name('update_tpIncidence');
    Route::post('deleteTpIncidence', 'Adm\TpIncidencesController@destroy')->name('delete_tpIncidence');
    
    //Rutas tabla configuración tipos de incidencia vs sistemas externos
    Route::get('PivotIncidence', 'Adm\TpIncidencesController@index_pivot')->name('index_pivotincidence');
    Route::post('createPivotIncidence', 'Adm\TpIncidencesController@st_pivot')->name('create_pivotIncidence');
    Route::post('updatePivotIncidence', 'Adm\TpIncidencesController@up_pivot')->name('update_pivotIncidence');
    Route::post('deletePivotIncidence', 'Adm\TpIncidencesController@de_pivot')->name('delete_pivotIncidence');

    //Rutas vista usuarios
    Route::get('users', 'Adm\UsersController@index')->name('index_user');
    Route::post('updateUsers', 'Adm\UsersController@update')->name('update_user');
    Route::post('deleteUsers', 'Adm\UsersController@destroy')->name('delete_user');
    
    Route::get('profile', 'Pages\profileController@index')->name('profile');
    Route::post('profile/update', 'Pages\profileController@updatePass')->name('profile_update');
    Route::post('updateReportUser', 'Pages\profileController@updateReport')->name('report_user');

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
    Route::post('myVacations/calcReturnDate', 'Pages\myVacationsController@calcReturnDate')->name('myVacations_calcReturnDate');
    
    Route::get('requestVacations/{id?}', 'Pages\requestVacationsController@index')->name('requestVacations');
    Route::post('requestVacations/vacations/accept', 'Pages\requestVacationsController@acceptRequest')->name('requestVacations_acceptRequest');
    Route::post('requestVacations/vacations/reject', 'Pages\requestVacationsController@rejectRequest')->name('requestVacations_rejectRequest');
    Route::post('requestVacations/vacations/filterYear', 'Pages\requestVacationsController@filterYear')->name('requestVacations_filterYear');
    Route::post('requestVacations/vacations/checkMail', 'Pages\requestVacationsController@checkMail')->name('requestVacations_checkMail');
    Route::post('requestVacations/vacations/getEmpApplicationsEA', 'Pages\requestVacationsController@getEmpApplicationsEA')->name('requestVacations_getEmpApplicationsEA');
    Route::post('requestVacations/vacations/getDataManager', 'Pages\requestVacationsController@getDataManager')->name('requestVacations_getDataManager');
    Route::post('requestVacations/vacations/getlDays', 'Pages\requestVacationsController@getlDays')->name('requestVacations_getlDays');
    Route::post('requestVacations/vacations/quickSend', 'Pages\requestVacationsController@quickSend')->name('requestVacations_quickSend');
    Route::post('requestVacations/vacations/quickData', 'Pages\requestVacationsController@quickData')->name('requestVacations_quickData');
    Route::post('requestVacations/vacations/getApplication', 'Pages\requestVacationsController@getApplication')->name('requestVacations_getApplication');
    Route::post('requestVacations/vacations/cancelRequest', 'Pages\requestVacationsController@cancelRequest')->name('requestVacations_cancelRequest');

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

    Route::post('getUserData', 'Utils\usersUtils@getUserData')->name('getUserData');

    Route::get('report/myEmpVacations', 'Pages\ReportMyEmpVacations@index')->name('report_myEmpVacations');
    Route::post('report/myEmpVacations_getLevelDown', 'Pages\ReportMyEmpVacations@getLevelDown')->name('report_getLevelDown');
    Route::post('report/myEmpVacations_getLevelUp', 'Pages\ReportMyEmpVacations@getLevelUp')->name('report_getLevelUp');
    Route::post('report/myEmpVacations_myEmpVacationsFilterYear', 'Pages\ReportMyEmpVacations@myEmpVacationsFilterYear')->name('report_myEmpVacationsFilterYear');

    Route::get('empVSArea', 'Adm\empVSOrgChartController@index')->name('empVSArea_index');
    Route::post('empVSArea/update', 'Adm\empVSOrgChartController@update')->name('empVSArea_update');

    Route::get('recoveredVacations', 'Pages\recoveredVacationsController@index')->name('recoveredVacations');
    Route::post('recoveredVacations/save', 'Pages\recoveredVacationsController@save')->name('recoveredVacations_save');

    // Route::post('notifications/clean', 'Utils\notificationsController@cleanNotificationsToSee')->name('notifications_clean');
    Route::get('notifications/getNotifications', 'Utils\notificationsController@getNotifications')->name('notifications_getNotifications');
    Route::post('notifications/cleanPendetNotification', 'Utils\notificationsController@cleanPendetNotification')->name('notifications_cleanPendetNotification');
    Route::post('notifications/revisedNotification', 'Utils\notificationsController@revisedNotification')->name('notifications_revisedNotification');
   
    Route::get('incidences', 'Pages\incidencesController@index')->name('incidences_index');
    Route::post('incidences/create', 'Pages\incidencesController@createIncidence')->name('incidences_create');
    Route::post('incidences/getIncidence', 'Pages\incidencesController@getApplication')->name('incidences_getIncidence');
    Route::post('incidences/update', 'Pages\incidencesController@updateIncidence')->name('incidences_update');
    Route::post('incidences/delete', 'Pages\incidencesController@deleteIncidence')->name('incidences_delete');
    Route::post('incidences/send', 'Pages\incidencesController@sendIncident')->name('incidences_send');
    Route::post('incidences/gestionSendIncidence', 'Pages\incidencesController@gestionSendIncidence')->name('incidences_gestionSendIncidence');
    Route::post('incidences/getBirdthDayIncidences', 'Pages\incidencesController@getBirdthDayIncidences')->name('incidences_getBirdthDayIncidences');
    Route::post('incidences/checkMail', 'Pages\incidencesController@checkMail')->name('incidences_checkMail');

    Route::get('requestIncidences/{id?}', 'Pages\requestIncidencesController@index')->name('requestIncidences_index');
    Route::post('requestIncidences/incidences/getEmployee', 'Pages\requestIncidencesController@getEmployee')->name('requestIncidences_getEmployee');
    Route::post('requestIncidences/incidences/approbe', 'Pages\requestIncidencesController@approbeIncidence')->name('requestIncidences_approbe');
    Route::post('requestIncidences/incidences/reject', 'Pages\requestIncidencesController@rejectIncidence')->name('requestIncidences_reject');
    Route::get('requestIncidences/incidences/getAllEmployees', 'Pages\requestIncidencesController@getAllEmployees')->name('requestIncidences_getAllEmployees');
    Route::get('requestIncidences/incidences/getEmployeeData', 'Pages\requestIncidencesController@getEmployeeData')->name('requestIncidences_getEmployeeData');
    Route::post('requestIncidences/incidences/sendAndAuthorize', 'Pages\incidencesController@sendAndAuthorize')->name('requestIncidences_sendAndAuthorize');
    Route::post('requestIncidences/incidences/seeLikeManager', 'Pages\requestIncidencesController@seeLikeManager')->name('requestIncidences_seeLikeManager');
    Route::post('requestIncidences/incidences/cancelIncidence', 'Pages\requestIncidencesController@cancelIncidence')->name('requestIncidences_cancel');

    Route::get('permission/{id?}', 'Pages\permissionController@index')->name('permission_index');
    Route::post('permission/save', 'Pages\permissionController@createPermission')->name('permission_create');
    Route::post('permission/update', 'Pages\permissionController@updatePermission')->name('permission_update');
    Route::post('permission/getPermission', 'Pages\permissionController@getPermission')->name('permission_getPermission');
    Route::post('permission/delete', 'Pages\permissionController@deletePermission')->name('permission_delete');
    Route::post('permission/gestionSendIncidence', 'Pages\permissionController@gestionSendIncidence')->name('permission_gestionSendIncidence');
    Route::post('permission/checkMail', 'Pages\permissionController@checkMail')->name('permission_checkMail');

    Route::get('requestPermission/{id?}', 'Pages\requestPermissionController@index')->name('requestPermission_index');
    Route::get('reqPer/personal/{id?}', 'Pages\requestPermissionController@PersonalTheme')->name('requestPersonalPermission');
    Route::post('requestPermission/permission/getEmployee', 'Pages\requestPermissionController@getEmployee')->name('requestPermission_getEmployee');
    Route::post('requestPermission/permission/approbePermission', 'Pages\requestPermissionController@approbePermission')->name('requestPermission_approbe');
    Route::post('requestPermission/permission/rejectPermission', 'Pages\requestPermissionController@rejectPermission')->name('requestPermission_reject');
    Route::post('requestPermission/permission/getAllEmployees', 'Pages\requestPermissionController@getAllEmployees')->name('requestPermission_getAllEmployees');
    Route::post('requestPermission/permission/getDirectEmployees', 'Pages\requestPermissionController@getDirectEmployees')->name('requestPermission_getDirectEmployees');
    Route::post('requestPermission/permission/sendAndAuthorize', 'Pages\permissionController@sendAndAuthorize')->name('requestPermission_sendAndAuthorize');
    Route::post('requestPermission/permission/seeLikeManager', 'Pages\requestPermissionController@seeLikeManager')->name('requestPermission_seeLikeManager');
    Route::post('requestPermission/permission/cancelPermission', 'Pages\requestPermissionController@cancelPermission')->name('requestPermission_cancel');
    
    Route::get('configAuth', 'Pages\configAuthController@index')->name('configAuth');
    Route::post('updateAuth', 'Pages\configAuthController@updateAuth')->name('update_authConf');
    Route::post('createAuth', 'Pages\configAuthController@createAuth')->name('create_authConf');
    Route::post('deleteAuth', 'Pages\configAuthController@deleteAuth')->name('delete_authConf');

    Route::get('annUsersChilds', 'Pages\annUsersChildsController@employeesDirectAnn')->name('aniversarios_usuarios');

    Route::get('annAllUsersChilds', 'Pages\annAllUsersChildsController@employeesAllAnn')->name('aniversarios_todos');

    Route::get('anniversaryColab', 'Pages\annUsersChildsController@employeesDirectAnn')->name('colab_ann');
    Route::get('anniversaryAllColab', 'Pages\annAllUsersChildsController@employeesAllAnn')->name('all_colab_ann');
    
    Route::get('synchronize', 'Sys\SyncController@reSync')->name('synchronize');

    Route::get('directVac', 'Pages\directVacationsController@index')->name('directVacations');
    Route::post('directVac/approbe', 'Pages\requestVacationsController@acceptAutorizeRequest')->name('directVacations_approbe');
    
    Route::get('recoverVacationsManagment', 'Pages\recoveredVacationsController@indexManagment')->name('recoveredVacations_managment');
    Route::post('recoverVacationsManagment/save', 'Pages\recoveredVacationsController@saveManagment')->name('recoveredVacations_managment_save');

    Route::get('delegationManager', 'Pages\delegationController@indexManager')->name('delegationManager');
    Route::post('delegationManager/saveDelegation', 'Pages\delegationController@saveDelegationManager')->name('delegationManager_saveDelegation');
    Route::post('delegationManager/updateDelegation', 'Pages\delegationController@updateDelegationManager')->name('delegationManager_updateDelegation');
    Route::post('delegationManager/deleteDelegation', 'Pages\delegationController@deleteDelegationManager')->name('delegationManager_deleteDelegation');

    /**Rutas eventos */
    Route::get('events', 'Pages\EventsController@index')->name('events');
    Route::post('events/save', 'Pages\EventsController@store')->name('events_save');
    Route::post('events/update', 'Pages\EventsController@updateEvent')->name('events_update');
    Route::post('events/delete', 'Pages\EventsController@deleteEvent')->name('events_delete');
    Route::post('events/getAssigned', 'Pages\EventsController@getEventAssigned')->name('events_getAssigned');
    Route::post('events/assignUser', 'Pages\EventsController@saveAssignUser')->name('events_saveAssignUser');
    Route::post('events/assignGroup', 'Pages\EventsController@saveAssigngroup')->name('events_saveAssignGroup');

    /**Rutas grupos de empleados  */
    Route::get('groups', 'Pages\employeeGroupsController@index')->name('groups');
    Route::post('groups/save', 'Pages\employeeGroupsController@saveGroup')->name('groups_save');
    Route::post('groups/update', 'Pages\employeeGroupsController@updateGroup')->name('groups_update');
    Route::post('groups/delete', 'Pages\employeeGroupsController@deleteGroup')->name('groups_delete');
    Route::post('groups/getUsersAssign', 'Pages\employeeGroupsController@getUsersAssign')->name('groups_getUsersAssign');
    Route::post('groups/assignUser', 'Pages\employeeGroupsController@setAssign')->name('groups_setAssign');

    /**Rutas certificados univ */
    Route::get('univCertificates', 'Pages\univCertificatesController@index')->name('univCertificates');
    Route::post('univCertificates/getCuadrants', 'Pages\univCertificatesController@getCuadrants')->name('univCertificates_getCuadrants');
    Route::post('univCertificates/getCertificates', 'Pages\univCertificatesController@getCertificates')->name('univCertificates_getCertificates');
    Route::get('univCertificates/getAllEmployees', 'Pages\univCertificatesController@getAllEmployees')->name('univCertificates_getAllEmployees');
    Route::get('univCertificates/getMyEmployees', 'Pages\univCertificatesController@getMyEmployees')->name('univCertificates_getMyEmployees');
    Route::get('univCertificates/getAllMyEmployees', 'Pages\univCertificatesController@getAllMyEmployees')->name('univCertificates_getAllMyEmployees');

    Route::get('personalData', 'Pages\personalDataController@index')->name('personalData');
    Route::post('personalData\update', 'Pages\personalDataController@updatePersonalData')->name('personalData_updatePersonalData');
    
    Route::get('administrativeMinutes', 'Pages\sanctionsController@indexAdministrativeMinutes')->name('admMinutes');
    Route::get('sanctions', 'Pages\sanctionsController@indexSanctions')->name('sanctions');
    Route::post('sanctions/myEmployees', 'Pages\sanctionsController@getMyEmployees')->name('sanctions_myEmployees');
    Route::post('sanctions/allEmployees', 'Pages\sanctionsController@getAllEmployees')->name('sanctions_allEmployees');
    Route::post('sanctions/MySanction', 'Pages\sanctionsController@getMySanction')->name('sanctions_getMySanction');

    Route::get('datePersonal', 'Pages\WorkRecordController@index')->name('word_record_personal');
    Route::post('datePersonal/GetPDF', 'Pages\WorkRecordController@getWorkRecord')->name('get_word_record_personal');
    Route::get('datePersonalManager', 'Pages\WorkRecordController@indexManager')->name('word_record_personal_manager');
    Route::get('datePersonalLog', 'Pages\WorkRecordLogController@index')->name('word_record_log');

});
/**
 * Rutas permisos 
 */
Route::get('permissionsToday', 'Pages\permissionsTodayController@index')->name('permissions_today');
Route::get('permissionsToday/getPermissions', 'Pages\permissionsTodayController@getlPermissions')->name('permissions_today_get');
<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Mail\cancelIncidenceMail;
use App\User;
use App\Utils\CapLinkUtils;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\authorizeVacationMail;
use App\Models\Vacations\Application;
use App\Models\Vacations\ApplicationsBreakdown;
use App\Models\Vacations\ApplicationLog;
use App\Models\Vacations\requestVacationLog;
use App\Models\Seasons\SpecialSeason;
use App\Utils\orgChartUtils;
use App\Utils\EmployeeVacationUtils;
use App\Constants\SysConst;
use App\Models\Vacations\MailLog;
use Spatie\Async\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use \App\Utils\delegationUtils;
use \App\Utils\folioUtils;
use App\Utils\recoveredVacationsUtils;
use App\Utils\notificationsUtils;
use App\Utils\incidencesUtils;
use App\Utils\usersInSystemUtils;

class requestVacationsController extends Controller
{
    public function mergedApplicationsRepeat($lEmployees, $lEmpSpecial, $lFatherEmpSpecial){
        // Se comentó el bloque de codigo por un error a solucionar al obtener lFatherEmpSpecial
        // foreach($lEmpSpecial as $emp){
        //     $oEmpFath = $lFatherEmpSpecial->where('external_id_n', $emp->external_id_n)->first();
        //     if(!is_null($oEmpFath)){
        //         foreach($emp->applications as $app){
        //             $oApp = $oEmpFath->applications->where('id_application', $app->id_application)->first();
        //             if(!is_null($oApp)){
        //                 $res = $oEmpFath->applications->where('id_application', $app->id_application)->first();
        //                 $index = $oEmpFath->applications->search($res);
        //                 $oEmpFath->applications->forget($index);
        //             }
        //         }
        //         $emp->applications = $emp->applications->merge($oEmpFath->applications);
        //         $res = $lFatherEmpSpecial->where('external_id_n', $emp->external_id_n)->first();
        //         $index = $lFatherEmpSpecial->search($res);
        //         $lFatherEmpSpecial->forget($index);
        //     }
        // }

        foreach($lEmployees as $emp){
            $oEmpSpec = $lEmpSpecial->where('external_id_n', $emp->external_id_n)->first();
            if(!is_null($oEmpSpec)){
                foreach($emp->applications as $app){
                    $oSpecApp = collect($oEmpSpec->applications);
                    $oApp = $oSpecApp->where('id_application', $app->id_application)->first();
                    if(!is_null($oApp)){
                        $res = $emp->applications->where('id_application', $oApp->id_application)->first();
                        $index = $emp->applications->search($res);
                        $emp->applications->forget($index);
                    }
                }
                $emp->applications = $emp->applications->merge($oEmpSpec->applications);
                $res = $lEmpSpecial->where('external_id_n', $emp->external_id_n)->first();
                $index = $lEmpSpecial->search($res);
                $lEmpSpecial->forget($index);
            }

            // $oEmpFath = $lFatherEmpSpecial->where('external_id_n', $emp->external_id_n)->first();
            // if(!is_null($oEmpFath)){
            //     foreach($emp->applications as $app){
            //         $oApp = $oEmpFath->applications->where('id_application', $app->id_application)->first();
            //         if(!is_null($oApp)){
            //             $res = $emp->applications->where('id_application', $oApp->id_application)->first();
            //             $index = $emp->applications->search();
            //             $emp->applications->forget($index);
            //         }
            //     }
            //     $emp->applications = $emp->applications->merge($oEmpFath->applications);
            //     $res = $lFatherEmpSpecial->where('external_id_n', $emp->external_id_n)->first();
            //     $index = $lFatherEmpSpecial->search($res);
            //     $lFatherEmpSpecial->forget($index);
            // }
        }

        $merged = $lEmployees->merge($lEmpSpecial);
        // $merged = $merged->merge($lFatherEmpSpecial);

        return $merged;
    }

    public function getData($year, $org_chart_job_id = null){
        if(is_null($org_chart_job_id)){
            // $org_chart_job_id = \Auth::user()->org_chart_job_id;
            $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        }

        $arrOrgJobsAux = orgChartUtils::getDirectChildsOrgChartJob($org_chart_job_id);
        // $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJobNoBoss($org_chart_job_id);
        $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJob($org_chart_job_id);

        $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
        $config = \App\Utils\Configuration::getConfigurations();
        if($org_chart_job_id == $config->default_node){
            $arrOrgJobsWitoutSuperviser = \DB::table('applications as a')
                                            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
                                            ->where('a.send_default', 1)
                                            ->where('a.is_deleted', 0)
                                            ->where('type_incident_id', SysConst::TYPE_VACACIONES)
                                            ->where('a.request_status_id', SysConst::APPLICATION_ENVIADO)
                                            ->pluck('org_chart_job_id')
                                            ->toArray();

            $result = array_diff($arrOrgJobsWitoutSuperviser, $arrOrgJobs);

            $lEmployeesWitoutSuperviser = EmployeeVacationUtils::getlEmployees($result);

            $lEmployees = $lEmployees->merge($lEmployeesWitoutSuperviser);
        }

        foreach($lEmployees as $emp){
            $applications_enviado = EmployeeVacationUtils::getApplications(
                                                            $emp->id,
                                                            null,
                                                            [ SysConst::APPLICATION_ENVIADO ]
                                                        );

            $applications_revision = EmployeeVacationUtils::getApplications(
                                                            $emp->id,
                                                            $year,
                                                            [ 
                                                              SysConst::APPLICATION_APROBADO,
                                                              SysConst::APPLICATION_CONSUMIDO,
                                                              SysConst::APPLICATION_RECHAZADO,
                                                              SysConst::APPLICATION_CANCELADO,
                                                            ]
                                                        );

            $emp->applications = $applications_enviado->merge($applications_revision);
                    
            // $emp->applications = EmployeeVacationUtils::getTakedDays($emp);
        }

        $lEmpSpecial_enviado = EmployeeVacationUtils::getApplicationsTypeSpecial(
                                    $org_chart_job_id,
                                    [ SysConst::APPLICATION_ENVIADO ],
                                    null
                                );

        $lEmpSpecial_revision = EmployeeVacationUtils::getApplicationsTypeSpecial(
                                    $org_chart_job_id,
                                    [   
                                        SysConst::APPLICATION_APROBADO,
                                        SysConst::APPLICATION_CONSUMIDO,
                                        SysConst::APPLICATION_RECHAZADO
                                    ],
                                    $year
                                );

        $lEmpSpecial = $lEmpSpecial_enviado->merge($lEmpSpecial_revision);

        // $lFatherEmpSpecial = EmployeeVacationUtils::getFatherApplicationsTypeSpecial($org_chart_job_id, [SysConst::APPLICATION_ENVIADO], $year);
        $lFatherEmpSpecial = null;
        // $merged = $lEmployees->merge($lEmpSpecial);
        // $merged = $merged->merge($lFatherEmpSpecial);

        $merged = $this->mergedApplicationsRepeat($lEmployees, $lEmpSpecial, $lFatherEmpSpecial);

        $holidays = \DB::table('holidays')
                        ->where('is_deleted', 0)
                        ->pluck('fecha');
        foreach ($merged as &$info) {
            // Verificar si el org_chart_job_id está en el array de directEmployeeIds
            if (in_array($info->org_chart_job_id, $arrOrgJobsAux)) {
                $info->is_direct = 1; // Si está, es empleado directo
            } else {
                $info->is_direct = 0; // Si no está, no es empleado directo
            }
        }

        return [$year, $merged, $holidays, $arrOrgJobs];
    }

    public function getApplication(Request $request){
        try {
            $oApplication = \DB::table('applications as a')
                                ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
                                ->leftJoin('sys_applications_sts as ap_st', 'ap_st.id_applications_st', '=', 'a.request_status_id')
                                ->leftJoin('users as u_rev', 'u_rev.id', '=', 'a.user_apr_rej_id')
                                ->leftJoin('applications_vs_types as at', 'at.application_id', '=', 'a.id_application')
                                ->where('a.id_application', $request->application_id)
                                ->where('a.is_deleted', 0)
                                ->select(
                                    'a.*',
                                    'at.is_normal',
                                    'at.is_past',
                                    'at.is_advanced',
                                    'at.is_proportional',
                                    'at.is_season_special',
                                    'at.is_recover_vacation',
                                    'at.is_event',
                                    'u.birthday_n',
                                    'u.benefits_date',
                                    'u.payment_frec_id',
                                    'ap_st.applications_st_name',
                                    'u_rev.full_name_ui as revisor',
                                )
                                ->first();

        $oUser = EmployeeVacationUtils::getEmployeeDataForMyVacation($oApplication->user_id);
        $lEvents = EmployeeVacationUtils::getEmployeeEvents($oApplication->user_id);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no es posible obtener la solicitud. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }
        
        return json_encode(['success' => true, 
            'oApplication' => $oApplication, 
            'tot_vacation_remaining' => $oUser->tot_vacation_remaining,
            'lEvents' => $lEvents,
        ]);
    }

    public function index($idApplication = null){
        // \Auth::user()->authorizedRole(SysConst::JEFE);
        delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR, SysConst::GH]);
        $config = \App\Utils\Configuration::getConfigurations();
        $year = Carbon::now()->year;
        $data = $this->getData($year);
        $myManagers = orgChartUtils::getMyManagers(delegationUtils::getOrgChartJobIdUser());
        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
            'APPLICATION_CONSUMIDO' => SysConst::APPLICATION_CONSUMIDO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO
        ];

        if(!is_null($idApplication)){
            $oApplication = \DB::table('applications as a')
                                ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
                                ->leftJoin('sys_applications_sts as ap_st', 'ap_st.id_applications_st', '=', 'a.request_status_id')
                                ->leftJoin('users as u_rev', 'u_rev.id', '=', 'a.user_apr_rej_id')
                                ->leftJoin('applications_vs_types as at', 'at.application_id', '=', 'a.id_application')
                                ->where('a.id_application', $idApplication)
                                ->where('a.is_deleted', 0)
                                ->where('a.type_incident_id', SysConst::TYPE_VACACIONES)
                                ->select(
                                    'a.*',
                                    'at.is_normal',
                                    'at.is_past',
                                    'at.is_advanced',
                                    'at.is_proportional',
                                    'at.is_season_special',
                                    'at.is_recover_vacation',
                                    'at.is_event',
                                    'u.birthday_n',
                                    'u.benefits_date',
                                    'u.payment_frec_id',
                                    'ap_st.applications_st_name',
                                    'u_rev.full_name_ui as revisor',
                                )
                                ->first();

            $myEmp = $data[1]->where('id', $oApplication->user_id);

            if(count($myEmp) == 0){
                $oApplication = null;
                $lEvents = [];
            }else{
                $oUser = EmployeeVacationUtils::getEmployeeDataForMyVacation($oApplication->user_id);
                $oApplication->tot_vacation_remaining = $oUser->tot_vacation_remaining;
                $lEvents = EmployeeVacationUtils::getEmployeeEvents($oApplication->user_id);
            }
        }else{
            $oApplication = null;
            $lEvents = [];
        }

        $lRequestStatus = \DB::table('sys_applications_sts')
                        ->where('is_deleted', 0)
                        ->whereNotIn('id_applications_st', [SysConst::APPLICATION_CONSUMIDO, SysConst::APPLICATION_CREADO])
                        ->select(
                            'id_applications_st as id',
                            'applications_st_name as name'
                        )
                        ->get();

        $lGestionStatus = \DB::table('sys_applications_sts')
                        ->where('is_deleted', 0)
                        ->where('id_applications_st', '!=', SysConst::APPLICATION_CONSUMIDO)
                        ->select(
                            'id_applications_st as id',
                            'applications_st_name as name'
                        )
                        ->get();

        $data[1] = usersInSystemUtils::FilterUsersInSystem($data[1], 'id');
        $myManagers = usersInSystemUtils::FilterUsersInSystem($myManagers, 'id');

        $authorized_client = $config->authorized_client_web;

        return view('emp_vacations.requestVacations')->with('lEmployees', $data[1])
                                                    ->with('year', $data[0])
                                                    ->with('lHolidays', $data[2])
                                                    ->with('constants', $constants)
                                                    ->with('idApplication', $idApplication)
                                                    ->with('myManagers', $myManagers)
                                                    ->with('config', $config)
                                                    ->with('oApplication', $oApplication)
                                                    ->with('lRequestStatus', $lRequestStatus)
                                                    ->with('lGestionStatus', $lGestionStatus)
                                                    ->with('lEvents', $lEvents)
                                                    ->with('authorized_client', $authorized_client);
    }

    public function getDataManager(Request $request){
        try {
            $year = Carbon::now()->year;
            if(!is_null($request->manager_id)){
                $oManager = \DB::table('users')
                                ->where('id', $request->manager_id)
                                ->where('is_delete', 0)
                                ->where('is_active', 1)
                                ->first();
                            
                if(is_null($oManager)){
                    return json_encode(['success' => false, 'message' => 'En este momento no es posible encontrar al supervisor '.$request->manager_name.' en el sistema. Por favor verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
                }

                $data = $this->getData($year, $oManager->org_chart_job_id);
            }else{
                $data = $this->getData($year);
            }

        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no fue posible obtener los registros del supervisor '.$oManager->full_name_ui.'. Por favor, verifique su conexión a internet e inténtelo de nuevo ', 'icon' => 'error']);
        }

        $data[1] = usersInSystemUtils::FilterUsersInSystem($data[1], 'id');
        return json_encode(['success' => true, 'lEmployees' => $data[1], 'year' => $data[0], 'lHolidays' => $data[2]]);
    }

    public function acceptRequest(Request $request){
        // \Auth::user()->authorizedRole(SysConst::JEFE);
        // \Auth::user()->IsMyEmployee($request->id_user);
        delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR, SysConst::GH]);
        $application = Application::findOrFail($request->id_application);
        // if(!$application->send_default){
        //     delegationUtils::getIsMyEmployeeUser($request->id_user);
        // }
        try {

            $result = incidencesUtils::checkVoboIsOpen($application->user_id, $application->start_date, $application->end_date);
            if($result->result == false){
                return json_encode(['success' => false, 'message' => $result->message, 'icon' => 'warning']);
            }

            if($application->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden aprobar solicitudes nuevas', 'icon' => 'warning']);
            }

            $oType = \DB::table('applications_vs_types')
                        ->where('application_id', $application->id_application)
                        ->first();

            \DB::beginTransaction();
            if($oType->is_recover_vacation){
                recoveredVacationsUtils::resetUsedDays($application);
            }

            if(!$oType->is_recover_vacation){
                $this->recalcApplicationsBreakdowns($request->id_user, $request->id_application, [
                                                                                                    SysConst::APPLICATION_CREADO,
                                                                                                    SysConst::APPLICATION_ENVIADO
                                                                                                ],
                                                                                            true);
            }

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $request->comments);

            $application->request_status_id = SysConst::APPLICATION_APROBADO;
            // $application->user_apr_rej_id = \Auth::user()->id;
            $application->user_apr_rej_id = delegationUtils::getIdUser();
            $application->approved_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $comments;
            if($request->returnDate){
                $application->return_date = $request->returnDate;
            }
            $application->authorized_client = $request->authorized_client;
            $application->update();

            notificationsUtils::revisedNotificationFromAction($application->type_incident_id, $application->id_application);

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            // $application_log->created_by = \Auth::user()->id;
            // $application_log->updated_by = \Auth::user()->id;
            $application_log->created_by = delegationUtils::getIdUser();
            $application_log->updated_by = delegationUtils::getIdUser();
            $application_log->save();

            $data = json_decode($this->sendRequestVacation($application, json_decode($application->ldays)));

            if($data->code == 500 || $data->code == 550){
                \DB::rollBack();
                return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
            }

            $employee = \DB::table('users')
                                ->where('id', $request->id_user)
                                ->first();

            $mailLog = new MailLog();
            $mailLog->date_log = Carbon::now()->toDateString();
            $mailLog->to_user_id = $employee->id;
            $mailLog->application_id_n = $application->id_application;
            $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
            $mailLog->type_mail_id = SysConst::MAIL_ACEPT_RECH_SOLICITUD;
            $mailLog->is_deleted = 0;
            // $mailLog->created_by = \Auth::user()->id;
            // $mailLog->updated_by = \Auth::user()->id;
            $mailLog->created_by = delegationUtils::getIdUser();
            $mailLog->updated_by = delegationUtils::getIdUser();
            $mailLog->save();
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible aprobar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet, cierre la solicitud e inténtelo de nuevo', 'icon' => 'error']);
        }

        $org_chart_job_id = null;
        if(!is_null($request->manager_id)){
            $oManager = \DB::table('users')
                            ->where('id', $request->manager_id)
                            ->where('is_delete', 0)
                            ->where('is_active', 1)
                            ->first();

            $org_chart_job_id = !is_null($oManager) ? $oManager->org_chart_job_id : null;
        }
        $data = $this->getData($request->year, $org_chart_job_id);

        $mypool = Pool::create();
        $mypool[] = async(function () use ($application, $request, $employee, $mailLog){
            try {
                Mail::to($employee->institutional_mail)->send(new authorizeVacationMail(
                                                        $application->id_application,
                                                        $employee->id,
                                                        $request->lDays,
                                                        $request->returnDate
                                                    )
                                                );
            } catch (\Throwable $th) {
                $mailLog->sys_mails_st_id = SysConst::MAIL_NO_ENVIADO;
                $mailLog->update();   
                \Log::error($th);
                return null; 
            }

            $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
            $mailLog->update();
        })->then(function ($mailLog) {
            
        })->catch(function ($mailLog) {
            
        })->timeout(function ($mailLog) {
            
        });

        $data[1] = usersInSystemUtils::FilterUsersInSystem($data[1], 'id');
        return json_encode(['success' => true, 'mail_log_id' => $mailLog->id_mail_log, 'message' => 'Solicitud aprobada con éxito', 'icon' => 'success', 'lEmployees' => $data[1], 'holidays' => $data[2]]);
    }

    public function acceptAutorizeRequest(Request $request){
        $application = Application::findOrFail($request->id_application);
        try {

            // $result = incidencesUtils::checkVoboIsOpen($application->user_id, $application->start_date, $application->end_date);
            // if($result->result == false){
            //     return json_encode(['success' => false, 'message' => $result->message, 'icon' => 'warning']);
            // }

            if($application->request_status_id != SysConst::APPLICATION_CREADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden aprobar solicitudes nuevas', 'icon' => 'warning']);
            }

            $oType = \DB::table('applications_vs_types')
                        ->where('application_id', $application->id_application)
                        ->first();

            \DB::beginTransaction();
            if($oType->is_recover_vacation){
                recoveredVacationsUtils::resetUsedDays($application);
            }

            if(!$oType->is_recover_vacation){
                $this->recalcApplicationsBreakdowns($request->id_user, $request->id_application, [
                                                                                                    SysConst::APPLICATION_CREADO,
                                                                                                    SysConst::APPLICATION_ENVIADO
                                                                                                ],
                                                                                            true);
            }

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $request->comments);

            $application->request_status_id = SysConst::APPLICATION_APROBADO;
            $application->user_apr_rej_id = delegationUtils::getIdUser();
            $application->approved_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $comments;
            $application->return_date = $request->returnDate;
            $application->update();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = delegationUtils::getIdUser();
            $application_log->updated_by = delegationUtils::getIdUser();
            $application_log->save();

            $data = json_decode($this->sendRequestVacation($application, json_decode($application->ldays)));

            if($data->code == 500 || $data->code == 550){
                \DB::rollBack();
                return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
            }

            $employee = \DB::table('users')
                                ->where('id', $request->id_user)
                                ->first();

            $mailLog = new MailLog();
            $mailLog->date_log = Carbon::now()->toDateString();
            $mailLog->to_user_id = $employee->id;
            $mailLog->application_id_n = $application->id_application;
            $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
            $mailLog->type_mail_id = SysConst::MAIL_ACEPT_RECH_SOLICITUD;
            $mailLog->is_deleted = 0;
            $mailLog->created_by = delegationUtils::getIdUser();
            $mailLog->updated_by = delegationUtils::getIdUser();
            $mailLog->save();
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible aprobar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet, cierre la solicitud e inténtelo de nuevo', 'icon' => 'error']);
        }

        $org_chart_job_id = null;
        if(!is_null($request->manager_id)){
            $oManager = \DB::table('users')
                            ->where('id', $request->manager_id)
                            ->where('is_delete', 0)
                            ->where('is_active', 1)
                            ->first();

            $org_chart_job_id = !is_null($oManager) ? $oManager->org_chart_job_id : null;
        }
        $data = $this->getData($request->year, $org_chart_job_id);

        $mypool = Pool::create();
        $mypool[] = async(function () use ($application, $request, $employee, $mailLog){
            try {
                Mail::to($employee->institutional_mail)->send(new authorizeVacationMail(
                                                        $application->id_application,
                                                        $employee->id,
                                                        $request->lDays,
                                                        $request->returnDate
                                                    )
                                                );
            } catch (\Throwable $th) {
                $mailLog->sys_mails_st_id = SysConst::MAIL_NO_ENVIADO;
                $mailLog->update();   
                \Log::error($th);
                return null; 
            }

            $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
            $mailLog->update();
        })->then(function ($mailLog) {
            
        })->catch(function ($mailLog) {
            
        })->timeout(function ($mailLog) {
            
        });

        return json_encode(['success' => true, 'message' => 'Solicitud aprobada con éxito', 'icon' => 'success']);
    }

    public function rejectRequest(Request $request){
        // \Auth::user()->authorizedRole(SysConst::JEFE);
        // \Auth::user()->IsMyEmployee($request->id_user);
        delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR, SysConst::GH]);
        $application = Application::findOrFail($request->id_application);
        if(!$application->send_default){
            delegationUtils::getIsMyEmployeeUser($request->id_user);
        }
        try {

            $arrRequestStatus = [
                SysConst::APPLICATION_CREADO,
                SysConst::APPLICATION_ENVIADO,
            ];
            
            if($application->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden rechazar solicitudes nuevas', 'icon' => 'warning']);
            }
            
            $oType = \DB::table('applications_vs_types')
                        ->where('application_id', $application->id_application)
                        ->first();

            \DB::beginTransaction();
            if($oType->is_recover_vacation){
                recoveredVacationsUtils::resetUsedDays($application);
            }

            if(!$oType->is_recover_vacation){
                $this->recalcApplicationsBreakdowns($request->id_user, $request->id_application, $arrRequestStatus, false);
            }

            $comments = str_replace(['"', "\\", "\r", "\n"], "", $request->comments);
            
            $application->request_status_id = SysConst::APPLICATION_RECHAZADO;
            // $application->user_apr_rej_id = \Auth::user()->id;
            $application->user_apr_rej_id = delegationUtils::getIdUser();
            $application->rejected_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $comments;
            if($request->returnDate){
                $application->return_date = $request->returnDate;
            }
            $application->authorized_client = $request->authorized_client;
            $application->update();

            notificationsUtils::revisedNotificationFromAction($application->type_incident_id, $application->id_application);

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            // $application_log->created_by = \Auth::user()->id;
            // $application_log->updated_by = \Auth::user()->id;
            $application_log->created_by = delegationUtils::getIdUser();
            $application_log->updated_by = delegationUtils::getIdUser();
            $application_log->save();

            $employee = \DB::table('users')
                            ->where('id', $request->id_user)
                            ->first();

            $mailLog = new MailLog();
            $mailLog->date_log = Carbon::now()->toDateString();
            $mailLog->to_user_id = $employee->id;
            $mailLog->application_id_n = $application->id_application;
            $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
            $mailLog->type_mail_id = SysConst::MAIL_ACEPT_RECH_SOLICITUD;
            $mailLog->is_deleted = 0;
            // $mailLog->created_by = \Auth::user()->id;
            // $mailLog->updated_by = \Auth::user()->id;
            $mailLog->created_by = delegationUtils::getIdUser();
            $mailLog->updated_by = delegationUtils::getIdUser();
            $mailLog->save();
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible rechazar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet, cierre la solicitud e inténtelo de nuevo', 'icon' => 'error']);
        }

        $org_chart_job_id = null;
        if(!is_null($request->manager_id)){
            $oManager = \DB::table('users')
                            ->where('id', $request->manager_id)
                            ->where('is_delete', 0)
                            ->where('is_active', 1)
                            ->first();

            $org_chart_job_id = !is_null($oManager) ? $oManager->org_chart_job_id : null;
        }
        $data = $this->getData($request->year, $org_chart_job_id);

        $mypool = Pool::create();
        $mypool[] = async(function () use ($application, $request, $employee, $mailLog){
            try {
                Mail::to($employee->institutional_mail)->send(new authorizeVacationMail(
                                                        $application->id_application,
                                                        $employee->id,
                                                        $request->lDays,
                                                        $request->returnDate
                                                    )
                                                );
            } catch (\Throwable $th) {
                $mailLog->sys_mails_st_id = SysConst::MAIL_NO_ENVIADO;
                $mailLog->update();
                \Log::error($th);
                return null; 
            }

            $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
            $mailLog->update();
        })->then(function ($mailLog) {
            
        })->catch(function ($mailLog) {
            
        })->timeout(function ($mailLog) {
            
        });

        $data[1] = usersInSystemUtils::FilterUsersInSystem($data[1], 'id');
        return json_encode(['success' => true, 'mail_log_id' => $mailLog->id_mail_log, 'message' => 'Solicitud rechazada con éxito', 'icon' => 'success', 'lEmployees' => $data[1], 'holidays' => $data[2]]);
    }

    public function filterYear(Request $request){
        try {
            if(!is_null($request->manager_id)){
                $oManager = \DB::table('users')
                            ->where('id', $request->manager_id)
                            ->where('is_delete', 0)
                            ->where('is_active', 1)
                            ->first();
                        
                if(is_null($oManager)){
                    return json_encode(['success' => false, 'message' => 'En este momento no es posible encontrar al supervisor '.$request->manager_name.' en el sistema. Por favor verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
                }
                $data = $this->getData($request->year, $oManager->org_chart_job_id);
            }else{
                $data = $this->getData($request->year, delegationUtils::getOrgChartJobIdUser());
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no es posible obtener los registros. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);    
        }

        $data[1] = usersInSystemUtils::FilterUsersInSystem($data[1], 'id');
        return json_encode(['success' => true, 'lEmployees' => $data[1], 'holidays' => $data[2]]);
    }

    public function recalcApplicationsBreakdowns($employee_id, $application_id, $arrRequestStatus, $isAccept){
        $lApplications = Application::where('user_id', $employee_id)
                                    ->whereIn('request_status_id', $arrRequestStatus)
                                    ->where('is_deleted', 0)
                                    ->where('type_incident_id', SysConst::TYPE_VACACIONES)
                                    ->get();

        $applicationsId = [];

        foreach($lApplications as $app){
            array_push($applicationsId, $app->id_application);
            $app->is_deleted = 1;
            $app->update();
        }

        $appBreakDowns = ApplicationsBreakdown::whereIn('application_id', $applicationsId)->get();
        foreach($appBreakDowns as $ab){
            $ab->delete();
        }

        $oApplication = Application::find($application_id); 

        $lApplications = Application::where('user_id', $employee_id)
                                    ->whereIn('id_application', $applicationsId)
                                    ->where('id_application', '!=', $application_id)
                                    ->where('type_incident_id', SysConst::TYPE_VACACIONES)
                                    ->get();

        if($isAccept){
            $lApplications->prepend($oApplication);
        }

        foreach($lApplications as $app){
            $takedDays = $app->total_days;
            $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id, true, 1);

            if($user->tot_vacation_remaining < $takedDays){
                // return json_encode(['success' => false, 'message' => 'El colaborador no cuenta con dias disponibles', 'icon' => 'warning']);
                throw new \Exception("El colaborador no cuenta con los días de vacaciones solicitados");
            }

            $vacations = collect($user->vacation)->sortBy('year');

            foreach($vacations as $vac){
                if($takedDays > 0){
                    $count = 0;
                    if($vac->remaining > 0){
                        for($i=0; $i<$vac->remaining; $i++){
                            $takedDays--;
                            $count++;
                            if($takedDays == 0 || $takedDays < 0){
                                break;
                            }
                        }
                        $vac->remaining = $vac->remaining - $count;
                        $appBreakdown = new ApplicationsBreakdown();
                        $appBreakdown->application_id = $app->id_application;
                        $appBreakdown->days_effective = $count;
                        $appBreakdown->application_year = $vac->year;
                        $appBreakdown->admition_count = 1;
                        $appBreakdown->save();
                    }
                }else{
                    break;
                }
            }

            $app->is_deleted = 0;
            $app->update();
        }

        if(!$isAccept){
            $oApplication->is_deleted = 0;
            $oApplication->update();
        }
    }

    public function checkMail(Request $request){
        $mailLog = MailLog::find($request->mail_log_id);

        $message = '';
        if($mailLog->sys_mails_st_id == SysConst::MAIL_NO_ENVIADO){
            $user = \DB::table('users')
                        ->where('id', $mailLog->to_user_id)
                        ->first();

            if(is_null($user->institutional_mail)){
                $message = 'En este momento no es posible enviar el correo electrónico porque el solicitante no cuenta con una dirección registrada en el sistema. Solicita una dirección de correo electrónico a GH para fines de comunicación.';
            }else{
                $message = 'El correo electrónico no pudo ser enviado. Te recomendamos verificar tu conexión a internet para resolver el problema (de ser necesario comunícate con el área de sistemas).';
            }
        }

        return json_encode(['success' => true, 'status' => $mailLog->sys_mails_st_id, 'message' => $message]);
    }

    public function sendRequestVacation($oApplication, $lDays){
        $lHolidays = \DB::table('holidays')
                        ->where('is_deleted', 0)
                        ->pluck('fecha')
                        ->toArray();

        $employee = \DB::table('users')
                        ->where('id', $oApplication->user_id)
                        ->first();

        $ext_company_id = \DB::table('ext_company')
                            ->where('id_company', $employee->company_id)
                            ->value('external_id');

        $appBreakDowns = ApplicationsBreakdown::where('application_id', $oApplication->id_application)->get();

        $typeIncident = \DB::table('cat_incidence_tps')
                            ->where('id_incidence_tp', $oApplication->type_incident_id)
                            ->first();

        $userVacation = \DB::table('vacation_users')
                            ->where('user_id', $employee->id)
                            ->where('is_deleted', 0)
                            ->get();

        $rows = [];
        $start_date = Carbon::parse($oApplication->start_date);
        $count = 0;
        $indexlDays = 0;
        foreach($appBreakDowns as $index => $br){
            if($index != 0){
                for($i=$indexlDays; $i < count($lDays); $i++){
                    if($lDays[$i]->taked){
                        $start_date = Carbon::parse($lDays[$i]->date);
                        $indexlDays = $i;
                        break;
                    }
                }
            }
            $year = $userVacation->where('year', $br->application_year)->first();
            $end_date = clone $start_date;
            $count = 0;

            $rowDays = [];
            for($i=$indexlDays; $i < count($lDays); $i++){
                if($lDays[$i]->taked){
                    $end_date = Carbon::parse($lDays[$i]->date);
                    $indexlDays = $i+1;
                    $count++;

                    $rowDays[] = $lDays[$i];
                }
                if($count >= $br->days_effective){
                    break;
                }
            }

            $row = [
                'breakdown_id' => $br->id_application_breakdown,
                'folio' => $oApplication->folio_n.'-'.$count,
                'effective_days' => $br->days_effective,
                'year' => $br->application_year,
                'anniversary' => $year->id_anniversary,
                'start_date' => $start_date->toDateString(),
                'end_date' => $end_date->toDateString(),
                'lDays' => $rowDays,
            ];

            array_push($rows, $row);
        }

        $ext_ids = \DB::table('tp_incidents_pivot')
                    ->where('tp_incident_id', $typeIncident->id_incidence_tp)
                    ->where('int_sys_id', 2)
                    ->first();

        $arrJson = [
            'to_insert' => true,
            'application_id' => $oApplication->id_application,
            'folio' => $oApplication->folio_n,
            'employee_id' => $employee->external_id_n,
            'company_id' => $ext_company_id,
            'type_pay_id' => $employee->payment_frec_id,
            'tp_abs' => $ext_ids->ext_tp_incident_id,
            'cl_abs' => $ext_ids->ext_cl_incident_id,
            'date_send' => $oApplication->date_send_n,
            'date_ini' => $oApplication->start_date,
            'date_end' => $oApplication->end_date,
            'total_days' => $oApplication->total_days,
            // 'lDays' => $oApplication->ldays,
            'rows' => $rows,
        ];
        $config = \App\Utils\Configuration::getConfigurations();
        $client = new Client([
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
        ]);

        $str = json_encode($arrJson);

        $response = $client->request('GET', 'postIncidents/' . json_encode($arrJson));
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        $oVacLog = new requestVacationLog();
        $oVacLog->application_id = $oApplication->id_application;
        $oVacLog->employee_id = $oApplication->user_id;
        $oVacLog->response_code = $data->response->code;
        $oVacLog->message = $data->response->message;
        // $oVacLog->created_by = \Auth::user()->id;
        // $oVacLog->updated_by = \Auth::user()->id;
        $oVacLog->created_by = delegationUtils::getIdUser();
        $oVacLog->updated_by = delegationUtils::getIdUser();
        $oVacLog->save();

        return json_encode(['code' => $data->response->code, 'message' => $data->response->message]);
            // return null;
    }

    public function checkDate($oDate, $lHolidays, $employee){
        for($i = 0; $i < 31; $i++){
            switch ($oDate->dayOfWeek) {
                case 6:
                    if($employee->payment_frec_id == SysConst::QUINCENA){
                        $oDate->add(2, 'days');
                    }
                    break;
                case 0:
                    $oDate->add(1, 'days');
                    break;
                default:
                    break;
            }
            
            if(!in_array($oDate->toDateString(), $lHolidays)){
                break;
            }else{
                $oDate->add(1, 'days');
            }
        }

        return $oDate;
    }

    public function getEmpApplicationsEA(Request $request){
        try {
            $data = EmployeeVacationUtils::getEmpApplicationsEA($request->user_id);
            $lSpecialSeason = EmployeeVacationUtils::getEmpSpecialSeason($request->user_id);
            $user = \DB::table('users')->where('is_delete', 0)->where('is_active', 1)->where('id', $request->user_id)->first();
            $lTemp_special = EmployeeVacationUtils::getEmployeeTempSpecial($user->org_chart_job_id, $user->id, $user->job_id);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no fue posible obtener los registros de vacaciones. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'warning']);
        }

        return json_encode(['success' => true, 'arrAplications' => $data, 'arrSpecialSeasons' => $lSpecialSeason, 'lTemp' => $lTemp_special]);
    }

    public function getlDays(Request $request){
        try {
            $oApp = Application::find($request->id_application);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no es posible obtener la lista de días efectivos. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'error']);
        }
        return json_encode(['success' => true, 'lDays' => $oApp->ldays]);
    }

    public function quickSend(Request $request){
        try {
            $application = Application::findOrFail($request->id_application);

            \DB::beginTransaction();
            $application->request_status_id = SysConst::APPLICATION_ENVIADO;
            $application->date_send_n = Carbon::now()->toDateString();
            $application->update();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento, no es posible enviar la solicitud debido a un error inesperado. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

        return json_encode(['success' => true]);
    }

    public function quickData(Request $request){
        try {
            $user = EmployeeVacationUtils::getEmployeeVacationsData($request->user_id);
            $user->applications = EmployeeVacationUtils::getApplications($request->user_id, $request->year);
            $user->applications = EmployeeVacationUtils::getTakedDays($user);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'En este momento no es posible obtener los datos el colaborador. Por favor, verifique su conexión a internet e inténtelo de nuevo', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $user]);
    }

    public function cancelRequest(Request $request){
        try {
            delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR, SysConst::GH]);
            
            \DB::beginTransaction();

            $application_id = $request->application_id;
            $manager_id = null;
            $year = $request->year;

            $oIncidence = Application::findOrFail($application_id);

            //delegationUtils::getIsMyEmployeeUser($oIncidence->user_id);

            $data = json_decode(CapLinkUtils::cancelIncidence($oIncidence));

            if($data->code == 500 || $data->code == 550){
                \DB::rollBack();
                return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
            }

            $oIncidence->request_status_id = SysConst::APPLICATION_CANCELADO;
            $oIncidence->user_apr_rej_id = \Auth::user()->id;
            $oIncidence->update();

            $employee = User::find($oIncidence->user_id);

            $mailLog = new MailLog();
            $mailLog->date_log = Carbon::now()->toDateString();
            $mailLog->to_user_id = $employee->id;
            $mailLog->application_id_n = $oIncidence->id_application;
            $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
            $mailLog->type_mail_id = SysConst::MAIL_CANCELACION_VACACIONES;
            $mailLog->is_deleted = 0;
            // $mailLog->created_by = \Auth::user()->id;
            // $mailLog->updated_by = \Auth::user()->id;
            $mailLog->created_by = delegationUtils::getIdUser();
            $mailLog->updated_by = delegationUtils::getIdUser();
            $mailLog->save();
    
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }
        
        $org_chart_job_id = null;
        if(!is_null($manager_id)){
            $oManager = \DB::table('users')
                            ->where('id', $manager_id)
                            ->where('is_delete', 0)
                            ->where('is_active', 1)
                            ->first();

            $org_chart_job_id = !is_null($oManager) ? $oManager->org_chart_job_id : null;
        }
        $data = $this->getData($year, $org_chart_job_id);

        $mypool = Pool::create();
        $mypool[] = async(function () use ($oIncidence, $employee, $mailLog){
            try {
                Mail::to($employee->institutional_mail)->send(new cancelIncidenceMail(
                                                        $oIncidence->id_application,
                                                        $oIncidence->user_id,
                                                        \Auth::user()->id
                                                    )
                                                );
            } catch (\Throwable $th) {
                $mailLog->sys_mails_st_id = SysConst::MAIL_NO_ENVIADO;
                $mailLog->update();   
                \Log::error($th);
                return null; 
            }

            $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
            $mailLog->update();
        })->then(function ($mailLog) {
            
        })->catch(function ($mailLog) {
            
        })->timeout(function ($mailLog) {
            
        });
        $data[1] = usersInSystemUtils::FilterUsersInSystem($data[1], 'id');
        return json_encode(['success' => true, 'lEmployees' => $data[1], 'mail_log_id' => $mailLog->id_mail_log]);
    }

    public function deleteRequest(Request $request){
        delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR, SysConst::GH]);
        $application = Application::findOrFail($request->id_application);
        if(!$application->send_default){
            delegationUtils::getIsMyEmployeeUser($request->id_user);
        }

        try {
            \DB::beginTransaction();
            if($application->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden rechazar solicitudes nuevas', 'icon' => 'warning']);
            }

            $result = json_decode(incidencesUtils::checkExternalIncident($application));

            if($result->code == 550){
                $application->is_deleted = 1;
                $application->update();
            }else{
                return json_encode(['success' => false, 'message' => 'No se encontró la solitud en sistema SIIE, 
                el proceso siguiente es rechazar o aprobar la solicitud, no eliminarla', 'icon' => 'warning']);
            }

            $org_chart_job_id = null;
            if(!is_null($request->manager_id)){
                $oManager = \DB::table('users')
                                ->where('id', $request->manager_id)
                                ->where('is_delete', 0)
                                ->where('is_active', 1)
                                ->first();

                $org_chart_job_id = !is_null($oManager) ? $oManager->org_chart_job_id : null;
            }
            $data = $this->getData(null, $org_chart_job_id);
            \DB::commit();
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        $data[1] = usersInSystemUtils::FilterUsersInSystem($data[1], 'id');
        return json_encode(['success' => true, 'lEmployees' => $data[1]]);
    }
}

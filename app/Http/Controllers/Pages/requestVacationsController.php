<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
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
                    $oApp = $oEmpSpec->applications->where('id_application', $app->id_application)->first();
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

        // $arrOrgJobsAux = orgChartUtils::getDirectChildsOrgChartJob($org_chart_job_id);
        $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJobNoBoss($org_chart_job_id);

        $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);

        foreach($lEmployees as $emp){
            $emp->applications = EmployeeVacationUtils::getApplications(
                                                            $emp->id,
                                                            $year,
                                                            [   SysConst::APPLICATION_ENVIADO,
                                                                SysConst::APPLICATION_APROBADO,
                                                                SysConst::APPLICATION_RECHAZADO
                                                            ]
                                                        );
            $emp->applications = EmployeeVacationUtils::getTakedDays($emp);
        }

        $lEmpSpecial = EmployeeVacationUtils::getApplicationsTypeSpecial(
                            $org_chart_job_id,
                            [   
                                SysConst::APPLICATION_ENVIADO,
                                SysConst::APPLICATION_APROBADO,
                                SysConst::APPLICATION_RECHAZADO
                            ],
                            $year
                        );

        // $lFatherEmpSpecial = EmployeeVacationUtils::getFatherApplicationsTypeSpecial($org_chart_job_id, [SysConst::APPLICATION_ENVIADO], $year);
        $lFatherEmpSpecial = null;
        // $merged = $lEmployees->merge($lEmpSpecial);
        // $merged = $merged->merge($lFatherEmpSpecial);

        $merged = $this->mergedApplicationsRepeat($lEmployees, $lEmpSpecial, $lFatherEmpSpecial);

        $holidays = \DB::table('holidays')
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        return [$year, $merged, $holidays, $arrOrgJobs];
    }

    public function index($idApplication = null){
        // \Auth::user()->authorizedRole(SysConst::JEFE);
        delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR]);
        $config = \App\Utils\Configuration::getConfigurations();
        $year = Carbon::now()->year;
        $data = $this->getData($year);
        // $myManagers = orgChartUtils::getMyManagers(\Auth::user()->org_chart_job_id);
        if(\Auth::user()->rol_id == SysConst::ADMINISTRADOR){
            $myManagers = orgChartUtils::getMyManagers(2);
        }else{
            $myManagers = orgChartUtils::getMyManagers(delegationUtils::getOrgChartJobIdUser());
        }
        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO
        ];

        return view('emp_vacations.requestVacations')->with('lEmployees', $data[1])
                                                    ->with('year', $data[0])
                                                    ->with('lHolidays', $data[2])
                                                    ->with('constants', $constants)
                                                    ->with('idApplication', $idApplication)
                                                    ->with('myManagers', $myManagers)
                                                    ->with('config', $config);
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
                    return json_encode(['success' => false, 'message' => 'No se encontro al supervisor '.$request->manager_name, 'icon' => 'error']);
                }

                $data = $this->getData($year, $oManager->org_chart_job_id);
            }else{
                $data = $this->getData($year);
            }

        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'error al obtener los registros del supervisor '.$oManager->full_name_ui, 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lEmployees' => $data[1], 'year' => $data[0], 'lHolidays' => $data[2]]);
    }

    public function acceptRequest(Request $request){
        // \Auth::user()->authorizedRole(SysConst::JEFE);
        // \Auth::user()->IsMyEmployee($request->id_user);
        delegationUtils::getAutorizeRolUser(SysConst::JEFE);
        delegationUtils::getIsMyEmployeeUser($request->id_user);
        try {
            $application = Application::findOrFail($request->id_application);

            if($application->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden aprobar solicitudes nuevas', 'icon' => 'warning']);
            }

            \DB::beginTransaction();

            $this->recalcApplicationsBreakdowns($request->id_user, $request->id_application, [
                                                                                                SysConst::APPLICATION_CREADO,
                                                                                                SysConst::APPLICATION_ENVIADO
                                                                                            ],
                                                                                        true);
            
            $application->request_status_id = SysConst::APPLICATION_APROBADO;
            // $application->user_apr_rej_id = \Auth::user()->id;
            $application->user_apr_rej_id = delegationUtils::getIdUser();
            $application->approved_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $request->comments;
            $application->update();

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
            return json_encode(['success' => false, 'message' => 'Error al aprobrar la solicitud', 'icon' => 'error']);
        }

        $org_chart_job_id = null;
        if(!is_null($request->manager_id)){
            $oManager = \DB::table('users')
                            ->where('id', $request->manager_id)
                            ->where('is_deleted', 0)
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
                return null; 
            }

            $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
            $mailLog->update();
        })->then(function ($mailLog) {
            
        })->catch(function ($mailLog) {
            
        })->timeout(function ($mailLog) {
            
        });

        return json_encode(['success' => true, 'mail_log_id' => $mailLog->id_mail_log, 'message' => 'Solicitud aprobada con éxito', 'icon' => 'success', 'lEmployees' => $data[1], 'holidays' => $data[2]]);
    }

    public function rejectRequest(Request $request){
        // \Auth::user()->authorizedRole(SysConst::JEFE);
        // \Auth::user()->IsMyEmployee($request->id_user);
        delegationUtils::getAutorizeRolUser(SysConst::JEFE);
        delegationUtils::getIsMyEmployeeUser($request->id_user);
        try {
            $application = Application::findOrFail($request->id_application);

            $arrRequestStatus = [
                SysConst::APPLICATION_CREADO,
                SysConst::APPLICATION_ENVIADO, 
                SysConst::APPLICATION_APROBADO
            ];
            
            if($application->request_status_id != SysConst::APPLICATION_ENVIADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden rechazar solicitudes nuevas', 'icon' => 'warning']);
            }
            
            \DB::beginTransaction();
            $this->recalcApplicationsBreakdowns($request->id_user, $request->id_application, $arrRequestStatus, false);
            
            $application->request_status_id = SysConst::APPLICATION_RECHAZADO;
            // $application->user_apr_rej_id = \Auth::user()->id;
            $application->user_apr_rej_id = delegationUtils::getIdUser();
            $application->rejected_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $request->comments;
            $application->update();

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
            return json_encode(['success' => false, 'message' => 'Error al rechazar la solicitud', 'icon' => 'error']);
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
                return null; 
            }

            $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
            $mailLog->update();
        })->then(function ($mailLog) {
            
        })->catch(function ($mailLog) {
            
        })->timeout(function ($mailLog) {
            
        });

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
                    return json_encode(['success' => false, 'message' => 'No se encontro al supervisor '.$request->manager_name, 'icon' => 'error']);
                }
                $data = $this->getData($request->year, $oManager->org_chart_job_id);
            }else{
                $data = $this->getData($request->year, delegationUtils::getOrgChartJobIdUser());
            }
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al cargar los registros', 'icon' => 'error']);    
        }

        return json_encode(['success' => true, 'lEmployees' => $data[1], 'holidays' => $data[2]]);
    }

    public function recalcApplicationsBreakdowns($employee_id, $application_id, $arrRequestStatus, $isAccept){
        $lApplications = Application::where('user_id', $employee_id)
                                    ->whereIn('request_status_id', $arrRequestStatus)
                                    ->where('is_deleted', 0)
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
                                    ->get();

        if($isAccept){
            $lApplications->prepend($oApplication);
        }

        foreach($lApplications as $app){
            $takedDays = $app->total_days;
            $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id, false, 1);

            if($user->tot_vacation_remaining < $takedDays){
                return json_encode(['success' => false, 'message' => 'El colaborador no cuenta con dias disponibles', 'icon' => 'warning']);
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

        return json_encode(['sucess' => true, 'status' => $mailLog->sys_mails_st_id]);
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
        foreach($appBreakDowns as $index => $br){
            if($index != 0){
                for($i=$br->days_effective; $i < count($lDays); $i++){
                    if($lDays[$i]->taked){
                        $start_date = Carbon::parse($lDays[$i]->date);
                        break;
                    }
                }
            }
            $year = $userVacation->where('year', $br->application_year)->first();
            $end_date = clone $start_date;
            $count = 0;
            $index != 0 ? $i=$br->days_effective : $i = 0;

            for($i; $i < count($lDays); $i++){
                if($lDays[$i]->taked){
                    $end_date = Carbon::parse($lDays[$i]->date);
                    $count++;
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
            ];

            array_push($rows, $row); 
        }

        $arrJson = [
            'application_id' => $oApplication->id_application,
            'folio' => $oApplication->folio_n,
            'employee_id' => $employee->external_id_n,
            'company_id' => $ext_company_id,
            'type_pay_id' => $employee->payment_frec_id,
            'type_incident_id' => $typeIncident->id_incidence_tp,
            'class_incident_id' => $typeIncident->incidence_cl_id,
            'date_send' => $oApplication->date_send_n,
            'date_ini' => $oApplication->start_date,
            'date_end' => $oApplication->end_date,
            'total_days' => $oApplication->total_days,
            'rows' => $rows,
        ];

        $client = new Client([
            'base_uri' => '192.168.1.233:9001',
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
            return json_encode(['success' => false, 'message' => 'No se pudieron obtener registos de vacaciones solicitadas anteriormente', 'icon' => 'warning']);
        }

        return json_encode(['success' => true, 'arrAplications' => $data, 'arrSpecialSeasons' => $lSpecialSeason, 'lTemp' => $lTemp_special]);
    }

    public function getlDays(Request $request){
        try {
            $oApp = Application::find($request->id_application);
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener la lista de días efectivos', 'error']);
        }
        return json_encode(['success' => true, 'lDays' => $oApp->ldays]);
    }
}

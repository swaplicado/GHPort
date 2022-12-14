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

class requestVacationsController extends Controller
{
    public function getData($year){
        $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob(\Auth::user()->org_chart_job_id);

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

        $holidays = \DB::table('holidays')
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        return [$year, $lEmployees, $holidays, $arrOrgJobs];
    }

    public function index($idApplication = null){
        \Auth::user()->authorizedRole(SysConst::JEFE);
        $config = \App\Utils\Configuration::getConfigurations();
        $year = Carbon::now()->year;
        $data = $this->getData($year);
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
                                                    ->with('config', $config);
    }

    public function acceptRequest(Request $request){
        \Auth::user()->authorizedRole(SysConst::JEFE);
        \Auth::user()->IsMyEmployee($request->id_user);
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
            $application->user_apr_rej_id = \Auth::user()->id;
            $application->approved_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $request->comments;
            $application->update();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = \Auth::user()->id;
            $application_log->updated_by = \Auth::user()->id;
            $application_log->save();

            $data = json_decode($this->sendRequestVacation($application));

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
            $mailLog->created_by = \Auth::user()->id;
            $mailLog->updated_by = \Auth::user()->id;
            $mailLog->save();
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al aprobrar la solicitud', 'icon' => 'error']);
        }

        $data = $this->getData($request->year);

        $mypool = Pool::create();
        $mypool[] = async(function () use ($application, $request, $employee, $mailLog){
            try {
                Mail::to($employee->email)->send(new authorizeVacationMail(
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

        return json_encode(['success' => true, 'mail_log_id' => $mailLog->id_mail_log, 'message' => 'Solicitud aprobada con ??xito', 'icon' => 'success', 'lEmployees' => $data[1], 'holidays' => $data[2]]);
    }

    public function rejectRequest(Request $request){
        \Auth::user()->authorizedRole(SysConst::JEFE);
        \Auth::user()->IsMyEmployee($request->id_user);
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
            $application->user_apr_rej_id = \Auth::user()->id;
            $application->rejected_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $request->comments;
            $application->update();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = \Auth::user()->id;
            $application_log->updated_by = \Auth::user()->id;
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
            $mailLog->created_by = \Auth::user()->id;
            $mailLog->updated_by = \Auth::user()->id;
            $mailLog->save();
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al rechazar la solicitud', 'icon' => 'error']);
        }

        $data = $this->getData($request->year);

        $mypool = Pool::create();
        $mypool[] = async(function () use ($application, $request, $employee, $mailLog){
            try {
                Mail::to($employee->email)->send(new authorizeVacationMail(
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

        return json_encode(['success' => true, 'mail_log_id' => $mailLog->id_mail_log, 'message' => 'Solicitud rechazada con ??xito', 'icon' => 'success', 'lEmployees' => $data[1], 'holidays' => $data[2]]);
    }

    public function filterYear(Request $request){
        try {
            $data = $this->getData($request->year);
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
            $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id);

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

    public function sendRequestVacation($oApplication){
        $lHolidays = \DB::table('holidays')
                        ->where('is_deleted', 0)
                        ->pluck('fecha')
                        ->toArray();

        $employee = \DB::table('users')
                        ->where('id', $oApplication->user_id)
                        ->first();

        $appBreakDowns = ApplicationsBreakdown::where('application_id', $oApplication->id_application)->get();

        $typeIncident = \DB::table('cat_incidence_tps')
                            ->where('id_incidence_tp', $oApplication->type_incident_id)
                            ->first();

        $userVacation = \DB::table('vacation_users')
                            ->where('user_id', $employee->id)
                            ->where('is_deleted', 0)
                            ->get();

        $rows = [];
        $start_date = $this->checkDate(Carbon::parse($oApplication->start_date), $lHolidays, $employee);
        $count = 0;
        foreach($appBreakDowns as $br){
            $year = $userVacation->where('year', $br->application_year)->first();
            $end_date = clone $start_date;
            for ($i=0; $i<($br->days_effective - 1); $i++) { 
                $end_date = $this->checkDate($end_date->add(1, 'days'), $lHolidays, $employee);
            }
            $count++;
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

            $start_date = $this->checkDate($end_date->add(1, 'days'), $lHolidays, $employee);
        }

        $arrJson = [
            'application_id' => $oApplication->id_application,
            'folio' => $oApplication->folio_n,
            'employee_id' => $employee->external_id_n,
            'company_id' => $employee->company_id,
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
            'timeout' => 10.0,
        ]);

        $response = $client->request('GET', 'postIncidents/' . json_encode($arrJson));
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        $oVacLog = new requestVacationLog();
        $oVacLog->application_id = $oApplication->id_application;
        $oVacLog->employee_id = $oApplication->user_id;
        $oVacLog->response_code = $data->response->code;
        $oVacLog->message = $data->response->message;
        $oVacLog->created_by = \Auth::user()->id;
        $oVacLog->updated_by = \Auth::user()->id;
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
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'No se pudieron obtener registos de vacaciones solicitadas anteriormente', 'icon' => 'warning']);
        }

        return json_encode(['success' => true, 'arrAplications' => $data, 'arrSpecialSeasons' => $lSpecialSeason]);
    }
}

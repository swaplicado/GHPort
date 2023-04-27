<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\EmployeeVacationUtils;
use App\Constants\SysConst;
use App\Models\Vacations\Application;
use App\Models\Vacations\ApplicationsBreakdown;
use App\Models\Vacations\ApplicationLog;
use GuzzleHttp\Client;
use \Carbon\Carbon;
use \App\Utils\folioUtils;

class specialVacationsController extends Controller
{
    public function index(){
        $config = \App\Utils\Configuration::getConfigurations();
        $user = EmployeeVacationUtils::getEmployeeDataForMyVacation(10); //Gerardo ortiz
        $now = Carbon::now();
        $initialCalendarDate = $now->addDays(1)->toDateString();

        $holidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO
        ];

        $lTemp_special = EmployeeVacationUtils::getEmployeeTempSpecial($user->org_chart_job_id, $user->id, $user->job_id);

        return view('special_vacations.special_vacation')->with('user', $user)
                                                ->with('initialCalendarDate', $initialCalendarDate)
                                                ->with('lHolidays', $holidays)
                                                ->with('year', Carbon::now()->year)
                                                ->with('constants', $constants)
                                                ->with('config', $config)
                                                ->with('lTemp', $lTemp_special);
    }

    public function setRequestVac(Request $request){
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $comments = $request->comments;
        $takedDays = $request->takedDays;
        $returnDate = $request->returnDate;
        $tot_calendar_days = $request->tot_calendar_days;
        $take_holidays = $request->take_holidays;
        $take_rest_days = $request->take_rest_days;
        $employee_id = $request->employee_id;
        
        try {

            $arrApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($employee_id);

            foreach($arrApplicationsEA as $arr){
                $isBetWeen = Carbon::parse($arr)->between($startDate, $endDate);
                if($isBetWeen){
                    return json_encode(['success' => false, 'message' => 'Ya existe una solicitud de vacaciones para la fecha: '.Carbon::parse($arr)->locale('es-ES')->isoFormat('ddd D-MMM-YYYY'), 'icon' => 'warning']);
                }
            }

            $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id);

            foreach($user->applications as $ap){
                if($ap->request_status_id == 1){
                    return json_encode(['success' => false, 'message' => 'No puede crear otra solicitud de vacaciones si tiene solicitudes creadas pendientes de enviar', 'icon' => 'warning']);
                }
            }

            $vacations = collect($user->vacation)->sortBy('year');

            \DB::beginTransaction();
            $date = Carbon::now();
            $application = new Application();
            $application->start_date = $startDate;
            $application->end_date = $endDate;
            $application->take_holidays = $take_holidays;
            $application->take_rest_days = $take_rest_days;
            $application->total_days = $takedDays;
            $application->tot_calendar_days = $tot_calendar_days;
            $application->return_date = $returnDate;
            $application->user_id = $employee_id;
            $application->request_status_id = SysConst::APPLICATION_CREADO;
            $application->type_incident_id = SysConst::TYPE_VACACIONES;
            $application->emp_comments_n = $comments;
            $application->is_deleted = false;
            $application->folio_n = folioUtils::makeFolio($date, $application->user_id);
            $application->save();

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
                        $appBreakdown->application_id = $application->id_application;
                        $appBreakdown->days_effective = $count;
                        $appBreakdown->application_year = $vac->year;
                        $appBreakdown->admition_count = 1;
                        $appBreakdown->save();
                    }
                }else{
                    break;
                }
            }

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = \Auth::user()->id;
            $application_log->updated_by = \Auth::user()->id;
            $application_log->save();

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al guardar la solicitud', 'icon' => 'error']);
        }

        $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id);
        $user->applications = EmployeeVacationUtils::getTakedDays($user);

        return json_encode(['success' => true, 'message' => 'Solicitud guardada con éxito', 'oUser' => $user]);
    }

    public function updateRequestVac(Request $request){
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $comments = $request->comments;
        $takedDays = $request->takedDays;
        $returnDate = $request->returnDate;
        $tot_calendar_days = $request->tot_calendar_days;
        $take_holidays = $request->take_holidays;
        $take_rest_days = $request->take_rest_days;
        $employee_id = $request->employee_id;

        try {
            $arrApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($employee_id);

            foreach($arrApplicationsEA as $arr){
                $isBetWeen = Carbon::parse($arr)->between($startDate, $endDate);
                if($isBetWeen){
                    return json_encode(['success' => false, 'message' => 'Ya existe una solicitud de vacaciones para la fecha: '.Carbon::parse($arr)->locale('es-ES')->isoFormat('ddd D-MMM-YYYY'), 'icon' => 'warning']);
                }
            }

            $application = Application::findOrFail($request->id_application);

            if($application->request_status_id != SysConst::APPLICATION_CREADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden eliminar solicitudes con el estatus CREADO', 'icon' => 'warning']);
            }

            \DB::beginTransaction();
            
            $application->is_deleted = 1;
            $application->update();

            $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id);
    
            if($user->tot_vacation_remaining < $takedDays){
                return json_encode(['success' => false, 'message' => 'No cuentas con días disponibles', 'icon' => 'warning']);
            }
    
            $vacations = collect($user->vacation)->sortBy('year');

            $appBreakDowns = ApplicationsBreakdown::where('application_id', $request->id_application)->get();
            foreach($appBreakDowns as $ab){
                $ab->delete();
            }
            $date = Carbon::now();
            $application->start_date = $startDate;
            $application->end_date = $endDate;
            $application->take_holidays = $take_holidays;
            $application->take_rest_days = $take_rest_days;
            $application->total_days = $takedDays;
            $application->tot_calendar_days = $tot_calendar_days;
            $application->return_date = $returnDate;
            $application->emp_comments_n = $comments;
            $application->is_deleted = 0;
            // $application->folio_n = $this->makeFolio($date, $application->user_id);
            $application->update();

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
                        $appBreakdown->application_id = $application->id_application;
                        $appBreakdown->days_effective = $count;
                        $appBreakdown->application_year = $vac->year;
                        $appBreakdown->admition_count = 1;
                        $appBreakdown->save();
                    }
                }else{
                    break;
                }
            }

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al editar el registro', 'icon' => 'error']);
        }
        $user = EmployeeVacationUtils::getEmployeeVacationsData($employee_id);
        $user->applications = EmployeeVacationUtils::getTakedDays($user);
        return json_encode(['success' => true, 'message' => 'Registro editado con éxito', 'icon' => 'success', 'oUser' => $user]);
    }

    public function getEmpApplicationsEA(Request $request){
        try {
            $lApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($request->user_id);
            $lSpecialSeason = EmployeeVacationUtils::getEmpSpecialSeason($request->user_id);
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'No se pudieron obtener registos de vacaciones solicitadas anteriormente', 'icon' => 'warning']);
        }

        return json_encode(['success' => true, 'arrAplications' => $lApplicationsEA, 'arrSpecialSeasons' => $lSpecialSeason]);
    }

    public function deleteRequestVac(Request $request){
        try {
            $application = Application::findOrFail($request->id_application);

            if($application->request_status_id != SysConst::APPLICATION_CREADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden eliminar solicitudes con el estatus CREADO', 'icon' => 'warning']);
            }

            \DB::beginTransaction();
            
            $application->is_deleted = 1;
            $application->update();

            $user = EmployeeVacationUtils::getEmployeeVacationsData($request->employee_id);
            $user->applications = EmployeeVacationUtils::getApplications($request->employee_id, $request->year);
            $user->applications = EmployeeVacationUtils::getTakedDays($user);

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al eliminar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'message' => 'Registro eliminado con éxito', 'icon' => 'success', 'oUser' => $user]);
    }

    public function filterYear(Request $request){
        try {
            $applications = EmployeeVacationUtils::getApplications($request->employee_id, $request->year);
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al cargar los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'applications' => $applications]);
    }

    public function sendRequestVac(Request $request){
        try {
            $application = Application::findOrFail($request->id_application);

            if($application->request_status_id != SysConst::APPLICATION_CREADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden aprobar solicitudes nuevas', 'icon' => 'warning']);
            }

            \DB::beginTransaction();

            $this->recalcApplicationsBreakdowns($request->employee_id, $request->id_application, [
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

            // $data = json_decode($this->sendRequestVacation($application));

            // if($data->code == 500 || $data->code == 550){
            //     \DB::rollBack();
            //     return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
            // }

            $user = EmployeeVacationUtils::getEmployeeVacationsData($request->employee_id);
            $user->applications = EmployeeVacationUtils::getTakedDays($user);
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al aprobrar la solicitud', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'message' => 'Registro enviado y aprobado con éxito', 'icon' => 'success', 'oUser' => $user]);
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
}

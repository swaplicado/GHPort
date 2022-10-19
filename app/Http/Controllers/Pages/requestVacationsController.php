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
use App\Utils\orgChartUtils;
use App\Utils\EmployeeVacationUtils;
use App\Constants\SysConst;


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
        }

        $holidays = \DB::table('holidays')
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        return [$year, $lEmployees, $holidays, $arrOrgJobs];
    }

    public function index($idApplication = null){
        \Auth::user()->authorizedRole(SysConst::JEFE);
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
                                                    ->with('idApplication', $idApplication);
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
            $application->sup_comments_n = $request->comments;
            $application->update();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = \Auth::user()->id;
            $application_log->updated_by = \Auth::user()->id;
            $application_log->save();
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al aprobrar la solicitud', 'icon' => 'error']);
        }

        $data = $this->getData($request->year);

        try {
            $employee = \DB::table('users')
                                ->where('id', $request->id_user)
                                ->first();

            Mail::to($employee->email)->send(new authorizeVacationMail(
                                                    $application->id_application,
                                                    $employee->id,
                                                    $request->lDays,
                                                    $request->returnDate
                                                )
                                            );
        } catch (\Throwable $th) {
            return json_encode(
                [
                    'success' => true,
                    'message' => 'La solicitud fue aprobada con éxito, pero ocurrio un error al enviar el e-mail, notifique al colaborador',
                    'icon' => 'info',
                    'lEmployees' => $data[1],
                    'holidays' => $data[2]
                ]
            );
        }

        return json_encode(['success' => true, 'message' => 'Solicitud aprobada con éxito', 'icon' => 'success', 'lEmployees' => $data[1], 'holidays' => $data[2]]);
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
            
            if($application->request_status_id != SysConst::APPLICATION_ENVIADO && $application->request_status_id != SysConst::APPLICATION_APROBADO){
                return json_encode(['success' => false, 'message' => 'Solo se pueden rechazar solicitudes nuevas o aprobadas', 'icon' => 'warning']);
            }
            
            \DB::beginTransaction();
            $this->recalcApplicationsBreakdowns($request->id_user, $request->id_application, $arrRequestStatus, false);
            
            $application->request_status_id = SysConst::APPLICATION_RECHAZADO;
            $application->sup_comments_n = $request->comments;
            $application->update();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = \Auth::user()->id;
            $application_log->updated_by = \Auth::user()->id;
            $application_log->save();
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al rechazar la solicitud', 'icon' => 'error']);
        }

        $data = $this->getData($request->year);

        try {
            $employee = \DB::table('users')
                                ->where('id', $request->id_user)
                                ->first();

            Mail::to($employee->email)->send(new authorizeVacationMail(
                                                    $application->id_application,
                                                    $employee->id,
                                                    $request->lDays,
                                                    $request->returnDate
                                                )
                                            );
        } catch (\Throwable $th) {
            return json_encode(
                [
                    'success' => true,
                    'message' => 'La solicitud fue rechazada con éxito, pero ocurrio un error al enviar el e-mail, notifique al colaborador',
                    'icon' => 'info',
                    'lEmployees' => $data[1],
                    'holidays' => $data[2]
                ]
            );
        }

        return json_encode(['success' => true, 'message' => 'Solicitud rechazada con éxito', 'icon' => 'success', 'lEmployees' => $data[1], 'holidays' => $data[2]]);
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
}

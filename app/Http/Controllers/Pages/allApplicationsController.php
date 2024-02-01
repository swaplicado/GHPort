<?php

namespace App\Http\Controllers\Pages;

use App\Constants\SysConst;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Utils\delegationUtils;
use App\Utils\orgChartUtils;
use App\Utils\EmployeeVacationUtils;
use App\Utils\applicationsUtils;
use App\Utils\dateUtils;

class allApplicationsController extends Controller
{
    public function index(requestVacationsController $requestVacationsController){
        $year = Carbon::now()->year;
        $dataEmployees = json_decode(applicationsUtils::getEmployeesToRevise());
        $dataVacations = json_decode(applicationsUtils::getVacations($year));
        $dataIncidences = json_decode(applicationsUtils::getIncidences($year));
        $dataPermissionsPersonal = json_decode(applicationsUtils::getPermissionsPersonal($year));
        $dataPermissionsLaboral = json_decode(applicationsUtils::getPermissionsLaboral($year));

        $dataEmployees = collect($dataEmployees->lEmployees);
        $dataEmployees = $dataEmployees->sortBy('full_name');

        $lEmployees = [];
        $lEmployees[] = [
            'id' => 0,
            'text' => 'Todos'
        ];
        foreach($dataEmployees as $employee){
            $lEmployees[] = [
                                'id' => $employee->id,
                                'text' => $employee->full_name
                            ];
        }

        $lVacations = [];
        foreach($dataVacations->lVacations as $vacation){
            $lVacations[] = [
                                'request_id' => $vacation->id_application,
                                'request_class_id' => SysConst::VACACIONES,
                                'request_status_id' => $vacation->request_status_id,
                                'employee_id' => $vacation->user_id,
                                'start_date' => $vacation->start_date,
                                'end_date' => $vacation->end_date,
                                'folio_n' => $vacation->folio_n,
                                'employee' => $vacation->employee,
                                'request_class' => 'Vacaciones',
                                'request_type' => $vacation->type,
                                'start_date_format' => dateUtils::formatDate($vacation->start_date, 'DDD D-M-Y'),
                                'end_date_format' => dateUtils::formatDate($vacation->end_date, 'DDD D-M-Y'),
                                'return_date' => dateUtils::formatDate($vacation->return_date, 'DDD D-M-Y'),
                                'time' => '',
                                'status' => $vacation->applications_st_name,
                                'date_send_n' => dateUtils::formatDate($vacation->date_send_n, 'DDD D-M-Y'),
                            ];
        }

        $lIncidences = [];
        foreach($dataIncidences->lIncidences as $incidence){
            $lIncidences[] = [
                                'request_id' => $incidence->id_application,
                                'request_class_id' => SysConst::INCIDENCIA,
                                'request_status_id' => $incidence->request_status_id,
                                'employee_id' => $incidence->user_id,
                                'start_date' => $incidence->start_date,
                                'end_date' => $incidence->end_date,
                                'folio_n' => $incidence->folio_n,
                                'employee' => $incidence->employee,
                                'request_class' => 'Incidencia',
                                'request_type' => $incidence->incidence_tp_name,
                                'start_date_format' => dateUtils::formatDate($incidence->start_date, 'DDD D-M-Y'),
                                'end_date_format' => dateUtils::formatDate($incidence->end_date, 'DDD D-M-Y'),
                                'return_date' => '',
                                'time' => '',
                                'status' => $incidence->applications_st_name,
                                'date_send_n' => dateUtils::formatDate($incidence->date_send_n, 'DDD D-M-Y'),
                            ];
        }

        $lPermissionsPersonal = [];
        foreach($dataPermissionsPersonal->lPermissions as $permission){
            $lPermissionsPersonal[] = [
                                'request_id' => $permission->id_hours_leave,
                                'request_class_id' => SysConst::PERMISO_PERSONAL_HORAS,
                                'request_status_id' => $permission->request_status_id,
                                'employee_id' => $permission->user_id,
                                'start_date' => $permission->start_date,
                                'end_date' => $permission->end_date,
                                'folio_n' => $permission->folio_n,
                                'employee' => $permission->employee,
                                'request_class' => 'Permiso personal',
                                'request_type' => $permission->permission_tp_name,
                                'start_date_format' => dateUtils::formatDate($permission->start_date, 'DDD D-M-Y'),
                                'end_date_format' => dateUtils::formatDate($permission->end_date, 'DDD D-M-Y'),
                                'return_date' => '',
                                'time' => $permission->time,
                                'status' => $permission->applications_st_name,
                                'date_send_n' => dateUtils::formatDate($permission->date_send_n, 'DDD D-M-Y'),
                            ];
        }

        $lPermissionsLaboral = [];
        foreach($dataPermissionsLaboral->lPermissions as $permission){
            $lPermissionsLaboral[] = [
                                'request_id' => $permission->id_hours_leave,
                                'request_class_id' => SysConst::PERMISO_LABORAL_HORAS,
                                'request_status_id' => $permission->request_status_id,
                                'employee_id' => $permission->user_id,
                                'start_date' => $permission->start_date,
                                'end_date' => $permission->end_date,
                                'folio_n' => $permission->folio_n,
                                'employee' => $permission->employee,
                                'request_class' => 'Permiso laboral',
                                'request_type' => $permission->permission_tp_name,
                                'start_date_format' => dateUtils::formatDate($permission->start_date, 'DDD D-M-Y'),
                                'end_date_format' => dateUtils::formatDate($permission->end_date, 'DDD D-M-Y'),
                                'return_date' => '',
                                'time' => $permission->time,
                                'status' => $permission->applications_st_name,
                                'date_send_n' => dateUtils::formatDate($permission->date_send_n, 'DDD D-M-Y'),
                            ];
        }

        $lApplications = array_merge($lVacations, $lIncidences, $lPermissionsPersonal, $lPermissionsLaboral);

        $lClases = [
            ['id' => 0, 'text' => 'Todo'],
            ['id' => SysConst::VACACIONES, 'text' => 'Vacaciones'],
            ['id' => SysConst::INCIDENCIA, 'text' => 'Incidencias'],
            ['id' => SysConst::PERMISO_PERSONAL_HORAS, 'text' => 'Permiso personal'],
            ['id' => SysConst::PERMISO_LABORAL_HORAS, 'text' => 'Permiso laboral'],
        ];

        $lStatus = [
            ['id' => SysConst::APPLICATION_ENVIADO, 'text' => 'Por aprobar'],
            ['id' => SysConst::APPLICATION_APROBADO, 'text' => 'Aprobadas'],
            ['id' => SysConst::APPLICATION_RECHAZADO, 'text' => 'Rechazadas'],
            ['id' => SysConst::APPLICATION_CANCELADO, 'text' => 'Canceladas'],
        ];

        $lConstants = [
            'CLASE_TODO' => 0,
            'VACACIONES' => SysConst::VACACIONES,
            'INCIDENCIA' => SysConst::INCIDENCIA,
            'PERMISO_PERSONAL_HORAS' => SysConst::PERMISO_PERSONAL_HORAS,
            'PERMISO_LABORAL_HORAS' => SysConst::PERMISO_LABORAL_HORAS,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO,
            'APPLICATION_CANCELADO' => SysConst::APPLICATION_CANCELADO,
        ];

        $lApplications = collect($lApplications)->sortBy('start_date');

        return view('allApplications.allApplications')->with('lApplications', $lApplications)
                                                        ->with('lClases', $lClases)
                                                        ->with('lStatus', $lStatus)
                                                        ->with('lConstants', $lConstants)
                                                        ->with('lEmployees', $lEmployees);
    }

    public function getApplication(Request $request){
        try {
            $request_class_id = $request->request_class;
            $request_id = $request->request_id;
            switch ($request_class_id) {
                case SysConst::VACACIONES:
                    $data = json_decode(applicationsUtils::getApplicationVacation($request_id));
                    break;
                case SysConst::INCIDENCIA:
                    $data = json_decode(applicationsUtils::getApplicationIncidence($request_id));
                    break;
                case SysConst::PERMISO_PERSONAL_HORAS:
                    $data = json_decode(applicationsUtils::getApplicationPermission($request_id));
                    break;
                default:
                    break;
            }

            $Employee = applicationsUtils::getEmployee($request->employee_id);
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => $th->getMessage()]);
        }

        return json_encode(['success' => true, 'data' => $data, 'employee' => $Employee]);
    }
}

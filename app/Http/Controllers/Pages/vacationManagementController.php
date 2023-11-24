<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\EmployeeVacationUtils;
use App\Utils\orgChartUtils;
use Carbon\Carbon;
use \App\Utils\delegationUtils;

class vacationManagementController extends Controller
{
    public function getEmployeeData(Request $request){
        try {
            $user = EmployeeVacationUtils::getEmployeeDataForMyVacation($request->employee_id);

            $now = Carbon::now();
            // $initialCalendarDate = $now->addDays(1)->toDateString();
            $initialCalendarDate = $now->subMonths(1)->toDateString();

            $year = $now->year;

            $lTemp_special = EmployeeVacationUtils::getEmployeeTempSpecial($user->org_chart_job_id, $user->id, $user->job_id);

        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['succeess' => false, 'message' => 'Error al obtener los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $user, 'initialCalendarDate' => $initialCalendarDate, 'year' => $year, 'lTemp' => $lTemp_special]);
    }

    public function getDirectEmployees(){
        try {
            // $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob(\Auth::user()->org_chart_job_id);
            $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob(delegationUtils::getOrgChartJobIdUser());
            $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al obtener la lista de colaboradores directos', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lEmployees' => $lEmployees ]);
    }

    public function getAllEmployees(){
        try {
            // $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJob(\Auth::user()->org_chart_job_id);
            $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJob(delegationUtils::getOrgChartJobIdUser());
            $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al obtener la lista de los colaboradores', 'icon' => 'error']);
        }
        return json_encode(['success'  => true, 'lEmployees' => $lEmployees]);
    }
}
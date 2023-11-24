<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constants\SysConst;
use App\Http\Controllers\Pages\EmployeesVacationsController;
use App\Utils\orgChartUtils;
use App\Utils\delegationUtils;
use App\Utils\EmployeeVacationUtils;
use Carbon\Carbon;

class ReportMyEmpVacations extends Controller
{
    public function index(){
        if(\Auth::user()->rol_id == SysConst::ADMINISTRADOR){
            $org_chart_job_id = 2;
        }else{
            $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        }
        $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($org_chart_job_id);
        
        $lLevels = json_encode([['level' => 0, 'orgCharts' => $arrOrgJobs]]);

        $lEmployees = (new EmployeesVacationsController)->getDirectEmployees($org_chart_job_id);

        $lEmployees = EmployeeVacationUtils::getVacations($lEmployees);
        $year = Carbon::now()->year;

        return view('emp_vacations.Report_myEmp_vacations')->with('lEmployees', $lEmployees)
                                                        ->with('year', $year)
                                                        ->with('lLevels', $lLevels);
    }

    public function getLevelDown(Request $request){
        try {
            $oLevels = json_decode($request->lLevels);
            // $oLevels = $request->lLevels;
            
            count($oLevels) > 1 ? $level = collect($oLevels)->last() : $level = $oLevels[0];
            $childs = [];
            foreach($level->orgCharts as $org_chart_job_id){
                $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($org_chart_job_id);
                count($arrOrgJobs) > 0 ? $childs[] = $arrOrgJobs : '';
            }

            if(count($childs) < 1){
                return json_encode(['success' => false, 'message' => 'No hay mas niveles por debajo del actual', 'icon' => 'info']);
            }

            array_push($oLevels, json_decode(json_encode(['level' => ((int)$level->level + 1), 'orgCharts' => \Arr::collapse($childs)])));
            $lEmployees = collect([]);
            foreach ($childs as $lOrgChart) {
                $result = EmployeeVacationUtils::getlEmployees($lOrgChart);
                $lEmployees = $lEmployees->merge($result);
            }
    
            $lEmployees = EmployeeVacationUtils::getVacations($lEmployees);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al obtener los colaboradores', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lEmployees' => $lEmployees, 'lLevels' => $oLevels]);
    }

    public function getLevelUp(Request $request){
        try {
            $oLevels = json_decode($request->lLevels);
            $lOrgcharts = [];
            if(count($oLevels) > 1){
                unset($oLevels[count($oLevels) - 1]);
                foreach($oLevels as $level){
                    $lOrgChart[] = $level->orgCharts;
                }
                $lOrgChart = \Arr::collapse($lOrgChart);

                $lEmployees = collect([]);

                $result = EmployeeVacationUtils::getlEmployees($lOrgChart);
                $lEmployees = $lEmployees->merge($result);
        
                $lEmployees = EmployeeVacationUtils::getVacations($lEmployees);
            }else{
                return json_encode(['success' => false, 'message' => 'No hay mas niveles por arriba del actual', 'icon' => 'info']);
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al obtener los colaboradores', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lEmployees' => $lEmployees, 'lLevels' => $oLevels]);
    }

    public function myEmpVacationsFilterYear(Request $request){
        try {
            $oLevels = json_decode($request->lLevels);
            foreach($oLevels as $level){
                $lOrgChart[] = $level->orgCharts;
            }
            $lOrgChart = \Arr::collapse($lOrgChart);

            $lEmployees = EmployeeVacationUtils::getlEmployees($lOrgChart);
    
            $lEmployees = EmployeeVacationUtils::getVacations($lEmployees, $request->startYear);
            $nowYear = Carbon::now()->year;
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Errror al obtener las vacaciones del periodo '. $request->startYear.' - '.$nowYear, 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lEmployees' => $lEmployees, 'nowYear' => $nowYear]);
    }
}
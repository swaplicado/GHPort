<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\orgChartUtils;
use App\Utils\EmployeeVacationUtils;
use App\Models\Adm\OrgChartJob;
use Carbon\Carbon;
use Carbon\Translator;
use App\Constants\SysConst;
use \App\Utils\delegationUtils;
use App\Utils\usersInSystemUtils;

class EmployeesVacationsController extends Controller
{
    public $months_code = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    /**
     * Vista vacaciones de los empleados directos
     */
    public function employeesDirectIndex(){
        $config = \App\Utils\Configuration::getConfigurations();
        // $lEmployees = $this->getDirectEmployees(\Auth::user()->org_chart_job_id);
        $lEmployees = $this->getDirectEmployees(delegationUtils::getOrgChartJobIdUser());

        $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
        // return view('emp_vacations.my_emp_vacations')->with('lEmployees', $lEmployees)->with('config', $config);
        return view('emp_vacations.my_direct_emp_vacations')->with('lEmployees', $lEmployees)->with('config', $config);
    }

    /**
     * Vista las vacaciones de todos los empleados por debajo de un usuario
     */
    public function allEmployeesIndex(){
        $config = \App\Utils\Configuration::getConfigurations();

        // $lEmployees = $this->getAlllEmployees(\Auth::user()->org_chart_job_id, $config);
        $lEmployees = $this->getAlllEmployees(delegationUtils::getOrgChartJobIdUser(), $config);

        $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
        return view('emp_vacations.all_emp_vacations')->with('lEmployees', $lEmployees)->with('config', $config);
    }

    /**
     * Regresa las vacaciones de los empleados directos
     */
    public function getDirectEmployees($orgJobId){
        $config = \App\Utils\Configuration::getConfigurations();
        $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($orgJobId);
        $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);

        foreach($lEmployees as $key => $emp){
            $from = Carbon::parse($emp->benefits_date);
            $to = Carbon::today()->locale('es');
            $human = $to->diffForHumans($from, true, false, 6);
            $emp->antiquity = $human;
            $orgJob = orgChartUtils::getDirectChildsOrgChartJob($emp->org_chart_job_id);

            $lEmployees[$key] = EmployeeVacationUtils::getEmployeeVacationsData($emp->id);
            if(count($orgJob) > 0){
                $lEmployees[$key]->is_head_user = true;
            }else{
                $lEmployees[$key]->is_head_user = false;
            }
            $from = Carbon::parse($lEmployees[$key]->benefits_date);
            $to = Carbon::today()->locale('es');
    
            $human = $to->diffForHumans($from, true, false, 6);
            $lEmployees[$key]->antiquity = $human;
        }

        return $lEmployees;
    }

    /**
     * regresa las vacaciones de todos los empleados por debajo del usuario
     */
    public function getAlllEmployees($orgJobId, $config){
        $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($orgJobId);
        $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);

        foreach($lEmployees as $key => $emp){
            $orgJob = orgChartUtils::getDirectChildsOrgChartJob($emp->org_chart_job_id);
            $lEmployees[$key] = EmployeeVacationUtils::getEmployeeVacationsData($emp->id);
            $from = Carbon::parse($emp->benefits_date);
            $to = Carbon::today()->locale('es');
            $human = $to->diffForHumans($from, true, false, 6);
            $lEmployees[$key]->antiquity = $human;
            if(count($orgJob) > 0){
                $lEmployees[$key]->is_head_user = true;
                $lEmployees[$key]->subEmployees = $this->getAlllEmployees($emp->org_chart_job_id, $config);
            }else{
                $lEmployees[$key]->is_head_user = false;
                $lEmployees[$key]->subEmployees = [];
            }
        }

        return $lEmployees;
    }

    /**
     * Reporte todas las vacaciones
     */
    public function allVacationsIndex(){
        $lEmployees = \DB::table('users')
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->where('id', '!=', 1)
                        ->select('id', 'employee_num', 'full_name', 'full_name_ui')
                        ->get();

        $lEmployees = EmployeeVacationUtils::getVacations($lEmployees);

        $year = Carbon::now()->year;

        return view('emp_vacations.all_vacations')->with('lEmployees', $lEmployees)
                                                ->with('year', $year);
    }

    /**
     * Reporte todas las vacaciones a partir de año
     */
    public function allVacations(Request $request){
        try {
            $lEmployees = \DB::table('users')
                            ->where('is_delete', 0)
                            ->where('is_active', 1)
                            ->where('id', '!=', 1)
                            ->select('id', 'employee_num', 'full_name', 'full_name_ui')
                            ->get();
    
            $lEmployees = EmployeeVacationUtils::getVacations($lEmployees, $request->startYear);
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lEmployees' => $lEmployees]);
    }

    public function getVacationHistory(Request $request){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $user = EmployeeVacationUtils::getEmployeeVacationsData($request->user_id, true);
        } catch (\Throwable $th) {
            return json_encode(['success' => true, 'message' => 'Error al obtener los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $user]);
    }

    public function hiddeHistory(Request $request){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $user = EmployeeVacationUtils::getEmployeeVacationsData($request->user_id);
        } catch (\Throwable $th) {
            return json_encode(['success' => true, 'message' => 'Error al obtener los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $user]);
    }
}

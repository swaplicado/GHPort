<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Utils\delegationUtils;
use App\Utils\orgChartUtils;
use Carbon\Carbon;
use App\Utils\EmployeeVacationUtils;
use App\Utils\usersInSystemUtils;

class annAllUsersChildsController extends Controller
{
    // vista aniversarios todos los empleados
    public function employeesAllAnn() {
        $config = \App\Utils\Configuration::getConfigurations();
        $lannUsersChilds = $this->getAlllEmployees(delegationUtils::getOrgChartJobIdUser());

        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->endOfMonth()->toDateString();

        $lannUsersChilds = usersInSystemUtils::FilterUsersInSystem($lannUsersChilds, 'id');
        return view('users.annAllUsersChilds')->with('lannUsersChilds', $lannUsersChilds)
                                            ->with('config', $config)
                                            ->with('startDate', $startDate)
                                            ->with('endDate', $endDate);
    }

    public function getAlllEmployees($orgJobId){
        $config = \App\Utils\Configuration::getConfigurations();
        $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJob($orgJobId);
        $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);

        foreach($lEmployees as $key => $emp){
            $orgJob = orgChartUtils::getAllChildsOrgChartJob($emp->org_chart_job_id);
            $lEmployees[$key] = $this->getlannAllUsersChilds($emp->id);
            //$lEmployees[$key] = EmployeeVacationUtils::getEmployeeVacationsData($emp->id);
            $from = Carbon::parse($emp->benefits_date);
            $to = Carbon::today()->locale('es');
            $human = $to->diffForHumans($from, true, false, 6);
            $lEmployees[$key]->antiquity = $human;
            if(count($orgJob) > 0){
                $lEmployees[$key]->is_head_user = true;
                $lEmployees[$key]->subEmployees = $this->getAlllEmployees($emp->org_chart_job_id);
            }else{
                $lEmployees[$key]->is_head_user = false;
                $lEmployees[$key]->subEmployees = [];
            }
        }

        return $lEmployees;
    }

    /**
     * Obtiene la lista de empleados a partir de un arreglo con los id
     */
    public static function getlannAllUsersChilds($id){
        $lEmployees = \DB::table('users as u')
                        ->where('u.is_active', 1)
                        ->where('u.is_delete', 0)
                        ->where('u.id', $id)
                        ->join('org_chart_jobs as cj', 'cj.id_org_chart_job', '=', 'u.org_chart_job_id')
                        ->select('u.full_name as name', 'u.benefits_date as ann', 'u.birthday_n as birth', 'u.org_chart_job_id',  'cj.job_name as area', 'u.id')
                        ->orderBy('cj.job_name', 'asc', 'u.full_name', 'asc')
                        ->first();
        return $lEmployees;
    }
    
}
<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Utils\delegationUtils;
use App\Utils\orgChartUtils;
use Carbon\Carbon;
use App\Utils\EmployeeVacationUtils;

class annUsersChildsController extends Controller
{
    // vista aniversarios empleados directos
    public function employeesDirectAnn() {
        $config = \App\Utils\Configuration::getConfigurations();
        $lannUsersChilds = $this->getDirectEmployees(delegationUtils::getOrgChartJobIdUser());

        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->endOfMonth()->toDateString();

        return view('users.annUsersChilds')->with('lannUsersChilds', $lannUsersChilds)
                                            ->with('config', $config)
                                            ->with('startDate', $startDate)
                                            ->with('endDate', $endDate);
    }

    /**
     * Regresa los aniversarios de los empleados directos
     */
    public function getDirectEmployees($orgJobId){
        $config = \App\Utils\Configuration::getConfigurations();
        $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($orgJobId);
        $lannUsersChilds = EmployeeVacationUtils::getlEmployees($arrOrgJobs);

        foreach($lannUsersChilds as $key => $emp){
            $from = Carbon::parse($emp->benefits_date);
            $to = Carbon::today()->locale('es');
            $human = $to->diffForHumans($from, true, false, 6);
            $emp->antiquity = $human;
            $orgJob = $this->getOrgJobs($emp->org_chart_job_id);

            $lannUsersChilds[$key] = $this->getlannUsersChilds($emp->id);
            if(count($orgJob) > 0){
                $lannUsersChilds[$key]->is_head_user = true;
            }else{
                $lannUsersChilds[$key]->is_head_user = false;
            }
            $from = Carbon::parse($lannUsersChilds[$key]->ann);
            $to = Carbon::today()->locale('es');
    
            $human = $to->diffForHumans($from, true, false, 6);
            $lannUsersChilds[$key]->antiquity = $human;
        }

        return $lannUsersChilds;
    }

    /**
     * Obtiene la lista de áreas funcionales a partir de un arreglo con los id de los org_jobs
     */
    public static function getOrgJobs($arrOrgJobs){
        $lEmployees = \DB::table('users as u')
                        ->where('u.is_active', 1)
                        ->where('u.is_delete', 0)
                        ->where('u.org_chart_job_id', $arrOrgJobs)
                        ->select('*')
                        ->get();
        return $lEmployees;
    }
    
    /**
     * Obtiene la lista de empleados a partir de un arreglo con los id
     */
    public static function getlannUsersChilds($id){
        $lEmployees = \DB::table('users as u')
                        ->where('u.is_active', 1)
                        ->where('u.is_delete', 0)
                        ->where('u.id', $id)
                        ->join('org_chart_jobs as cj', 'cj.id_org_chart_job', '=', 'u.org_chart_job_id')
                        ->select('u.full_name as name', 'u.benefits_date as ann', 'u.birthday_n as birth', 'u.org_chart_job_id', 'cj.job_name as area', 'u.id')
                        ->orderBy('cj.job_name', 'asc', 'u.full_name', 'asc')
                        ->first();
        return $lEmployees;
    }
}
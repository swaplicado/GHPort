<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Utils\usersInSystemUtils;
use Illuminate\Http\Request;
use App\User;
use App\Models\Adm\OrgChartJob;

class empVSOrgChartController extends Controller
{
    public function getlUsers(){
        // $lUsers = User::leftJoin('org_chart_jobs as or', 'or.id_org_chart_job', '=', 'users.org_chart_job_id')
        //                 ->leftJoin('org_chart_jobs as orTop', 'orTop.id_org_chart_job', '=', 'or.top_org_chart_job_id_n')
        //                 ->leftJoin('ext_jobs as j', 'j.id_job', '=', 'users.job_id')
        //                 ->leftJoin('ext_departments as d', 'd.id_department', '=', 'j.department_id')
        //                 ->where('users.is_active', 1)
        //                 ->where('users.is_delete', 0)
        //                 ->where('users.id', '!=', 1)
        //                 ->where('users.show_in_system', 1)
        //                 ->select(
        //                     'users.id',
        //                     'users.full_name_ui',
        //                     'users.org_chart_job_id',
        //                     'or.id_org_chart_job',
        //                     'or.job_name_ui',
        //                     'or.top_org_chart_job_id_n',
        //                     'orTop.id_org_chart_job as id_org_chart_job_top',
        //                     'orTop.job_name_ui as job_name_ui_top',
        //                     'd.department_name_ui as department',
        //                     'j.job_name_ui as job'
        //                 )
        //                 ->orderBy('users.full_name_ui', 'asc')
        //                 ->get();

        $lUsers = \DB::table('ext_departments as d')
                        ->leftJoin('ext_jobs as j', function($join) {
                            $join->on('j.department_id', '=', 'd.id_department')
                                 ->where('j.is_deleted', 0);
                        })
                        ->leftJoin('users as u', function($join) {
                            $join->on('u.job_id', '=', 'j.id_job')
                                 ->where('u.is_active', 1)
                                 ->where('u.is_delete', 0)
                                 ->where('u.id', '!=', 1)
                                 ->where('u.show_in_system', 1);
                        })
                        ->leftJoin('org_chart_jobs as or', 'or.id_org_chart_job', '=', 'u.org_chart_job_id')
                        ->leftJoin('org_chart_jobs as orTop', 'orTop.id_org_chart_job', '=', 'or.top_org_chart_job_id_n')
                        ->where('d.is_deleted', 0)
                        ->select(
                            'u.id',
                            'u.full_name_ui',
                            'u.org_chart_job_id',
                            'or.id_org_chart_job',
                            'or.job_name_ui',
                            'or.top_org_chart_job_id_n',
                            'orTop.id_org_chart_job as id_org_chart_job_top',
                            'orTop.job_name_ui as job_name_ui_top',
                            'd.department_name_ui as department',
                            'j.job_name_ui as job'
                        )
                        ->orderBy('d.department_name_ui', 'asc')
                        ->orderBy('j.job_name_ui', 'asc')
                        ->orderBy('u.full_name_ui', 'asc')
                        ->get();

        return $lUsers;
    }

    public function index(){
        $lUsers = $this->getlUsers();
        $lOrgChart = OrgChartJob::where('is_deleted', 0)->orderBy('job_name', 'asc')->get();

        $lUsers = usersInSystemUtils::FilterUsersInSystem($lUsers, 'id');
        return view('Adm.empVSOrgChart')->with('lUsers', $lUsers)
                                        ->with('lOrgChart', $lOrgChart);
    }

    public function update(Request $request){
        try {
            \DB::beginTransaction();
            $oUser = User::findOrFail($request->user_id);
            $oUser->org_chart_job_id = $request->selOrgChart_id;
            $oUser->org_chart_job_modified = 1;
            $oUser->update();

            $lUsers = $this->getlUsers();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lUsers' => $lUsers]);
    }
}

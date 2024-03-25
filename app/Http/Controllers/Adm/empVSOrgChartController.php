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
        $lUsers = User::leftJoin('org_chart_jobs as or', 'or.id_org_chart_job', '=', 'users.org_chart_job_id')
                        ->leftJoin('org_chart_jobs as orTop', 'orTop.id_org_chart_job', '=', 'or.top_org_chart_job_id_n')
                        ->where('is_active', 1)
                        ->where('is_delete', 0)
                        ->where('id', '!=', 1)
                        ->select(
                            'users.id',
                            'users.full_name_ui',
                            'users.org_chart_job_id',
                            'or.id_org_chart_job',
                            'or.job_name_ui',
                            'or.top_org_chart_job_id_n',
                            'orTop.id_org_chart_job as id_org_chart_job_top',
                            'orTop.job_name_ui as job_name_ui_top',
                        )
                        ->get();

        return $lUsers;
    }

    public function index(){
        $lUsers = $this->getlUsers();
        $lOrgChart = OrgChartJob::where('is_deleted', 0)->get();

        $lUsers = usersInSystemUtils::FilterUsersInSystem($lUsers, 'id');
        return view('Adm.empVSOrgChart')->with('lUsers', $lUsers)
                                        ->with('lOrgChart', $lOrgChart);
    }

    public function update(Request $request){
        try {
            \DB::beginTransaction();
            $oUser = User::findOrFail($request->user_id);
            $oUser->org_chart_job_id = $request->selOrgChart_id;
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

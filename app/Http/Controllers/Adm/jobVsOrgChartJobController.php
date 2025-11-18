<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Utils\OrgChartUtils;
use Illuminate\Http\Request;
use App\Models\Adm\jobVsOrgChartJob;
use App\Models\Adm\OrgChartJob;

class jobVsOrgChartJobController extends Controller
{
    public function index(){
        $lJobVsOrgChartJob = \DB::table('ext_jobs_vs_org_chart_job as jj')
                                ->rightJoin('ext_jobs as j', 'j.id_job', '=', 'jj.ext_job_id')
                                ->leftJoin('org_chart_jobs as oj', 'oj.id_org_chart_job', '=', 'jj.org_chart_job_id_n')
                                ->leftJoin('ext_departments as d', 'd.id_department', '=', 'j.department_id')
                                ->select(
                                    'jj.*',
                                    'j.id_job',
                                    'j.job_name_ui as job',
                                    'oj.id_org_chart_job',
                                    'oj.job_name_ui as orgChart',
                                    'oj.positions',
                                    'd.department_name_ui as department'
                                )
                                ->where('j.id_job', '!=', 1)
                                ->where('j.is_deleted', 0)
                                ->where('d.is_deleted', 0)
                                ->orderBy('j.job_name_ui', 'asc')
                                ->get();

        $lJobs = \DB::table('ext_jobs')->where('is_deleted', 0)->orderBy('job_name', 'asc')->get();
        $lOrgChartJobs = \DB::table('org_chart_jobs')->where('is_deleted', 0)->orderBy('job_name', 'asc')->get();

        return view('Adm.jobVsOrgChartJob')->with('lJobVsOrgChartJob', $lJobVsOrgChartJob)
                                        ->with('lJobs', $lJobs)
                                        ->with('lOrgChartJobs', $lOrgChartJobs);
    }

    public function update(Request $request){
        try {
            \DB::beginTransaction();
            $ojobVsOrgChartJob = jobVsOrgChartJob::find($request->jobVsOrgChartJob_id);
            if(is_null($ojobVsOrgChartJob)){
                $ojobVsOrgChartJob = new jobVsOrgChartJob();
                $ojobVsOrgChartJob->ext_job_id = $request->job_id; 
                $ojobVsOrgChartJob->org_chart_job_id_n = $request->orgChart_id;
                $ojobVsOrgChartJob->save();
            }else{
                $ojobVsOrgChartJob->org_chart_job_id_n = $request->orgChart_id;
                $ojobVsOrgChartJob->update();

                OrgChartUtils::updateUserOrgChartJobByJob($ojobVsOrgChartJob->ext_job_id);
            }

            $oOrgChartJob = OrgChartJob::find($request->orgChart_id);
            $oOrgChartJob->positions = $request->positions;
            $oOrgChartJob->update();

            $lJobVsOrgChartJob = \DB::table('ext_jobs_vs_org_chart_job as jj')
                                ->rightJoin('ext_jobs as j', 'j.id_job', '=', 'jj.ext_job_id')
                                ->leftJoin('org_chart_jobs as oj', 'oj.id_org_chart_job', '=', 'jj.org_chart_job_id_n')
                                ->leftJoin('ext_departments as d', 'd.id_department', '=', 'j.department_id')
                                ->select(
                                    'jj.*',
                                    'j.id_job',
                                    'j.job_name_ui as job',
                                    'oj.id_org_chart_job',
                                    'oj.job_name_ui as orgChart',
                                    'oj.positions',
                                    'd.department_name_ui as department'
                                )
                                ->where('j.id_job', '!=', 1)
                                ->where('j.is_deleted', 0)
                                ->where('d.is_deleted', 0)
                                ->orderBy('j.job_name_ui', 'asc')
                                ->get();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lJobVsOrgChartJob' => $lJobVsOrgChartJob]);
    }
}

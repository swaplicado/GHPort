<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Adm\Area;
use App\Models\Adm\AreaHeadUser;
use App\User;
class OrgChartController extends Controller
{
    public function index(){
        // $areas = \DB::table('org_chart_jobs as ocj')
        //             ->leftJoin('users as u', 'u.org_chart_job_id', '=', 'ocj.id_org_chart_job')
        //             ->where('u.is_active', 1)
        //             ->where('u.is_delete', 0)
        //             ->where('ocj.is_deleted', 0)
        //             ->where('ocj.id_org_chart_job', '!=', 1)
        //             ->select(
        //                 'ocj.id_org_chart_job as id_area',
        //                 'ocj.top_org_chart_job_id_n as father_area_id',
        //                 'u.full_name as user',
        //                 'ocj.job_name',
        //                 'ocj.positions'
        //                 )
        //             ->get();

        $areas = \DB::table('org_chart_jobs as ocj')
                    ->where('ocj.is_deleted', 0)
                    ->where('ocj.positions', '>', 0)
                    ->where('ocj.id_org_chart_job', '!=', 1)
                    ->get();

        foreach($areas as $area){
            if($area->positions == 1){
                $area->users = User::where([['is_active', 1], ['is_delete', 0], ['org_chart_job_id', $area->id_org_chart_job]])->value('full_name');
                $area->is_head = true;
            }else{
                $area->users = User::where([['is_active', 1], ['is_delete', 0], ['org_chart_job_id', $area->id_org_chart_job]])->select('full_name')->get()->toArray();
                $area->is_head = false;
            }
        }
        
        $lAreas = [];
        foreach($areas as $ar){
            if($ar->is_head){
                $lAreas[] = [
                    'id' => $ar->id_org_chart_job,
                    'pid' => $ar->top_org_chart_job_id_n,
                    'name' => $ar->users,
                    'title' => $ar->job_name,
                    'img' => "https://cdn.balkan.app/shared/2.jpg",
                    'jobs' => '1/'.$ar->positions,
                    'tags' => ['']
                ];
            }else{
                $lAreas[] = [
                    'id' => $ar->id_org_chart_job,
                    'pid' => $ar->top_org_chart_job_id_n,
                    'name' => '',
                    'title' => $ar->job_name,
                    'img' => count($ar->users) > 0 ? "https://cdn.balkan.app/shared/14.jpg" : "https://cdn.balkan.app/shared/empty-img-none.svg",
                    'jobs' => count($ar->users).'/'.$ar->positions,
                    'tags' => [ count($ar->users) < 1 ? "yellow" : '']
                ];
            }
        }

        return view('Adm.OrgChart')->with('lAreas', $lAreas);
    }
}
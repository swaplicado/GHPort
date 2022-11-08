<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Adm\Area;
use App\Models\Adm\AreaHeadUser;
use App\User;
use App\Models\Adm\OrgChartJob;
use App\Constants\SysConst;
class OrgChartController extends Controller
{
    public function index(){
        $areas = \DB::table('org_chart_jobs as ocj')
                    ->where('ocj.is_deleted', 0)
                    ->where('ocj.positions', '>', 0)
                    ->where('ocj.id_org_chart_job', '!=', 1)
                    ->get();

        foreach($areas as $area){
            if($area->positions == 1){
                $area->users = User::where([['is_active', 1], ['is_delete', 0], ['org_chart_job_id', $area->id_org_chart_job]])->select('full_name')->get()->toArray();
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
                    'name' => count($ar->users) > 0 ? $ar->users[0]['full_name'] : '',
                    'title' => $ar->job_name,
                    'img' => count($ar->users) > 0 ? "https://cdn.balkan.app/shared/2.jpg" : "https://cdn.balkan.app/shared/empty-img-none.svg",
                    'jobs' => count($ar->users).'/'.$ar->positions,
                    'tags' => [ count($ar->users) < 1 ? "yellow" : '']
                ];
            }else{
                $lAreas[] = [
                    'id' => $ar->id_org_chart_job,
                    'pid' => $ar->top_org_chart_job_id_n,
                    'name' => '',
                    'title' => $ar->job_name,
                    'img' => count($ar->users) > 0 ? asset('img/group3-com.png') : "https://cdn.balkan.app/shared/empty-img-none.svg",
                    'jobs' => count($ar->users).'/'.$ar->positions,
                    'tags' => [ count($ar->users) < 1 ? "yellow" : '']
                ];
            }
        }

        return view('Adm.OrgChart')->with('lAreas', $lAreas);
    }

    public function assignArea(){
        \Auth::user()->authorizedRole([SysConst::ADMINISTRADOR, SysConst::GH]);
        $areas = \DB::table('org_chart_jobs as ocj')
                    ->where('ocj.is_deleted', 0)
                    ->where('ocj.positions', '>', 0)
                    ->where('ocj.id_org_chart_job', '!=', 1)
                    ->get();

        foreach($areas as $area){
            $ar = !is_null($area->top_org_chart_job_id_n) ? $areas->where('id_org_chart_job', $area->top_org_chart_job_id_n)->first() : null;
            $area->top_org_chart_job = !is_null($ar) ? $ar->job_name : null;
            if($area->positions == 1){
                $head_user = User::where([['is_active', 1], ['is_delete', 0], ['org_chart_job_id', $area->id_org_chart_job]])->first();
                $area->head_user_id = !is_null($head_user) ? $head_user->id : null;
                $area->head_user = !is_null($head_user) ? $head_user->full_name : null;
            }else{
                $area->head_user_id = null;
                $area->head_user = null;
            }
        }

        $users = \DB::table('users')
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->select('id', 'full_name as text')
                    ->get();

        return view('Adm.assignArea')->with('lAreas', $areas)
                                    ->with('lUsers', $users);
    }

    public function updateAssignArea(Request $request){
        \Auth::user()->authorizedRole([SysConst::ADMINISTRADOR, SysConst::GH]);
        /** FALTA ACTUALIZAR HEAD USER */
        try {
            \DB::beginTransaction();
                $area = OrgChartJob::findOrFail($request->org_chart_job);
                $area->top_org_chart_job_id_n = $request->top_org_chart_job_id;
                $area->update();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al actualizar el registro']);
        }

        $areas = \DB::table('org_chart_jobs as ocj')
                    ->where('ocj.is_deleted', 0)
                    ->where('ocj.positions', '>', 0)
                    ->where('ocj.id_org_chart_job', '!=', 1)
                    ->get();

        foreach($areas as $area){
            $ar = !is_null($area->top_org_chart_job_id_n) ? $areas->where('id_org_chart_job', $area->top_org_chart_job_id_n)->first() : null;
            $area->top_org_chart_job = !is_null($ar) ? $ar->job_name : null;
            if($area->positions == 1){
                $head_user = User::where([['is_active', 1], ['is_delete', 0], ['org_chart_job_id', $area->id_org_chart_job]])->first();
                $area->head_user_id = !is_null($head_user) ? $head_user->id : null;
                $area->head_user = !is_null($head_user) ? $head_user->full_name : null;
            }else{
                $area->head_user_id = null;
                $area->head_user = null;
            }
        }

        return json_encode(['success' => true, 'message' => 'Registro actualizadó con exitó', 'lAreas' => $areas]);
    }
}
<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Adm\Area;
use App\Models\Adm\AreaHeadUser;
use App\User;
use App\Models\Adm\OrgChartJob;
use App\Constants\SysConst;
use \App\Utils\delegationUtils;
use \App\Utils\orgChartUtils;
class OrgChartController extends Controller
{
    public function index(){
        $areas = \DB::table('org_chart_jobs as ocj')
                    ->leftJoin('organization_levels as ol', 'ol.id_organization_level', '=', 'ocj.org_level_id')
                    ->where('ocj.is_deleted', 0)
                    ->where('ocj.positions', '>', 0)
                    ->where('ocj.id_org_chart_job', '!=', 1)
                    ->select(
                        'ocj.*',
                        'ol.name as level'
                    )
                    ->get();

        foreach($areas as $area){
            if($area->positions == 1){
                $area->users = User::leftJoin('users_vs_photos as up', 'up.user_id', '=', 'users.id')
                                    ->where([['is_active', 1], ['is_delete', 0], ['org_chart_job_id', $area->id_org_chart_job]])
                                    ->select('full_name', 'up.photo_base64_n as photo64')
                                    ->orderBy('full_name', 'asc')
                                    ->get()
                                    ->toArray();
                $area->is_head = true;
            }else{
                $area->users = User::where([['is_active', 1], ['is_delete', 0], ['org_chart_job_id', $area->id_org_chart_job]])
                                    ->select('full_name')
                                    ->orderBy('full_name', 'asc')
                                    ->get()
                                    ->toArray();
                $area->is_head = false;
            }
        }

        $lAreas = [];
        foreach($areas as $ar){
            if($ar->is_head){
                $lAreas[] = [
                    'name' => count($ar->users) > 0 ? $ar->users[0]['full_name'] : '',
                    'imageUrl' => count($ar->users) > 0 ? 
                        ($ar->users[0]['photo64'] != null ? 
                            "data:image/jpg;base64,".$ar->users[0]['photo64'] : 
                                asset('img/sin_fotografia.png')) : 
                                    asset('img/vacante.png'),
                    'positionName' => $ar->job_name,
                    'id' => $ar->id_org_chart_job,
                    'parentId' => $ar->top_org_chart_job_id_n,
                    'jobs' => count($ar->users).'/'.$ar->positions,
                    'countUsers' => count($ar->users),
                    'level' => $ar->level,
                ];
            }else{
                $lAreas[] = [
                    'name' => '',
                    'imageUrl' => count($ar->users) > 0 ? asset('img/multiple-users-silhouette.png') : asset('img/vacante.png'),
                    'positionName' => $ar->job_name,
                    'id' => $ar->id_org_chart_job,
                    'parentId' => $ar->top_org_chart_job_id_n,
                    'jobs' => count($ar->users).'/'.$ar->positions,
                    'countUsers' => 2,
                    'level' => $ar->level,
                ];
            }
        }

        return view('Adm.OrgChart')->with('lAreas', $lAreas);
    }

    public function assignArea(){
        // \Auth::user()->authorizedRole([SysConst::ADMINISTRADOR, SysConst::GH]);
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        $areas = \DB::table('org_chart_jobs as ocj')
                    ->join('organization_levels', 'ocj.org_level_id', '=', 'organization_levels.id_organization_level')
                    ->where('ocj.is_deleted', 0)
                    ->where('ocj.positions', '>', 0)
                    ->where('ocj.id_org_chart_job', '!=', 1)
                    ->orderBy('ocj.job_name', 'asc')
                    ->get();

        foreach($areas as $area){
            $ar = !is_null($area->top_org_chart_job_id_n) ? $areas->where('id_org_chart_job', $area->top_org_chart_job_id_n)->first() : null;
            $area->top_org_chart_job = !is_null($ar) ? $ar->job_name : null;
            $childs = orgChartUtils::getDirectChildsOrgChartJob($area->id_org_chart_job);
            $childs = count($childs);

            $area->childs = $childs;
            // se puede analizar retirar esta parte del codigo.
            if($area->positions == 1){
                $head_user = User::where([['is_active', 1], ['is_delete', 0], ['org_chart_job_id', $area->id_org_chart_job]])->first();
                $area->head_user_id = !is_null($head_user) ? $head_user->id : null;
                $area->head_user = !is_null($head_user) ? $head_user->full_name : null;
            }else{
                $area->head_user_id = null;
                $area->head_user = null;
            }
            $area->org_level = $area->level. ' - '. $area->name;
            
        }

        $users = \DB::table('users')
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->select('id', 'full_name as text')
                    ->get();
        
        $levels = \DB::table('organization_levels')
                    ->get();

        return view('Adm.assignArea')->with('lAreas', $areas)
                                    ->with('lUsers', $users)
                                    ->with('lLevels', $levels);
    }

    public function updateAssignArea(Request $request){
        // \Auth::user()->authorizedRole([SysConst::ADMINISTRADOR, SysConst::GH]);
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        
        try {
            \DB::beginTransaction();
                $area = OrgChartJob::findOrFail($request->org_chart_job);
                $area->job_code = $request->area;
                $area->job_name = $request->area;
                $area->job_name_ui = $request->area; 
                $area->org_level_id = $request->org_level_id;
                $area->positions = $request->job_num;
                $area->is_leader_area = $request->leader;
                $area->is_boss = $request->leader;
                $area->is_leader_config = $request->config_leader;
                $area->top_org_chart_job_id_n = $request->top_org_chart_job_id;
                $area->is_deleted = 0;
                $area->updated_by = \Auth::user()->id;
                $area->update();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema']);
        }

        $areas = \DB::table('org_chart_jobs as ocj')
                    ->join('organization_levels', 'ocj.org_level_id', '=', 'organization_levels.id_organization_level')
                    ->where('ocj.is_deleted', 0)
                    ->where('ocj.positions', '>', 0)
                    ->where('ocj.id_org_chart_job', '!=', 1)
                    ->get();

        foreach($areas as $area){
            $ar = !is_null($area->top_org_chart_job_id_n) ? $areas->where('id_org_chart_job', $area->top_org_chart_job_id_n)->first() : null;
            $area->top_org_chart_job = !is_null($ar) ? $ar->job_name : null;
            $childs = orgChartUtils::getDirectChildsOrgChartJob($area->id_org_chart_job);
            $childs = count($childs);

            $area->childs = $childs;
            if($area->positions == 1){
                $head_user = User::where([['is_active', 1], ['is_delete', 0], ['org_chart_job_id', $area->id_org_chart_job]])->first();
                $area->head_user_id = !is_null($head_user) ? $head_user->id : null;
                $area->head_user = !is_null($head_user) ? $head_user->full_name : null;
            }else{
                $area->head_user_id = null;
                $area->head_user = null;
            }
            $area->org_level = $area->level. ' - '. $area->name;
        }

        return json_encode(['success' => true, 'message' => 'Registro actualizadó con exitó', 'lAreas' => $areas]);
    }

    public function getUsers(Request $request){
        try {
            $lUser = User::leftJoin('users_vs_photos as up', 'up.user_id', '=', 'users.id')
                        ->where('org_chart_job_id', $request->orgChart_id)
                        ->where('is_active', 1)
                        ->where('is_delete', 0)
                        ->select('users.*', 'up.photo_base64_n')
                        ->orderBy('full_name', 'asc')
                        ->get();
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lUser' => $lUser]);
    }
    public function createAssignArea(Request $request){
        
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        
        try {
            \DB::beginTransaction();
                $area = new OrgChartJob();
                $area->job_code = $request->area;
                $area->job_name = $request->area;
                $area->job_name_ui = $request->area; 
                $area->org_level_id = $request->org_level_id;
                $area->positions = $request->job_num;
                $area->is_leader_area = $request->leader;
                $area->is_boss = $request->leader;
                $area->is_leader_config = $request->config_leader;
                $area->top_org_chart_job_id_n = $request->top_org_chart_job_id;
                $area->is_deleted = 0;
                $area->created_by = \Auth::user()->id;
                $area->updated_by = \Auth::user()->id;
                $area->save();
            \DB::commit();
        } catch (\Throwable $th) {
            //Log::emergency($th->getMessage());
            \DB::rollback();
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema']);
        }

        $areas = \DB::table('org_chart_jobs as ocj')
                    ->join('organization_levels', 'ocj.org_level_id', '=', 'organization_levels.id_organization_level')
                    ->where('ocj.is_deleted', 0)
                    ->where('ocj.positions', '>', 0)
                    ->where('ocj.id_org_chart_job', '!=', 1)
                    ->get();

        foreach($areas as $area){
            $ar = !is_null($area->top_org_chart_job_id_n) ? $areas->where('id_org_chart_job', $area->top_org_chart_job_id_n)->first() : null;
            $area->top_org_chart_job = !is_null($ar) ? $ar->job_name : null;
            $childs = orgChartUtils::getDirectChildsOrgChartJob($area->id_org_chart_job);
            $childs = count($childs);

            $area->childs = $childs;
            if($area->positions == 1){
                $head_user = User::where([['is_active', 1], ['is_delete', 0], ['org_chart_job_id', $area->id_org_chart_job]])->first();
                $area->head_user_id = !is_null($head_user) ? $head_user->id : null;
                $area->head_user = !is_null($head_user) ? $head_user->full_name : null;
            }else{
                $area->head_user_id = null;
                $area->head_user = null;
            }
            $area->org_level = $area->level. ' - '. $area->name;
        }

        return json_encode(['success' => true, 'message' => 'Registro creado con éxito', 'lAreas' => $areas]);
    }

    public function deleteAssignArea(Request $request){
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        try {
            \DB::beginTransaction();
                $area = OrgChartJob::findOrFail($request->org_chart_job);
                $area->is_deleted = 1;
                $area->updated_by = \Auth::user()->id;
                $area->update();;
                \DB::commit();
        } catch (\Throwable $th) {
            //Log::emergency($th->getMessage());
            \DB::rollback();
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema']);
        }

        $areas = \DB::table('org_chart_jobs as ocj')
                    ->join('organization_levels', 'ocj.org_level_id', '=', 'organization_levels.id_organization_level')
                    ->where('ocj.is_deleted', 0)
                    ->where('ocj.positions', '>', 0)
                    ->where('ocj.id_org_chart_job', '!=', 1)
                    ->get();

        foreach($areas as $area){
            $ar = !is_null($area->top_org_chart_job_id_n) ? $areas->where('id_org_chart_job', $area->top_org_chart_job_id_n)->first() : null;
            $area->top_org_chart_job = !is_null($ar) ? $ar->job_name : null;
            $childs = orgChartUtils::getDirectChildsOrgChartJob($area->id_org_chart_job);
            $childs = count($childs);

            $area->childs = $childs;
            if($area->positions == 1){
                $head_user = User::where([['is_active', 1], ['is_delete', 0], ['org_chart_job_id', $area->id_org_chart_job]])->first();
                $area->head_user_id = !is_null($head_user) ? $head_user->id : null;
                $area->head_user = !is_null($head_user) ? $head_user->full_name : null;
            }else{
                $area->head_user_id = null;
                $area->head_user = null;
            }
            $area->org_level = $area->level. ' - '. $area->name;
        }

        return json_encode(['success' => true, 'message' => 'Registro eliminado con exitó', 'lAreas' => $areas]);
    }
}
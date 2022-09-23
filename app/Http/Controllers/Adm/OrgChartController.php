<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Adm\Area;
use App\Models\Adm\AreaHeadUser;
class OrgChartController extends Controller
{
    public function index(){
        $areas = \DB::table('adm_areas as a')
                    ->leftJoin('adm_areas_users as au', 'a.id_area', '=',  'au.area_id')
                    ->leftJoin('users as u', 'u.id', '=', 'au.head_user_id')
                    ->where(function ($query){
                        $query->where('au.is_deleted', 0)
                            ->orWhere('au.is_deleted', null);
                    })
                    ->where('a.is_deleted', 0)
                    ->select('a.id_area', 'a.area', 'a.father_area_id', 'u.full_name as head_user')
                    ->get();

        $lAreas = [];
        foreach($areas as $ar){
            $lAreas[] = ['id' => $ar->id_area, 'pid' => $ar->father_area_id, 'name' => $ar->head_user, 'title' => $ar->area, 'img' => "https://cdn.balkan.app/shared/16.jpg" ];
        }

        return view('Adm.OrgChart')->with('lAreas', $lAreas);
    }

    public function assignArea(){
        $areas = \DB::table('adm_areas as a')
                    ->leftJoin('adm_areas_users as au', 'a.id_area', '=',  'au.area_id')
                    ->leftJoin('users as u', 'u.id', '=', 'au.head_user_id')
                    ->where(function ($query){
                        $query->where('au.is_deleted', 0)
                            ->orWhere('au.is_deleted', null);
                    })
                    ->where('a.is_deleted', 0)
                    ->select('a.id_area', 'a.area', 'a.father_area_id', 'u.full_name as head_user',  'u.id as user_id')
                    ->get();

        $users = \DB::table('users')
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->select('id', 'full_name as text')
                    ->get();

        foreach($areas as $area){
            $ar = $areas->where('id_area', $area->father_area_id)->first();
            if(!is_null($ar)){
                $area->father_area = $ar->area;
            }else{
                $area->father_area = null;
            }
        }

        return view('Adm.assignArea')->with('lAreas', $areas)
                                    ->with('lUsers', $users);
    }

    public function updateAssignArea(Request $request){
        try {
            \DB::beginTransaction();
                $area = Area::findOrFail($request->area_id);
                $area->father_area_id = $request->father_area_id;
                $area->update();

                $areaHeadUser = AreaHeadUser::where([['area_id', $request->area_id], ['is_deleted', 0]])->first();
                if(!is_null($areaHeadUser)){
                    $areaHeadUser->head_user_id = $request->superviser_id;
                    $areaHeadUser->update();
                }else{
                    $areaHeadUser = new AreaHeadUser();
                    $areaHeadUser->area_id = $request->area_id;
                    $areaHeadUser->head_user_id = $request->superviser_id;
                    $areaHeadUser->is_deleted = 0;
                    $areaHeadUser->created_by_id = \Auth::user()->id;
                    $areaHeadUser->updated_by_id = \Auth::user()->id;
                    $areaHeadUser->save();
                }
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al actualizar el registro']);
        }
        return json_encode(['success' => true, 'message' => 'Registro actualizadó con exitó']);
    }
}

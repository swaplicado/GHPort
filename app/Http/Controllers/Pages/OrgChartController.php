<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        return view('OrgChart')->with('lAreas', $lAreas);
    }
}

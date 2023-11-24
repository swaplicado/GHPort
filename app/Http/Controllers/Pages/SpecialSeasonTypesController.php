<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seasons\SpecialSeasonType;
use \App\Utils\delegationUtils;

class SpecialSeasonTypesController extends Controller
{
    public function index(){
        $lSpecialSeasonType = SpecialSeasonType::leftJoin('users as u', 'u.id', '=', 'special_season_types.updated_by')
                                            ->where('special_season_types.is_deleted', 0)
                                            ->select(
                                                'special_season_types.*',
                                                'u.full_name_ui',
                                            )
                                            ->get();

        return view('Adm.specialSeasonTypes')->with('lSpecialSeasonType', $lSpecialSeasonType);
    }

    public function saveSeasonType(Request $request){
        try {
            $checkSeasonType = SpecialSeasonType::where('priority', $request->priority)->where('is_deleted', 0)->first();
            if(!is_null($checkSeasonType)){
                return json_encode(['success' => false, 'message' => 'Error al guardar el registro, ya existe un tipo de temporada especial con la misma prioridad', 'icon' => 'error']);
            }
            \DB::beginTransaction();
                $oSeasonType = new SpecialSeasonType();
                $oSeasonType->name = $request->name;
                $oSeasonType->key_code = $request->key_code;
                $oSeasonType->priority = $request->priority;
                // $oSeasonType->color = $request->color;
                $oSeasonType->color = $request->hexColor;
                $oSeasonType->description = $request->description;
                // $oSeasonType->created_by = \Auth::user()->id;
                // $oSeasonType->updated_by = \Auth::user()->id;
                $oSeasonType->created_by = delegationUtils::getIdUser();
                $oSeasonType->updated_by = delegationUtils::getIdUser();
                $oSeasonType->save();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al guardar el registro', 'icon' => 'error']);
        }

        $lSpecialSeasonType = SpecialSeasonType::leftJoin('users as u', 'u.id', '=', 'special_season_types.updated_by')
                                                ->where('special_season_types.is_deleted', 0)
                                                ->select(
                                                    'special_season_types.*',
                                                    'u.full_name_ui',
                                                )
                                                ->get();
        return json_encode(['success' => true, 'lSpecialSeasonType' => $lSpecialSeasonType]);
    }

    public function updateSeasonType(Request $request){
        try {
            \DB::beginTransaction();
                $oSeasonType = SpecialSeasonType::find($request->id_special_season_type);
                $oSeasonType->name = $request->name;
                $oSeasonType->key_code = $request->key_code;
                $oSeasonType->priority = $request->priority;
                // $oSeasonType->color = $request->color;
                $oSeasonType->color = $request->hexColor;
                $oSeasonType->description = $request->description;
                // $oSeasonType->updated_by = \Auth::user()->id;
                $oSeasonType->updated_by = delegationUtils::getIdUser();
                $oSeasonType->update();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al guardar el registro', 'icon' => 'error']);
        }

        $lSpecialSeasonType = SpecialSeasonType::leftJoin('users as u', 'u.id', '=', 'special_season_types.updated_by')
                                                ->where('is_deleted', 0)
                                                ->select(
                                                    'special_season_types.*',
                                                    'u.full_name_ui',
                                                )
                                                ->get();
        return json_encode(['success' => true, 'lSpecialSeasonType' => $lSpecialSeasonType]);
    }

    // public function deleteSeasonType(Request $request){
    //     try {
    //         \DB::beginTransaction();
    //             $oSeasonType = SpecialSeasonType::find($request->id_special_season_type);
    //             $oSeasonType->is_deleted = 1;
    //             $oSeasonType->update();
    //         \DB::commit();
    //     } catch (\Throwable $th) {
    //         \DB::rollBack();
    //         return json_encode(['success' => false, 'message' => 'Error al guardar el registro', 'icon' => 'error']);
    //     }

    //     $lSpecialSeasonType = SpecialSeasonType::leftJoin('users as u', 'u.id', '=', 'special_season_types.updated_by')
    //                                             ->where('is_deleted', 0)
    //                                             ->select(
    //                                                 'special_season_types.*',
    //                                                 'u.full_name_ui',
    //                                             )
    //                                             ->get();
    //     return json_encode(['success' => true, 'lSpecialSeasonType' => $lSpecialSeasonType]);
    // }

    public function deleteSeasonType(Request $request){
        try {
            \DB::beginTransaction();
                // $oSeasonType = SpecialSeasonType::where('is_deleted', 0)->where('priority', \DB::raw("(select min(`priority`) from special_season_types)"))->first();
                $oSeasonType = SpecialSeasonType::where('is_deleted', 0)->orderBy('priority', 'asc')->first();
                $oSeasonType->is_deleted = 1;
                $oSeasonType->update();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al eliminar el registro', 'icon' => 'error']);
        }

        $lSpecialSeasonType = SpecialSeasonType::leftJoin('users as u', 'u.id', '=', 'special_season_types.updated_by')
                                                ->where('is_deleted', 0)
                                                ->select(
                                                    'special_season_types.*',
                                                    'u.full_name_ui',
                                                )
                                                ->get();
        return json_encode(['success' => true, 'lSpecialSeasonType' => $lSpecialSeasonType]);
    }
}

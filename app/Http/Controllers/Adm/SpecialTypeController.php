<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adm\SpecialType;


class SpecialTypeController extends Controller
{
    public function index(){
        $lSpecialType = SpecialType::where('is_deleted', 0)->orderBy('priority')->get();
        $config = \App\Utils\Configuration::getConfigurations();
        $lSituation = $config->lSituation;
        foreach($lSpecialType as $st){
            $index = array_search($st->situation, array_column($lSituation, 'id'));
            $text = $lSituation[$index]->text;
            $st->situation_name = $text;
        }

        return view('Adm.special_type')->with('lSpecialType', $lSpecialType)
                                    ->with('lSituation', $lSituation);
    }

    public function save(Request $request){
        try {
            \DB::beginTransaction();
            $oSpecialType = new SpecialType();
            $oSpecialType->name = $request->name;
            $oSpecialType->code = $request->code;
            $oSpecialType->situation = $request->situation_id;
            $oSpecialType->priority = (array_search('0', array_column($request->lOrder, 'id')) + 1);
            $oSpecialType->created_by = \Auth::user()->id;
            $oSpecialType->updated_by = \Auth::user()->id;
            $oSpecialType->save();

            foreach($request->lOrder as $index => $item){
                if($item['id'] != 0){
                    $oSpecialType = SpecialType::findOrFail($item['id']);
                    $oSpecialType->priority = $index + 1;
                    $oSpecialType->update();
                }
            }

            $lSpecialType = SpecialType::where('is_deleted', 0)->orderBy('priority')->get();
            $config = \App\Utils\Configuration::getConfigurations();
            $lSituation = $config->lSituation;
            foreach($lSpecialType as $st){
                $index = array_search($st->situation, array_column($lSituation, 'id'));
                $text = $lSituation[$index]->text;
                $st->situation_name = $text;
            }
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lSpecialType' => $lSpecialType]);
    }

    public function update(Request $request){
        try {
            \DB::beginTransaction();
            $oSpecialType = SpecialType::findOrFail($request->id);
            $oSpecialType->name = $request->name;
            $oSpecialType->code = $request->code;
            $oSpecialType->situation = $request->situation_id;
            $oSpecialType->update();

            foreach($request->lOrder as $index => $item){
                $oSpecialType = SpecialType::findOrFail($item['id']);
                $oSpecialType->priority = $index + 1;
                $oSpecialType->update();
            }

            $lSpecialType = SpecialType::where('is_deleted', 0)->orderBy('priority')->get();
            $config = \App\Utils\Configuration::getConfigurations();
            $lSituation = $config->lSituation;
            foreach($lSpecialType as $st){
                $index = array_search($st->situation, array_column($lSituation, 'id'));
                $text = $lSituation[$index]->text;
                $st->situation_name = $text;
            }

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lSpecialType' => $lSpecialType]);
    }

    public function delete(Request $request){
        try {
            $lSpecialTypeVsOrgChart = \DB::table('cat_special_vs_org_chart')
                                        ->where('cat_special_id', $request->id)
                                        ->where('is_deleted', 0)
                                        ->first();

            if(!is_null($lSpecialTypeVsOrgChart)){
                return json_encode(['success' => false, 'message' => 'No se puede eliminar una solicitud que este asignada', 'icon' => 'error']);
            }

            \DB::beginTransaction();
            $oSpecialType = SpecialType::findOrFail($request->id);
            $oSpecialType->is_deleted = 1;
            $oSpecialType->update();

            $lSpecialType = SpecialType::where('is_deleted', 0)->get();
            $config = \App\Utils\Configuration::getConfigurations();
            $lSituation = $config->lSituation;
            foreach($lSpecialType as $st){
                $index = array_search($st->situation, array_column($lSituation, 'id'));
                $text = $lSituation[$index]->text;
                $st->situation_name = $text;
            }
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al eliminar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lSpecialType' => $lSpecialType]);
    }
}

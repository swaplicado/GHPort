<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Utils\delegationUtils;
use App\User;
use App\Models\Adm\ClIncidence;
use App\Models\Adm\InteractSystem;
use App\Models\Adm\TpIncidence;
use App\Models\Adm\PivotIncidence;
use App\Constants\SysConst;

class TpIncidencesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        $lTpIncidence = \DB::table('cat_incidence_tps as tps')
                            ->join('cat_incidence_cls as cls', 'tps.incidence_cl_id', '=', 'cls.id_incidence_cl')
                            ->join('interact_systems as is', 'tps.interact_system_id', '=', 'is.id_int_sys')
                            ->where('tps.is_deleted',0)
                            ->select('tps.id_incidence_tp AS idTp','tps.incidence_tp_name AS nameTp', 'cls.id_incidence_cl AS idCl', 'cls.incidence_cl_name AS nameCl', 'tps.is_active AS active', 'tps.need_auth AS auth', 'is.id_int_sys AS idSys', 'is.name AS nameSys','tps.is_deleted AS deleted')
                            ->get();
    
        $lClIncidence = ClIncidence::where('is_deleted', 0)->get();
        $lInteractSystem = InteractSystem::where('is_deleted', 0)->get();

        return view('Adm.tpIncidence')->with('lTpIncidence', $lTpIncidence)->with('lClIncidence',$lClIncidence)->with('lInteractSystem',$lInteractSystem);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        
        try {
            \DB::beginTransaction();
                $tp = new TpIncidence();
                $tp->incidence_cl_id = $request->idCl;
                $tp->incidence_tp_name = $request->nameTp;
                $tp->is_active = $request->active; 
                $tp->need_auth = $request->auth;
                $tp->interact_system_id = $request->idSys;
                $tp->external_id = 1;
                $tp->is_deleted = 0;
                $tp->created_by = \Auth::user()->id;
                $tp->updated_by = \Auth::user()->id;
                $tp->save();
            \DB::commit();
        } catch (\Throwable $th) {
            //Log::emergency($th->getMessage());
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al crear el registro']);
        }

        $lTpIncidence = \DB::table('cat_incidence_tps as tps')
                            ->join('cat_incidence_cls as cls', 'tps.incidence_cl_id', '=', 'cls.id_incidence_cl')
                            ->join('interact_systems as is', 'tps.interact_system_id', '=', 'is.id_int_sys')
                            ->where('tps.is_deleted',0)
                            ->select('tps.id_incidence_tp AS idTp','tps.incidence_tp_name AS nameTp', 'cls.id_incidence_cl AS idCl', 'cls.incidence_cl_name AS nameCl', 'tps.is_active AS active', 'tps.need_auth AS auth', 'is.id_int_sys AS idSys', 'is.name AS nameSys','tps.is_deleted AS deleted')
                            ->get();

        return json_encode(['success' => true, 'message' => 'Registro creado con exitó', 'lTpIncidence' => $lTpIncidence]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        
        try {
            \DB::beginTransaction();
                $tp = TpIncidence::findOrFail($request->idTp);
                $tp->incidence_cl_id = $request->idCl;
                $tp->incidence_tp_name = $request->nameTp;
                $tp->is_active = $request->active; 
                $tp->need_auth = $request->auth;
                $tp->interact_system_id = $request->idSys;
                $tp->external_id = 1;
                $tp->is_deleted = 0;
                $tp->updated_by = \Auth::user()->id;
                $tp->update();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al actualizar el registro']);
        }

        $lTpIncidence = \DB::table('cat_incidence_tps as tps')
                            ->join('cat_incidence_cls as cls', 'tps.incidence_cl_id', '=', 'cls.id_incidence_cl')
                            ->join('interact_systems as is', 'tps.interact_system_id', '=', 'is.id_int_sys')
                            ->where('tps.is_deleted',0)
                            ->select('tps.id_incidence_tp AS idTp','tps.incidence_tp_name AS nameTp', 'cls.id_incidence_cl AS idCl', 'cls.incidence_cl_name AS nameCl', 'tps.is_active AS active', 'tps.need_auth AS auth', 'is.id_int_sys AS idSys', 'is.name AS nameSys','tps.is_deleted AS deleted')
                            ->get();

        return json_encode(['success' => true, 'message' => 'Registro actualizado con exitó', 'lTpIncidence' => $lTpIncidence]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        try {
            \DB::beginTransaction();
                $tp = TpIncidence::findOrFail($request->idTp);
                $tp->is_deleted = 1;
                $tp->updated_by = \Auth::user()->id;
                $tp->update();;
                \DB::commit();
        } catch (\Throwable $th) {
            //Log::emergency($th->getMessage());
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al eliminar el registro']);
        }
    }

    public function index_pivot() 
    {
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        $lPivot = \DB::table('tp_incidents_pivot as tip')
                            ->join('cat_incidence_tps as tps', 'tps.id_incidence_tp', '=', 'tip.tp_incident_id')
                            ->join('interact_systems as is', 'tip.int_sys_id', '=', 'is.id_int_sys')
                            ->where('tip.is_deleted',0)
                            ->select('tip.id_pivot AS idPiv','tps.incidence_tp_name AS nameTp', 'tps.id_incidence_tp AS idTp', 'tip.ext_tp_incident_id AS tpExt', 'tip.ext_cl_incident_id AS clExt', 'is.id_int_sys AS idSys', 'is.name AS nameSys')
                            ->get();
        
        $lTpIncidence = TpIncidence::where('is_deleted', 0)->get();
        $lInteractSystem = InteractSystem::where('is_deleted', 0)->get();
                    
        return view('Adm.indexPivot')->with('lPivot', $lPivot)->with('lTpIncidence',$lTpIncidence)->with('lInteractSystem',$lInteractSystem);                    
    }

    public function st_pivot(Request $request)
    {
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        
        $duplicate = \DB::table('tp_incidents_pivot')
                            ->where('tp_incident_id',$request->idTp)
                            ->where('int_sys_id',$request->idSys)
                            ->get();
        if(count($duplicate) > 0){
            return json_encode(['success' => false, 'message' => 'El registro ya existe, si necesitas cambiarlo, usa la opción de editar']);
        }
                            
        try {
            \DB::beginTransaction();
                $tp = new PivotIncidence();
                $tp->tp_incident_id = $request->idTp;
                $tp->ext_tp_incident_id = $request->tpExt;
                $tp->ext_cl_incident_id = $request->clExt; 
                $tp->int_sys_id = $request->idSys;
                $tp->is_deleted = 0;
                $tp->created_by = \Auth::user()->id;
                $tp->updated_by = \Auth::user()->id;
                $tp->save();
            \DB::commit();
        } catch (\Throwable $th) {
            //Log::emergency($th->getMessage());
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al crear el registro']);
        }

        $lPivot = \DB::table('tp_incidents_pivot as tip')
                            ->join('cat_incidence_tps as tps', 'tps.id_incidence_tp', '=', 'tip.tp_incident_id')
                            ->join('interact_systems as is', 'tip.int_sys_id', '=', 'is.id_int_sys')
                            ->where('tip.is_deleted',0)
                            ->select('tip.id_pivot AS idPiv','tps.incidence_tp_name AS nameTp', 'tps.id_incidence_tp AS idTp', 'tip.ext_tp_incident_id AS tpExt', 'tip.ext_cl_incident_id AS clExt', 'is.id_int_sys AS idSys', 'is.name AS nameSys')
                            ->get();

        return json_encode(['success' => true, 'message' => 'Registro creado con exitó', 'lPivot' => $lPivot]);
    }

    public function up_pivot(Request $request)
    {
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        if(count($duplicate) > 0){
            return json_encode(['success' => false, 'message' => 'El registro que tratas de editar choca con un registro ya existente']);
        }
        try {
            \DB::beginTransaction();
                $tp = PivotIncidence::findOrFail($request->idPiv);
                $tp->tp_incident_id = $request->idTp;
                $tp->ext_tp_incident_id = $request->tpExt;
                $tp->ext_cl_incident_id = $request->clExt; 
                $tp->int_sys_id = $request->idSys;
                $tp->is_deleted = 0;
                $tp->updated_by = \Auth::user()->id;
                $tp->update();
            \DB::commit();
        } catch (\Throwable $th) {
            //Log::emergency($th->getMessage());
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al editar el registro']);
        }

        $lPivot = \DB::table('tp_incidents_pivot as tip')
                            ->join('cat_incidence_tps as tps', 'tps.id_incidence_tp', '=', 'tip.tp_incident_id')
                            ->join('interact_systems as is', 'tip.int_sys_id', '=', 'is.id_int_sys')
                            ->where('tip.is_deleted',0)
                            ->select('tip.id_pivot AS idPiv','tps.incidence_tp_name AS nameTp', 'tps.id_incidence_tp AS idTp', 'tip.ext_tp_incident_id AS tpExt', 'tip.ext_cl_incident_id AS clExt', 'is.id_int_sys AS idSys', 'is.name AS nameSys')
                            ->get();

        return json_encode(['success' => true, 'message' => 'Registro editado con exitó', 'lPivot' => $lPivot]);
    }

    public function de_pivot(Request $request)
    {
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        try {
            \DB::beginTransaction();
                $tp = PivotIncidence::findOrFail($request->idPiv);
                $tp->is_deleted = 1;
                $tp->updated_by = \Auth::user()->id;
                $tp->update();;
                \DB::commit();
        } catch (\Throwable $th) {
            //Log::emergency($th->getMessage());
            \DB::rollback();
            return json_encode(['success' => false, 'message' => 'Error al eliminar el registro']);
        }
        $lPivot = \DB::table('tp_incidents_pivot as tip')
                            ->join('cat_incidence_tps as tps', 'tps.id_incidence_tp', '=', 'tip.tp_incident_id')
                            ->join('interact_systems as is', 'tip.int_sys_id', '=', 'is.id_int_sys')
                            ->where('tip.is_deleted',0)
                            ->select('tip.id_pivot AS idPiv','tps.incidence_tp_name AS nameTp', 'tps.id_incidence_tp AS idTp', 'tip.ext_tp_incident_id AS tpExt', 'tip.ext_cl_incident_id AS clExt', 'is.id_int_sys AS idSys', 'is.name AS nameSys')
                            ->get();

        return json_encode(['success' => true, 'message' => 'Registro eliminado con exitó', 'lPivot' => $lPivot]);
    }
}

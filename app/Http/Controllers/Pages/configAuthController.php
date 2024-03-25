<?php

namespace App\Http\Controllers\Pages;

use App\Models\Adm\ConfigAuth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\usersInSystemUtils;

class configAuthController extends Controller
{
    public function index(){
        // consulta principal, muestra la tabla
        $lconfigAuth = \DB::table('config_authorization as ca')
                        ->where('ca.is_deleted', 0)
                        ->join('cat_incidence_tps as it', 'it.id_incidence_tp', '=', 'ca.tp_incidence_id')
                        ->leftjoin('ext_company as com', 'com.id_company', '=', 'ca.company_id')
                        ->leftjoin('org_chart_jobs as cj', 'cj.id_org_chart_job', '=', 'ca.org_chart_id')
                        ->leftjoin('users as u', 'u.id', '=', 'ca.user_id')
                        ->select('ca.*', 'it.incidence_tp_name as incidence', 'com.company_name_ui as company', 'cj.job_name_ui as job', 'u.full_name_ui as user')
                        ->get();
        //dd($lconfigAuth);

        // consultas de los select del modal
        $areas = \DB::table('org_chart_jobs as ocj')
                    ->where('ocj.is_deleted', 0)
                    ->where('ocj.positions', '>', 0)
                    ->where('ocj.id_org_chart_job', '!=', 1)
                    ->get();
                    //dd($areas);
        
        $users = \DB::table('users')
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->select('id', 'full_name as text')
                    ->get();

        $incidenses = \DB::table('cat_incidence_tps')
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->get();
    
        $companies = \DB::table('ext_company')
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->get();

        $users = usersInSystemUtils::FilterUsersInSystem($users, 'id');
        return view('Adm.configAuth')->with('lconfigAuth', $lconfigAuth)
                                    ->with('lAreas', $areas)
                                    ->with('lUsers', $users)
                                    ->with('lInci', $incidenses)
                                    ->with('lComp', $companies);
    }

    public function createAuth(Request $request) {
        //delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);

        try {
            if($request->comp_id != null) {
            \DB::beginTransaction();
                    $auth = new ConfigAuth();
                    $auth->tp_incidence_id = $request->tp_inci_id; // se usa el id del tipo de incidencia, request desde vue
                    $auth->company_id = $request->comp_id;
                    $auth->org_chart_id = null;
                    $auth->user_id = null;
                    $auth->need_auth = $request->needauth;
                    $auth->is_deleted = 0;
                    $auth->created_by = \Auth::user()->id;
                    $auth->updated_by = \Auth::user()->id;
                    $auth->save();
                \DB::commit();
            }
            if ($request->area_id != null) {
                \DB::beginTransaction();
                    $auth = new ConfigAuth();
                    $auth->tp_incidence_id = $request->tp_inci_id; // se usa el id del tipo de incidencia, request desde vue
                    $auth->company_id = null;
                    $auth->org_chart_id = $request->area_id;
                    $auth->user_id = null;
                    $auth->need_auth = $request->needauth;
                    $auth->is_deleted = 0;
                    $auth->created_by = \Auth::user()->id;
                    $auth->updated_by = \Auth::user()->id;
                    $auth->save();
                \DB::commit();
            }
            if ($request->user_id != null) {
                \DB::beginTransaction();
                    $auth = new ConfigAuth();
                    $auth->tp_incidence_id = $request->tp_inci_id; // se usa el id del tipo de incidencia, request desde vue
                    $auth->company_id = null;
                    $auth->org_chart_id = null;
                    $auth->user_id = $request->user_id;
                    $auth->need_auth = $request->needauth;
                    $auth->is_deleted = 0;
                    $auth->created_by = \Auth::user()->id;
                    $auth->updated_by = \Auth::user()->id;
                    $auth->save();
                \DB::commit();
            }
        } catch (\Throwable $th) {
            \DB::rollback();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema']);
        }

        //misma consulta que la principal para redibujar la tabla
        $lconfigAuth = \DB::table('config_authorization as ca')
                        ->where('ca.is_deleted', 0)
                        ->Join('cat_incidence_tps as it', 'it.id_incidence_tp', '=', 'ca.tp_incidence_id')
                        ->leftjoin('ext_company as com', 'com.id_company', '=', 'ca.company_id')
                        ->leftjoin('org_chart_jobs as cj', 'cj.id_org_chart_job', '=', 'ca.org_chart_id')
                        ->leftjoin('users as u', 'u.id', '=', 'ca.user_id')
                        ->select('ca.*', 'it.incidence_tp_name as incidence', 'com.company_name_ui as company', 'cj.job_name_ui as job', 'u.full_name_ui as user')
                        ->get();

        return json_encode(['success' => true, 'message' => 'Registro creado con éxito', 'lconfigAuth' => $lconfigAuth]);
    }

    public function updateAuth(Request $request) {
        //delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);

        try {
            \DB::beginTransaction();
                $auth = ConfigAuth::findOrFail($request->auth_id); // primary key
                $auth->company_id = $request->comp_id;
                $auth->org_chart_id = $request->area_id;
                $auth->user_id = $request->user_id;
                $auth->need_auth = $request->needauth;
                $auth->is_deleted = 0;
                $auth->updated_by = \Auth::user()->id;
                $auth->update();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rolback();
            \Log::error($th);
            return json_encode(['sucess' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema']);
        }

        $lconfigAuth = \DB::table('config_authorization as ca')
                        ->where('ca.is_deleted', 0)
                        ->Join('cat_incidence_tps as it', 'it.id_incidence_tp', '=', 'ca.tp_incidence_id')
                        ->leftjoin('ext_company as com', 'com.id_company', '=', 'ca.company_id')
                        ->leftjoin('org_chart_jobs as cj', 'cj.id_org_chart_job', '=', 'ca.org_chart_id')
                        ->leftjoin('users as u', 'u.id', '=', 'ca.user_id')
                        ->select('ca.*', 'it.incidence_tp_name as incidence', 'com.company_name_ui as company', 'cj.job_name_ui as job', 'u.full_name_ui as user')
                        ->get();
                        
        return json_encode(['success' => true, 'message' => 'Registro actualizado con éxito', 'lconfigAuth' => $lconfigAuth]); 
    }
    
    public function deleteAuth(Request $request) {
        try {
            \DB::beginTransaction();
                $auth = ConfigAuth::findOrFail($request->auth_id); // primary key
                $auth->is_deleted = 1;
                $auth->updated_by = \Auth::user()->id;
                $auth->update();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema']);
        }

        $lconfigAuth = \DB::table('config_authorization as ca')
                        ->where('ca.is_deleted', 0)
                        ->Join('cat_incidence_tps as it', 'it.id_incidence_tp', '=', 'ca.tp_incidence_id')
                        ->leftjoin('ext_company as com', 'com.id_company', '=', 'ca.company_id')
                        ->leftjoin('org_chart_jobs as cj', 'cj.id_org_chart_job', '=', 'ca.org_chart_id')
                        ->leftjoin('users as u', 'u.id', '=', 'ca.user_id')
                        ->select('ca.*', 'it.incidence_tp_name as incidence', 'com.company_name_ui as company', 'cj.job_name_ui as job', 'u.full_name_ui as user')
                        ->get();
                        
        return json_encode(['success' => true, 'message' => 'Registro eliminado con éxito', 'lconfigAuth' => $lconfigAuth]); 
    }
}

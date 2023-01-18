<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adm\Delegation;
use App\Utils\OrgChartUtils;
use App\Utils\EmployeeVacationUtils;

class delegationController extends Controller
{
    public function getData($arrExcept){
        if(\Auth::user()->rol_id == 4){
            $lDelegations_created = \DB::table('delegations as d')
                                ->leftJoin('users as uA', 'u.id', '=', 'd.user_delegation_id')
                                ->leftJoin('users as uB', 'u.id', '=', 'd.user_delegated_id')
                                ->where('d.is_deleted', 0)
                                ->where('d.is_active', 1)
                                ->select(
                                    'd.*',
                                    'uA.full_name_ui as user_delegation_name',
                                    'uB.full_name_ui as user_delegated_name'
                                )
                                ->get();
            
            $lDelegations_asigned = null;
        }else{
            $arr_org_charts = OrgChartUtils::getAllChildsOrgChartJob(\Auth::user()->org_chart_job_id);
            $lEmployees = EmployeeVacationUtils::getlEmployees($arr_org_charts)->pluck('id');

            $lDelegations_created = \DB::table('delegations as d')
                                ->leftJoin('users as uA', 'uA.id', '=', 'd.user_delegation_id')
                                ->leftJoin('users as uB', 'uB.id', '=', 'd.user_delegated_id')
                                ->where('d.is_deleted', 0)
                                ->where('d.is_active', 1)
                                ->whereIn('user_delegated_id', $lEmployees)
                                ->orWhere('user_delegated_id', \Auth::user()->id)
                                ->select(
                                    'd.*',
                                    'uA.full_name_ui as user_delegation_name',
                                    'uB.full_name_ui as user_delegated_name'
                                )
                                ->get();

            $lDelegations_asigned = \DB::table('delegations as d')
                                ->leftJoin('users as uA', 'uA.id', '=', 'd.user_delegation_id')
                                ->leftJoin('users as uB', 'uB.id', '=', 'd.user_delegated_id')
                                ->where('user_delegation_id', \Auth::user()->id)
                                ->where('d.is_deleted', 0)
                                ->where('d.is_active', 1)
                                ->select(
                                    'd.*',
                                    'uA.full_name_ui as user_delegation_name',
                                    'uB.full_name_ui as user_delegated_name'
                                )
                                ->get();
        }

        $lUsers = OrgChartUtils::getAllManagers($arrExcept);

        return [$lUsers, $lDelegations_created, $lDelegations_asigned];
    }

    public function index(){
        $arrExcept = [\Auth::user()->id];
        $data = $this->getData($arrExcept);

        return view('delegations.delegations')->with('lUsers', $data[0])
                                            ->with('lDelegations_created', $data[1])
                                            ->with('lDelegations_asigned', $data[2]);
    }

    public function saveDelegation(Request $request){
        if($request->user_delegated == NULL || $request->user_delegated == ''){
            if(\Auth::user()->rol_id == 4){
                return json_encode(['success' => false, 'message' => 'Debe seleccionar el usuario ausente.', 'icon' => 'warning']);
            }else{
                $request->user_delegated = \Auth::user()->id;
            }
        }

        try {
            \DB::beginTransaction();
            $oDelegation = new Delegation();
            $oDelegation->start_date = $request->start_date;
            $oDelegation->end_date = $request->end_date;
            $oDelegation->user_delegation_id = $request->user_delegation;
            $oDelegation->user_delegated_id = $request->user_delegated;
            $oDelegation->is_active = 1;
            $oDelegation->is_deleted = 0;
            $oDelegation->created_by = \Auth::user()->id;
            $oDelegation->updated_by = \Auth::user()->id;
            $oDelegation->save();

            $arrExcept = [\Auth::user()->id];
            $data = $this->getData($arrExcept);
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al guardar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lDelegations' => $data[1]]);
    }

    public function updateDelegation(Request $request){
        try {
            \DB::beginTransaction();
            $oDelegation = Delegation::findOrFail($request->delegation_id);
            $oDelegation->start_date = $request->start_date;
            $oDelegation->end_date = $request->end_date;
            $oDelegation->is_active = !$request->closeDelegation;
            $oDelegation->update();

            $arrExcept = [\Auth::user()->id];
            $data = $this->getData($arrExcept);
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al actualizar el registro', 'icon' => 'error']);
        }
        
        return json_encode(['success' => true, 'lDelegations' => $data[1]]);
    }

    public function deleteDelegation(Request $request){
        try {
            \DB::beginTransaction();
            $oDelegation = Delegation::findOrFail($request->delegation_id);
            $oDelegation->is_deleted = 1;
            $oDelegation->update();

            $arrExcept = [\Auth::user()->id];
            $data = $this->getData($arrExcept);
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al eliminar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lDelegations' => $data[1]]);
    }
}

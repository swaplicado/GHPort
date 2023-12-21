<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Adm\GroupAssign;
use Illuminate\Http\Request;
use App\Models\Adm\Group;

class employeeGroupsController extends Controller
{
    public function index(){
        $lGroups = Group::where('is_deleted', 0)->get();

        return view('groups.groups')->with('lGroups', $lGroups);
    }

    public function saveGroup(Request $request){
        $groupName = $request->groupName;
        try {
            \DB::beginTransaction();

            $oGroup = new Group();
            $oGroup->name =  $groupName;
            $oGroup->save();

            $lGroups = Group::where('is_deleted', 0)->get();

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);

            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lGroups' => $lGroups]);
    }

    public function updateGroup(Request $request){
        $idGroup = $request->idGroup;
        $groupName = $request->groupName;
        try {
            \DB::beginTransaction();

            $oGroup = Group::findOrFail($idGroup);
            $oGroup->name = $groupName;
            $oGroup->update();

            $lGroups = Group::where('is_deleted', 0)->get();

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            \Log::error($th);

            return json_encode(['scuccess' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lGroups' => $lGroups]);
    }

    public function deleteGroup(Request $request){
        $idGroup = $request->idGroup;
        try {
            \DB::beginTransaction();

            $oGroup = Group::findOrFail($idGroup);
            $oGroup->is_deleted = true;
            $oGroup->update();

            $lGroups = Group::where('is_deleted', 0)->get();

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            \Log::error($th);

            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lGroups' => $lGroups]);
    }

    public function getUsersAssign(Request $request){
        $idGroup = $request->idGroup;
        try {
            $lEmpAssgined = \DB::table('groups_assigns as ga')
                                ->join('users as u', 'u.id', '=', 'user_id_n')
                                ->where('group_id_n', $idGroup)
                                ->select(
                                    'u.id as id_employee',
                                    'u.full_name as employee'
                                )
                                ->orderBy('employee')
                                ->get();

            $lEmpNoAssigned = \DB::table('users')
                                    ->whereNotIn('id', $lEmpAssgined->pluck('id_employee')->toArray())
                                    ->where('is_active', 1)
                                    ->where('is_delete', 0)
                                    ->where('id', '!=', 1)
                                    ->select(
                                        'id as id_employee',
                                        'full_name as employee'
                                    )
                                    ->orderBy('employee')
                                    ->get();
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lEmpAssigned' => $lEmpAssgined, 'lEmpNoAssigned' => $lEmpNoAssigned]);
    }

    public function setAssign(Request $request){
        $idGroup = $request->idGroup;
        $lEmployeesAssigned = collect($request->lEmployeesAssigned);
        $lEmployeesNoAssigned = collect($request->lEmployeesNoAssigned);

        try {
            \DB::beginTransaction();
            $lIds = $lEmployeesNoAssigned->pluck('id_employee')->toArray();

            $lNoAssigned = GroupAssign::whereIn('user_id_n', $lIds)->where('group_id_n', $idGroup)->get();

            foreach ($lNoAssigned as $noAssigned) {
                $noAssigned->delete();
            }
            
            foreach ($lEmployeesAssigned as $emp) {
                $oAssigned = GroupAssign::firstOrNew(['group_id_n' => $idGroup, 'user_id_n' => $emp['id_employee']]);
                $oAssigned->group_id_n = $idGroup;
                $oAssigned->user_id_n = $emp['id_employee'];
                if($oAssigned->id_group_assign == null){
                    $oAssigned->created_by = \Auth::user()->id;
                }
                $oAssigned->updated_by = \Auth::user()->id;
                $oAssigned->save();
            }

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);

            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\permissionsUtils;
use \App\Utils\EmployeeVacationUtils;
use \App\Constants\SysConst;
use \App\Utils\delegationUtils;
use Carbon\Carbon;
use \App\Models\Permissions\Permission;
use \App\Utils\folioUtils;

class permissionController extends Controller
{
    public function index(){
        $lPermissions = permissionsUtils::getUserPermissions(delegationUtils::getIdUser());

        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
        ];

        $lTypes = \DB::table('cat_permission_tp')
                        ->where('is_deleted', 0)
                        ->where('is_active', 1)
                        ->get();

        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $lTemp_special = EmployeeVacationUtils::getEmployeeTempSpecial(delegationUtils::getOrgChartJobIdUser(), delegationUtils::getIdUser(), delegationUtils::getJobIdUser());

        return view('permissions.permissions')->with('lPermissions', $lPermissions)
                                            ->with('constants', $constants)
                                            ->with('lTypes', $lTypes)
                                            ->with('lHolidays', $lHolidays)
                                            ->with('lTemp', $lTemp_special)
                                            ->with('oPermission', null)
                                            ->with('oUser', \Auth::user());
    }

    public function createPermission(Request $request){
        try {
            $startDate = $request->startDate;
            $comments = $request->comments;
            $type_id = $request->type_id;
            $employee_id = $request->employee_id;
            $hours = $request->hours;
            $minutes = $request->minutes;

            \DB::beginTransaction();

            $permission = new Permission();
            $permission->folio_n = folioUtils::makeFolio(Carbon::now(), $employee_id, SysConst::TYPE_PERMISO_HORAS);
            $permission->start_date = $startDate;
            $permission->end_date = $startDate;
            $permission->total_days = 1;
            $permission->tot_calendar_days = 1;
            $permission->ldays = json_encode([$startDate]);
            $permission->minutes = permissionsUtils::getTime($hours, $minutes);
            $permission->user_id = $employee_id;
            $permission->request_status_id = SysConst::APPLICATION_CREADO;
            $permission->type_permission_id = $type_id;
            $permission->emp_comments_n = $comments;
            $permission->is_deleted = false;
            $permission->created_by = \Auth::user()->id;
            $permission->updated_by = \Auth::user()->id;
            $permission->save();

            $lPermissions = permissionsUtils::getUserPermissions(delegationUtils::getIdUser());

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al crear el permiso', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lPermissions' => $lPermissions]);
    }

    public function updatePermission(Request $request){
        try {
            $permission_id = $request->permission_id;
            $startDate = $request->startDate;
            $comments = $request->comments;
            $type_id = $request->type_id;
            $hours = $request->hours;
            $minutes = $request->minutes;

            \DB::beginTransaction();

            $permission = Permission::findOrFail($permission_id);
            $permission->start_date = $startDate;
            $permission->end_date = $startDate;
            $permission->ldays = json_encode([$startDate]);
            $permission->minutes = permissionsUtils::getTime($hours, $minutes);
            $permission->type_permission_id = $type_id;
            $permission->emp_comments_n = $comments;
            $permission->updated_by = \Auth::user()->id;
            $permission->update();

            $lPermissions = permissionsUtils::getUserPermissions(delegationUtils::getIdUser());

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al crear el permiso', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lPermissions' => $lPermissions]);
    }

    public function deletePermission(Request $request){
        try {
            $permission_id = $request->permission_id;

            \DB::beginTransaction();

            $permission = Permission::findOrFail($permission_id);
            $permission->is_deleted = true;
            $permission->update();

            $lPermissions = permissionsUtils::getUserPermissions(delegationUtils::getIdUser());
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al crear el permiso', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lPermissions' => $lPermissions]);
    }

    public function sendPermission(Request $request){

    }

    public function getPermission(Request $request){
        try {
            $oPermission = permissionsUtils::getPermission($request->permission_id);
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener el registro', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'oPermission' => $oPermission]);
    }
}

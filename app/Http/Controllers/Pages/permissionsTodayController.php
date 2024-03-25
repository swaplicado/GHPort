<?php

namespace App\Http\Controllers\pages;

use App\Constants\SysConst;
use App\Http\Controllers\Controller;
use App\Utils\dateUtils;
use App\Utils\permissionsUtils;
use Illuminate\Http\Request;
use Carbon\Carbon;

class permissionsTodayController extends Controller
{
    public function index(){
        try {
            $today = Carbon::today()->toDateString();
            $lPermissions = \DB::table('hours_leave as h')
                                ->join('permission_cl as cl', 'cl.id_permission_cl', '=', 'h.cl_permission_id')
                                ->join('cat_permission_tp as tp', 'tp.id_permission_tp', '=', 'h.type_permission_id')
                                ->join('users as u', 'u.id', '=', 'h.user_id')
                                ->where('h.start_date', $today)
                                ->where('h.request_status_id', SysConst::APPLICATION_APROBADO)
                                ->where('h.is_deleted', 0)
                                ->select(
                                    'h.id_hours_leave',
                                    'h.start_date',
                                    'h.minutes',
                                    'h.intermediate_out',
                                    'h.intermediate_return',
                                    'h.user_id',
                                    'h.request_status_id',
                                    'h.type_permission_id',
                                    'h.cl_permission_id',
                                    'cl.permission_cl_name',
                                    'tp.permission_tp_name',
                                    'u.full_name',
                                )
                                ->get();

            foreach($lPermissions as $permission){
                if($permission->type_permission_id!== SysConst::PERMISO_INTERMEDIO){
                    $result = permissionsUtils::convertMinutesToHours($permission->minutes);
                    $permission->time = $result[0].':'.$result[1].' hrs';
                }else{
                    $interOut = Carbon::createFromFormat('H:i:s', $permission->intermediate_out)->format('h:i A');
                    $interReturn = Carbon::createFromFormat('H:i:s', $permission->intermediate_return)->format('h:i A');
                    $permission->time = $interOut.' a '.$interReturn;
                }
            }

            $lTypes = \DB::table('cat_permission_tp')
                        ->where('is_deleted', 0)
                        ->where('is_active', 1)
                        ->select(
                            'id_permission_tp as id',
                            'permission_tp_name as text',
                        )
                        ->get()
                        ->toArray();

            array_unshift($lTypes, ['id' => 0, 'text' => 'Todos']);
        
            $lClass = \DB::table('permission_cl')
                            ->where('is_deleted', 0)
                            ->where('is_active', 1)
                            ->select(
                                'id_permission_cl',
                                'permission_cl_name'
                            )
                            ->get()
                            ->toArray();

            $todayString = dateUtils::formatDate($today, 'ddd D-M-Y');

            $layout = \Auth::user() != null ? 'layouts.principal' : 'layouts.principalNoAuth';

        } catch (\Throwable $th) {
            \Log::error($th);
        }

        return view('permissions.permissions_today')->with('lPermissions', $lPermissions)
                                                    ->with('lClass', $lClass)
                                                    ->with('lTypes', $lTypes)
                                                    ->with('today', $todayString)
                                                    ->with('layout', $layout);
    }

    public function getlPermissions(){
        try {
            $today = Carbon::today()->toDateString();
            $lPermissions = \DB::table('hours_leave as h')
                                ->join('permission_cl as cl', 'cl.id_permission_cl', '=', 'h.cl_permission_id')
                                ->join('cat_permission_tp as tp', 'tp.id_permission_tp', '=', 'h.type_permission_id')
                                ->join('users as u', 'u.id', '=', 'h.user_id')
                                ->where('h.start_date', $today)
                                ->where('h.request_status_id', SysConst::APPLICATION_APROBADO)
                                ->where('h.is_deleted', 0)
                                ->select(
                                    'h.id_hours_leave',
                                    'h.start_date',
                                    'h.minutes',
                                    'h.intermediate_out',
                                    'h.intermediate_return',
                                    'h.user_id',
                                    'h.request_status_id',
                                    'h.type_permission_id',
                                    'h.cl_permission_id',
                                    'cl.permission_cl_name',
                                    'tp.permission_tp_name',
                                    'u.full_name',
                                )
                                ->get();

            foreach($lPermissions as $permission){
                if($permission->type_permission_id!== SysConst::PERMISO_INTERMEDIO){
                    $result = permissionsUtils::convertMinutesToHours($permission->minutes);
                    $permission->time = $result[0].':'.$result[1].' hrs';
                }else{
                    $interOut = Carbon::createFromFormat('H:i:s', $permission->intermediate_out)->format('h:i A');
                    $interReturn = Carbon::createFromFormat('H:i:s', $permission->intermediate_return)->format('h:i A');
                    $permission->time = $interOut.' a '.$interReturn;
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lPermissions' => $lPermissions]);
    }
}

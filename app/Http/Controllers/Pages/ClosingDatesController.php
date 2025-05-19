<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Constants\SysConst;
use App\Models\Adm\ClosingDates;
use App\Models\Adm\ClosingDatesUser;
use App\Utils\usersInSystemUtils;

class ClosingDatesController extends Controller
{
    public function index(){
        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
            'TYPE_VACACIONES' => SysConst::TYPE_VACACIONES,
            'TYPE_INASISTENCIA' => SysConst::TYPE_INASISTENCIA,
            'TYPE_INASISTENCIA_ADMINISTRATIVA' => SysConst::TYPE_INASISTENCIA_ADMINISTRATIVA,
            'TYPE_PERMISO_SIN_GOCE' => SysConst::TYPE_PERMISO_SIN_GOCE,
            'TYPE_PERMISO_CON_GOCE' => SysConst::TYPE_PERMISO_CON_GOCE,
            'TYPE_PERMISO_PATERNIDAD' => SysConst::TYPE_PERMISO_PATERNIDAD,
            'TYPE_PRESCRIPCIÓN_MEDICA' => SysConst::TYPE_PRESCRIPCIÓN_MEDICA,
            'TYPE_TEMA_LABORAL' => SysConst::TYPE_TEMA_LABORAL,
            'TYPE_CUMPLEAÑOS' => SysConst::TYPE_CUMPLEAÑOS,
            'TYPE_HOMEOFFICE' => SysConst::TYPE_HOMEOFFICE,
        ];
        $dates = \DB::table('closing_dates as clo')
                    ->join('closing_dates_type as t', 't.id', '=', 'clo.type_id')
                    ->where('is_delete',0)
                    ->orderBy('start_date')
                    ->get();
        $initial = '2024-01-30';

        $lTypes = \DB::table('closing_dates_type')->get();

        return view('closing_dates.index')->with('lDates', $dates)
                                        ->with('initial',$initial)
                                        ->with('constants',$constants)
                                        ->with('lTypes', $lTypes);
    }

    public function createClosing(Request $request){
        if($request->id_closedate != null){
            $closing = ClosingDates::find($request->id_closedate);
            $closing->start_date = $request->startDate;
            $closing->end_date = $request->endDate;
            $closing->is_delete = 0;
            $closing->type_id = $request->type_id;

            $closing->save();

            $constants = [
                'SEMANA' => SysConst::SEMANA,
                'QUINCENA' => SysConst::QUINCENA,
                'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
                'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
                'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO,
                'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
                'TYPE_VACACIONES' => SysConst::TYPE_VACACIONES,
                'TYPE_INASISTENCIA' => SysConst::TYPE_INASISTENCIA,
                'TYPE_INASISTENCIA_ADMINISTRATIVA' => SysConst::TYPE_INASISTENCIA_ADMINISTRATIVA,
                'TYPE_PERMISO_SIN_GOCE' => SysConst::TYPE_PERMISO_SIN_GOCE,
                'TYPE_PERMISO_CON_GOCE' => SysConst::TYPE_PERMISO_CON_GOCE,
                'TYPE_PERMISO_PATERNIDAD' => SysConst::TYPE_PERMISO_PATERNIDAD,
                'TYPE_PRESCRIPCIÓN_MEDICA' => SysConst::TYPE_PRESCRIPCIÓN_MEDICA,
                'TYPE_TEMA_LABORAL' => SysConst::TYPE_TEMA_LABORAL,
                'TYPE_CUMPLEAÑOS' => SysConst::TYPE_CUMPLEAÑOS,
                'TYPE_HOMEOFFICE' => SysConst::TYPE_HOMEOFFICE,
            ];
            $dates = \DB::table('closing_dates as clo')
                        ->join('closing_dates_type as t', 't.id', '=', 'clo.type_id')
                        ->where('is_delete',0)
                        ->orderBy('start_date')
                        ->get();
            $initial = '2024-01-30';   
            
            return json_encode(['success' => true, 'lDates' => $dates]);
        }else{
            $closing = new ClosingDates();
            $closing->start_date = $request->startDate;
            $closing->end_date = $request->endDate;
            $closing->is_delete = 0;
            $closing->type_id = $request->type_id;

            $closing->save();

            $constants = [
                'SEMANA' => SysConst::SEMANA,
                'QUINCENA' => SysConst::QUINCENA,
                'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
                'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
                'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO,
                'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
                'TYPE_VACACIONES' => SysConst::TYPE_VACACIONES,
                'TYPE_INASISTENCIA' => SysConst::TYPE_INASISTENCIA,
                'TYPE_INASISTENCIA_ADMINISTRATIVA' => SysConst::TYPE_INASISTENCIA_ADMINISTRATIVA,
                'TYPE_PERMISO_SIN_GOCE' => SysConst::TYPE_PERMISO_SIN_GOCE,
                'TYPE_PERMISO_CON_GOCE' => SysConst::TYPE_PERMISO_CON_GOCE,
                'TYPE_PERMISO_PATERNIDAD' => SysConst::TYPE_PERMISO_PATERNIDAD,
                'TYPE_PRESCRIPCIÓN_MEDICA' => SysConst::TYPE_PRESCRIPCIÓN_MEDICA,
                'TYPE_TEMA_LABORAL' => SysConst::TYPE_TEMA_LABORAL,
                'TYPE_CUMPLEAÑOS' => SysConst::TYPE_CUMPLEAÑOS,
                'TYPE_HOMEOFFICE' => SysConst::TYPE_HOMEOFFICE,
            ];
            $dates = \DB::table('closing_dates as clo')
                        ->join('closing_dates_type as t', 't.id', '=', 'clo.type_id')
                        ->where('is_delete',0)
                        ->orderBy('start_date')
                        ->get();
            $initial = '2024-01-30';   
            
            return json_encode(['success' => true, 'lDates' => $dates]);
            }
        
        
    }

    public function updateClosing(Request $request){

    }

    public function deleteClosing(Request $request){
        $closing = ClosingDates::find($request->application_id);

        $closing->is_delete = 1;

        $closing->save();

        $lClosingDatesUsers = ClosingDatesUser::where('closing_date_id', $request->application_id)
                                        ->where('is_deleted', 0)
                                        ->get();

        foreach ($lClosingDatesUsers as $closingDatesUser) {
            $closingDatesUser->is_deleted = 1;
            $closingDatesUser->save();
        }

        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
            'TYPE_VACACIONES' => SysConst::TYPE_VACACIONES,
            'TYPE_INASISTENCIA' => SysConst::TYPE_INASISTENCIA,
            'TYPE_INASISTENCIA_ADMINISTRATIVA' => SysConst::TYPE_INASISTENCIA_ADMINISTRATIVA,
            'TYPE_PERMISO_SIN_GOCE' => SysConst::TYPE_PERMISO_SIN_GOCE,
            'TYPE_PERMISO_CON_GOCE' => SysConst::TYPE_PERMISO_CON_GOCE,
            'TYPE_PERMISO_PATERNIDAD' => SysConst::TYPE_PERMISO_PATERNIDAD,
            'TYPE_PRESCRIPCIÓN_MEDICA' => SysConst::TYPE_PRESCRIPCIÓN_MEDICA,
            'TYPE_TEMA_LABORAL' => SysConst::TYPE_TEMA_LABORAL,
            'TYPE_CUMPLEAÑOS' => SysConst::TYPE_CUMPLEAÑOS,
            'TYPE_HOMEOFFICE' => SysConst::TYPE_HOMEOFFICE,
        ];
        $dates = \DB::table('closing_dates as clo')
                    ->join('closing_dates_type as t', 't.id', '=', 'clo.type_id')
                    ->where('is_delete',0)
                    ->orderBy('start_date')
                    ->get();
        $initial = '2024-01-30';   
        
        return json_encode(['success' => true, 'lDates' => $dates]);    
    }

    public function getlUsers(Request $request) {
        $closingDates_id = $request->closingDates_id;

        if (!is_null($closingDates_id)) {
            $lUsersAssigned = \DB::table('closing_dates_users as clo')
                        ->join('users as u', 'u.id', '=', 'clo.user_id')
                        ->where('u.is_delete', 0)
                        ->where('clo.is_deleted', 0)
                        ->where('closing_date_id', $closingDates_id)
                        ->select('u.id', 'u.full_name_ui')
                        ->orderBy('u.full_name_ui')
                        ->get();

            $arraylUsersAssigned = $lUsersAssigned->pluck('id')->toArray();

            $lUsers = \DB::table('users')
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->whereNotIn('id', $arraylUsersAssigned)
                        ->select('id', 'full_name_ui')
                        ->orderBy('full_name_ui')
                        ->get();
        } else {
            $lUsersAssigned = [];
            $lUsers = \DB::table('users')
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->where('id', '!=', 1)
                        ->select('id', 'full_name_ui')
                        ->orderBy('full_name_ui')
                        ->get();
        }

        $lUsersAssigned = usersInSystemUtils::FilterUsersInSystem($lUsersAssigned, 'id');
        $lUsers = usersInSystemUtils::FilterUsersInSystem($lUsers, 'id');

        return json_encode(['success' => true, 'lUsers' => $lUsers, 'lUsersAssigned' => $lUsersAssigned]);
    }

    public function createClosingDatesUser(Request $request) {
        $closingDates_id = $request->closingDates_id;
        $lUsersAssigned = $request->lUsersAssigned;

        try {
            \DB::beginTransaction();

            if(is_null($closingDates_id)){
                $closing = new ClosingDates();
            }else{
                $closing = ClosingDates::find($closingDates_id);

                $lClosingDatesUsers = ClosingDatesUser::where('closing_date_id', $closingDates_id)
                                        ->where('is_deleted', 0)
                                        ->get();

                foreach ($lClosingDatesUsers as $closingDatesUser) {
                    $closingDatesUser->is_deleted = 1;
                    $closingDatesUser->save();
                }
            }
            $closing->start_date = $request->startDate;
            $closing->end_date = $request->endDate;
            $closing->is_delete = 0;
            $closing->type_id = $request->type_id;
            $closing->is_global = 0;
            $closing->save();

            foreach ($lUsersAssigned as $user) {
                $closingUser = new ClosingDatesUser();
                $closingUser->closing_date_id = $closing->id_closing_dates;
                $closingUser->user_id = $user['id'];
                $closingUser->is_closed = 0;
                $closingUser->is_deleted = 0;
                $closingUser->save();
            }

            $dates = \DB::table('closing_dates as clo')
                        ->join('closing_dates_type as t', 't.id', '=', 'clo.type_id')
                        ->where('is_delete',0)
                        ->orderBy('start_date')
                        ->get();


            \DB::commit();
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
            \DB::rollBack();
            return json_encode(['success' => false]);
        }

        return json_encode(['success' => true, 'lDates' => $dates]);
    }
}

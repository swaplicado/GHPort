<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Constants\SysConst;
use App\Models\Adm\ClosingDates;

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

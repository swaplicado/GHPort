<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constants\SysConst;

class directVacationsController extends Controller
{
    public function index(){
        $lEmployees = \DB::table('users as u')
                        ->where('is_active', 1)
                        ->where('is_delete', 0)
                        ->where('id','!=', 1)
                        ->select(
                            'id',
                            'full_name as text',
                        )
                        ->orderBy('text')
                        ->get()
                        ->toArray();

        $lGestionStatus = \DB::table('sys_applications_sts')
                        ->where('is_deleted', 0)
                        ->where('id_applications_st', '!=', SysConst::APPLICATION_CONSUMIDO)
                        ->select(
                            'id_applications_st as id',
                            'applications_st_name as name'
                        )
                        ->get();

        $lHolidays = \DB::table('holidays')
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $config = \App\Utils\Configuration::getConfigurations();

        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'APPLICATION_CREADO' => SysConst::APPLICATION_CREADO,
            'APPLICATION_ENVIADO' => SysConst::APPLICATION_ENVIADO,
            'APPLICATION_APROBADO' => SysConst::APPLICATION_APROBADO,
            'APPLICATION_CONSUMIDO' => SysConst::APPLICATION_CONSUMIDO,
            'APPLICATION_RECHAZADO' => SysConst::APPLICATION_RECHAZADO
        ];

        return view('directVacations.directVacations')->with('lEmployees', $lEmployees)
                                                    ->with('lGestionStatus', $lGestionStatus)
                                                    ->with('config', $config)
                                                    ->with('lHolidays', $lHolidays)
                                                    ->with('constants', $constants);
    }
}
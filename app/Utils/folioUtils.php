<?php namespace App\Utils;

use \App\Constants\SysConst;

class folioUtils {
    public static function makeFolio($date, $employee_id, $type_id = SysConst::TYPE_VACACIONES){
        $configFolio = \App\Utils\Configuration::getConfigurations()->folio;

        $oTypes = SysConst::lTypes;
        $key = array_search($type_id, $oTypes);
        $letter = SysConst::lTypesCodes[$key];

        $employee_num = \DB::table('users')
                            ->where('id', $employee_id)
                            ->value('employee_num');

        if($type_id != SysConst::TYPE_PERMISO_HORAS){
            $totApplications = \DB::table('applications')
                                    ->where('user_id', $employee_id)
                                    ->where('type_incident_id', $type_id)
                                    ->where('is_deleted', 0)
                                    ->count();
        }else if($type_id == SysConst::TYPE_PERMISO_HORAS){
            $totApplications = \DB::table('hours_leave')
                                    ->where('user_id', $employee_id)
                                    ->where('is_deleted', 0)
                                    ->count();
        }


        $totApplications = $totApplications + 1;

        $stTotApp = (string)$totApplications;
        
        for($i = $configFolio->consecutive_digits; $i > strlen($stTotApp); $i--){
            $stTotApp = '0'.$stTotApp;
        }

        $ceros = "";
        if(strlen($employee_num) < $configFolio->employee_num_digits){
            for($i = 0; $i < ($configFolio->employee_num_digits - strlen($employee_num)); $i++ ){
                $ceros = '0'.$ceros;
            }
            $employee_num = $ceros.$employee_num;
        }

        if($configFolio->characters > 0){
            $letter = substr($letter, 0, $configFolio->characters) . $configFolio->separator;
        }else{
            $letter = '';
        }

        $folio = $letter.$employee_num.$configFolio->separator.$stTotApp;

        return $folio;
    }
}
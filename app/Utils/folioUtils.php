<?php namespace App\Utils;

use \App\Constants\SysConst;

class folioUtils {
    public static function makeFolio($date, $employee_id, $type_id = SysConst::TYPE_VACACIONES){
        $oTypes = SysConst::lTypes;
        $key = array_search($type_id, $oTypes);
        $letter = SysConst::lTypesCodes[$key];

        $employee_num = \DB::table('users')
                            ->where('id', $employee_id)
                            ->value('employee_num');

        $totApplications = \DB::table('applications')
                                ->where('user_id', $employee_id)
                                ->where('type_incident_id', $type_id)
                                ->where('is_deleted', 0)
                                ->count();

        $totApplications = $totApplications + 1;

        $stTotApp = (string)$totApplications;
        for($i = 4; $i > strlen($totApplications); $i--){
            $stTotApp = '0'.$stTotApp;
        }

        $ceros = "";
        if(strlen($employee_num) < 5){
            for($i = 0; $i < (5 - strlen($employee_num)); $i++ ){
                $ceros = '0'.$ceros;
            }
            $employee_num = $ceros.$employee_num;
        }

        $folio = $letter.'-'.$employee_num.'-'.$stTotApp;

        return $folio;
    }
}
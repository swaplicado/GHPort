<?php namespace App\Utils;

use Carbon\Carbon;

class dateUtils {
    public static function formatDate($sDate, $format){
        $days = ['Dom.', 'Lun.', 'Mar.', 'Mié.', 'Jue.', 'Vie.', 'Sáb.'];
        $months = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        try {
            $oDate = Carbon::parse($sDate);
            if($format == 'ddd D-M-Y'){
                $date = $days[$oDate->dayOfWeek].' '.$oDate->format('d').'-'.$months[$oDate->month].'-'.$oDate->format('Y');
            }else{
                $date = $oDate->format($format);
            }
        } catch (\Throwable $th) {
            return "Fecha invalida.";
        }
        return $date;
    }
}
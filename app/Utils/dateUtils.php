<?php namespace App\Utils;

use Carbon\Carbon;

class dateUtils {
    public static function formatDate($sDate, $format){
        $days = ['Dom.', 'Lun.', 'Mar.', 'Mié.', 'Jue.', 'Vie.', 'Sáb.'];
        $daysComplete = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        $months = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $monthsAux = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

        try {
            $oDate = Carbon::parse($sDate);
            switch ($format) {
                case 'ddd D-M-Y':
                    $date = $days[$oDate->dayOfWeek].' '.$oDate->format('d').'-'.$months[$oDate->month].'-'.$oDate->format('Y');
                    break;
                case 'D/m/Y dddd':
                    $date = $oDate->format('d').'/'.$monthsAux[$oDate->month].'/'.$oDate->format('Y').' ('.$daysComplete[$oDate->dayOfWeek].')';
                    break;
                
                default:
                    $date = $oDate->format($format);
                    break;
            }
        } catch (\Throwable $th) {
            return "Fecha invalida.";
        }


        // try {
        //     $oDate = Carbon::parse($sDate);
        //     if($format == 'ddd D-M-Y'){
        //         $date = $days[$oDate->dayOfWeek].' '.$oDate->format('d').'-'.$months[$oDate->month].'-'.$oDate->format('Y');
        //     }else{
        //         $date = $oDate->format($format);
        //     }
        // } catch (\Throwable $th) {
        //     return "Fecha invalida.";
        // }
        return $date;
    }
}
<?php namespace App\Http\Controllers\Utils;

use Carbon\Carbon;
use App\Utils\dateUtils;

class applicationsUtils {
    public static function checkBirthdayRules($user_id, $oApplication) {
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $oRules = $config->birthdayRules;

            //se busca si ya existe una solicitud de cumpleaños para el año solicitado
            $application = \DB::table('applications as ap')
                                ->join('applications_breakdowns as bk', 'ap.id_application', '=', 'bk.application_id')
                                ->select('ap.*', 'bk.application_year')
                                ->where('ap.user_id', $user_id)
                                ->where('ap.type_incident_id', 9)
                                ->whereIn('ap.request_status_id', [3,2,1])
                                ->where('ap.is_deleted', 0)
                                ->where('bk.application_year', $oApplication->birthDayYear)
                                ->latest()->first();

            if (!is_null($application)) {
                return json_encode(['success' => false, 'message' => 'Ya existe una incidencia de cumpleaños correspondiente al año '.$oApplication->birthDayYear.'.', 'icon' => 'error']);
            }

            //si las reglas de cumpleaños estan activas
            if ($oRules->isActiveRules) {
                $oToday = Carbon::now();
                //si ya paso la fecha de aplicacion de las reglas
                if ($oToday->greaterThan($oRules->dateInitRules)) {
                    $birthday = \DB::table('users')
                                ->where('id', $user_id)
                                ->value('birthday_n');
                    
                    $oBirthday = Carbon::parse($birthday);
                    $actualBirthDay = Carbon::parse($oToday->year.'-'.$oBirthday->month.'-'.$oBirthday->month);
                    $oDateApplication = Carbon::parse($oApplication->startDate);
                    //se saca el cumpleaños correspondiente a la solicitud
                    $oApplicationBirthDay = Carbon::parse($oApplication->birthDayYear.'-'.$oBirthday->month.'-'.$oBirthday->day);
                    
                    if ($oDateApplication->isSameMonth($actualBirthDay) && $oApplication->year == $actualBirthDay->year) {
                        return json_encode(['success' => true, 'message' => '', 'icon' => 'success']);
                    }

                    if ($oApplicationBirthDay->isBefore($oRules->dateInitRules)) {
                        if ($oDateApplication->isBefore($oRules->limitDayToTakingPastBirthDay)) {
                            return json_encode(['success' => true, 'message' => '', 'icon' => 'success']);
                        } else {
                            return json_encode(['success' => false, 'message' => 'La fecha límite para gozar el día de cumpleaños es '.dateUtils::formatDate($oRules->limitDayToTakingPastBirthDay, 'ddd D-M-Y').'.', 'icon' => 'error']);
                        }
                    } else {
                        return json_encode(['success' => false, 'message' => 'El día de cumpleaños debe gozarse dentro del mes del cumpleaños.', 'icon' => 'error']);
                    }
                } else {
                    return json_encode(['success' => true, 'message' => '', 'icon' => 'success']);
                }
            } else {
                return json_encode(['success' => true, 'message' => '', 'icon' => 'success']);
            }
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'success']);
        }
    }
}
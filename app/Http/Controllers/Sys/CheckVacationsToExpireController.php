<?php

namespace App\Http\Controllers\Sys;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\EmployeeVacationUtils;
use Carbon\Carbon;

class CheckVacationsToExpireController extends Controller
{
    public static function daysToMonthsAndDays($days) {
        $months = floor($days / 30);
        $remainingDays = $days % 30;
        return [
            'months' => $months,
            'days' => $remainingDays
        ];
    }

    public static function checkVacationToExpire($user_id){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            // $expiredDate = Carbon::now()->subMonths($config->expiration_vacations->months)
            //                         ->subYears($config->expiration_vacations->years);

            $notifyDate = Carbon::now()->subDays($config->notifyVacationToExpireRange->days)
                                    ->subMonths($config->notifyVacationToExpireRange->months)
                                    ->subYears($config->notifyVacationToExpireRange->years);

            $oUser = EmployeeVacationUtils::getEmployeeVacationsData($user_id);

            $days = 0;
            $arrInfoVact = [];
            $arrInfoVact[] = [
                'Después de cumplir tu aniversario laboral, tienes derecho al disfrute de vacaciones correspondientes al período.',
                'De acuerdo con el Artículo 81 LFT. "Las vacaciones deberán concederse a los trabajadores dentro de los seis meses siguientes al cumplimiento del año de servicios".',
                'El trabajador tiene derecho a 1 año adicional (total de 18 meses desde el aniversario), para exigirlas, o las perderá legalmente.'
            ];
            foreach($oUser->vacation as $vac){
                $date_expiration = Carbon::parse($vac->date_end)->addDays(1)
                                                            ->addMonths($config->notifyVacationToExpireRange->months)
                                                            ->addYears($config->notifyVacationToExpireRange->years);

                $notifyDate = clone($date_expiration);
                $notifyDate = $notifyDate->subDays($config->notifyVacationToExpireRange->days)
                                                            ->subMonths($config->notifyVacationToExpireRange->months)
                                                            ->subYears($config->notifyVacationToExpireRange->years);
                                                            
                if(Carbon::now()->between($notifyDate, $date_expiration) && $vac->remaining > 0){
                    $vacToExpired = $vac->remaining;
                    $days = Carbon::now()->diffInDays($date_expiration);
                    $oTime = self::daysToMonthsAndDays($days);
                    if ($days <= $config->daysToShowVacationExpiredMessage) {
                        $arrInfoVact[] = [
                            'vacToExpired' => $vacToExpired,
                            'daysToExpired' => $days,
                            'date_expiration' => $date_expiration->toDateString(),
                            'anniversary' => $vac->id_anniversary,
                            'year' => $vac->year,
                            'date_start' => $vac->date_start,
                            'date_end' => $vac->date_end,
                            'message' => 'Tienes ' . $vacToExpired . ' días de vacaciones que vencen el ' . $date_expiration->locale('es')->isoFormat('ddd DD/MMM/YYYY') . ' correspondiente a tu aniversario número ' . $vac->id_anniversary,
                            'messageToBoos' => 'Tiene ' . $vacToExpired . ' días de vacaciones que vencen el ' . $date_expiration->locale('es')->isoFormat('ddd DD/MMM/YYYY') . ' correspondiente al aniversario número ' . $vac->id_anniversary
                        ];
                    }
                }

                // if(Carbon::parse($vac->date_end)->between($notifyDate, $expiredDate)){
                //     $vacToExpired = $vacToExpired + $vac->remaining;
                //     $days = Carbon::parse($vac->date_end)->diffInDays($expiredDate);
                // }
            }
            
        } catch (\Throwable $th) {
            return null;
        }

        return json_encode($arrInfoVact);
    }
}

<?php

namespace App\Http\Controllers\Sys;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\EmployeeVacationUtils;
use Carbon\Carbon;

class CheckVacationsToExpireController extends Controller
{
    public static function checkVacationToExpire($user_id){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            // $expiredDate = Carbon::now()->subMonths($config->expiration_vacations->months)
            //                         ->subYears($config->expiration_vacations->years);

            $notifyDate = Carbon::now()->subDays($config->notifyVacationToExpireRange->days)
                                    ->subMonths($config->notifyVacationToExpireRange->months)
                                    ->subYears($config->notifyVacationToExpireRange->years);

            $oUser = EmployeeVacationUtils::getEmployeeVacationsData($user_id);

            $vacToExpired = 0;
            $days = 0;
            foreach($oUser->vacation as $vac){
                $date_expiration = Carbon::parse($vac->date_end)->addDays(1)
                                                            ->addMonths($config->expiration_vacations->months)
                                                            ->addYears($config->expiration_vacations->years);

                $notifyDate = clone($date_expiration);
                $notifyDate = $notifyDate->subDays($config->notifyVacationToExpireRange->days)
                                                            ->subMonths($config->notifyVacationToExpireRange->months)
                                                            ->subYears($config->notifyVacationToExpireRange->years);

                if(Carbon::now()->between($notifyDate, $date_expiration)){
                    $vacToExpired = $vacToExpired + $vac->remaining;
                    $days = Carbon::now()->diffInDays($date_expiration);
                }

                // if(Carbon::parse($vac->date_end)->between($notifyDate, $expiredDate)){
                //     $vacToExpired = $vacToExpired + $vac->remaining;
                //     $days = Carbon::parse($vac->date_end)->diffInDays($expiredDate);
                // }
            }
            
        } catch (\Throwable $th) {
            return null;
        }

        return json_encode(['vacToExpired' => $vacToExpired, 'days' => $days]);
    }
}

<?php namespace App\Utils;

use App\Models\Vacations\RecoveredVacation;
use Carbon\Carbon;

class recoveredVacationsUtils {
    public static function resetUsedDays($oApplication){
        $lAppBreak = \DB::table('applications_breakdowns')
                        ->where('application_id', $oApplication->id_application)
                        ->get();

        $now_date = Carbon::now()->toDateString();
        foreach($lAppBreak as $ab){
            $vacation = \DB::table('vacation_users')
                            ->where('user_id', $oApplication->user_id)
                            ->where('year', $ab->application_year)
                            ->first();

            $recover = RecoveredVacation::where('user_id', $oApplication->user_id)
                                        ->where('vacation_user_id', $vacation->id_vacation_user)
                                        ->where('is_deleted', 0)
                                        ->where(function($query) use($now_date){
                                            $query->where('end_date', '>=', $now_date)
                                                ->orWhere('is_used', 1);
                                        })
                                        ->first();

            if(!is_null($recover)){
                $recover->used_days_n = $recover->used_days_n - $ab->days_effective;
                $end_date = Carbon::parse($recover->end_date);
                if($recover->used_days_n <= 0){
                    $recover->is_used = 0;
                }
                $recover->recovered_days = $recover->recovered_days - $ab->days_effective;
                $recover->update();
            }
        }
    }

    public static function insertUsedDays($oApplication){
        $lAppBreak = \DB::table('applications_breakdowns')
                        ->where('application_id', $oApplication->id_application)
                        ->get();

        $now_date = Carbon::now()->toDateString();
        foreach($lAppBreak as $ab){
            $vacation = \DB::table('vacation_users')
                            ->where('user_id', $oApplication->user_id)
                            ->where('year', $ab->application_year)
                            ->first();

            $recover = RecoveredVacation::where('user_id', $oApplication->user_id)
                                        ->where('vacation_user_id', $vacation->id_vacation_user)
                                        ->where('is_deleted', 0)
                                        ->where(function($query) use($now_date){
                                            $query->where('end_date', '>=', $now_date)
                                                ->orWhere('is_used', 1);
                                        })
                                        ->first();

            if(!is_null($recover)){
                $recover->used_days_n = $recover->used_days_n + $ab->days_effective;
                $recover->is_used = 1;
                $recover->update();
            }
        }
    }

    public static function getExpiredVacations($user){
        $config = \App\Utils\Configuration::getConfigurations();

        $date_expiration = Carbon::now()->subMonths($config->expiration_vacations->months)
                                        ->subYears($config->expiration_vacations->years)
                                        ->toDateString();

        $user->vacationsExpired = \DB::table('vacation_users')
                                        ->where('user_id', $user->id)
                                        ->where('is_deleted', 0)
                                        ->where('date_end', '<', $date_expiration)
                                        ->orderBY('year')
                                        ->get();

        if(count($user->vacationsExpired) < 1){
            $user->vacationsExpired->vacRemaining = 0;
            $user->vacationsExpired->vacRecovered = 0;
        }

        foreach($user->vacationsExpired as $key => $vac){
            $vacConsumed = EmployeeVacationUtils::getVacationConsumed($user->id, $vac->year)->sum('day_consumption');
            $vac_request = EmployeeVacationUtils::getVacationRequested($user->id, $vac->year)->sum('days_effective');
            $now_date = Carbon::now()->toDateString();

            $oVacRecovered = \DB::table('recovered_vacations')
                                    ->where('user_id', $user->id)
                                    ->where('vacation_user_id', $vac->id_vacation_user)
                                    ->where(function($query) use($now_date){
                                        $query->where('end_date', '>=', $now_date)
                                            ->orWhere('is_used', 1);
                                    })
                                    ->where('is_deleted', 0)
                                    ->first();

            $vac->vacRecovered = !is_null($oVacRecovered) ? $oVacRecovered->recovered_days : 0;
            $used_days = !is_null($oVacRecovered) ? $oVacRecovered->used_days_n : 0;
            
            $vac->consumedVac =  $vacConsumed + $vac_request;
            // $vac->vacRemaining = $vac->vacation_days - ( $vacConsumed + $vac_request + $vac->vacRecovered );
            $vac->vacRemaining = $vac->vacation_days - ( $vacConsumed + $vac->vacRecovered );

            if($vac->vacRemaining == 0 && $used_days == $vac->vacRecovered){
                $user->vacationsExpired->forget($key);
            }
        }
        $user->vacationsExpired = $user->vacationsExpired->values();
        $coll = collect($user->vacationsExpired);
        $user->TotVacRemaining = $coll->sum('vacRemaining');
        $user->TotVacRecovered = $coll->sum('vacRecovered');

        return $user;
    }
}
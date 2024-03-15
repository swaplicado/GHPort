<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\orgChartUtils;
use App\Utils\delegationUtils;
use App\Utils\EmployeeVacationUtils;
use App\Models\Vacations\RecoveredVacation;
use App\Utils\recoveredVacationsUtils;
use Carbon\Carbon;
use App\Utils\usersInSystemUtils;

class recoveredVacationsController extends Controller
{
    public function index(){
        $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($org_chart_job_id);
        $lUsers = \DB::table('users as u')
                    ->where('u.is_delete', 0)
                    ->where('u.is_active', 1)
                    ->whereIn('u.org_chart_job_id', $arrOrgJobs)
                    ->orderBy('full_name_ui')
                    ->get();

        $lUsers = usersInSystemUtils::FilterUsersInSystem($lUsers, 'id');
        foreach($lUsers as $user){
            $user = recoveredVacationsUtils::getExpiredVacations($user);
        }

        return view('emp_vacations.recovered_vacations')->with('lUsers', $lUsers);
    }

    public function save(Request $request){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $daysToRecover = $request->daysToRecover;
            $user = \DB::table('users as u')
                        ->where('id', $request->user_id)
                        ->first();
            
            $user = recoveredVacationsUtils::getExpiredVacations($user);
            $oRecoverDays = [];

            foreach($user->vacationsExpired as $vac){
                if($vac->vacRemaining > 0){
                    $days = $daysToRecover <= $vac->vacRemaining ? $daysToRecover : $vac->vacRemaining;
                    $daysToRecover = $daysToRecover - $days;
                    $oRecoverDays[] = ['vacation_id' => $vac->id_vacation_user, 'days' => $days];
                    if($daysToRecover <= 0){
                        break;
                    }
                }
            }

            $date_expired_recover = Carbon::now()->addYears($config->expiration_recover_vacations->years)
                                                ->addMonths($config->expiration_recover_vacations->months)
                                                ->addDays($config->expiration_recover_vacations->days)
                                                ->toDateString();

            $now_date = Carbon::now()->toDateString();

            \DB::beginTransaction();
            foreach($oRecoverDays as $rec){
                $oRecovered = RecoveredVacation::where('user_id', $user->id)
                                                ->where('vacation_user_id', $rec['vacation_id'])
                                                ->where(function($query) use($now_date){
                                                    $query->where('end_date', '>=', $now_date)
                                                        ->orWhere('is_used', 1);
                                                })
                                                ->where('is_deleted', 0)
                                                ->first();

                if(!is_null($oRecovered)){
                    $oRecovered->recovered_days = $oRecovered->recovered_days + $rec['days'];
                    $oRecovered->update();
                }else{
                    $oRecovered = new RecoveredVacation();
                    $oRecovered->user_id = $user->id;
                    $oRecovered->vacation_user_id = $rec['vacation_id'];
                    $oRecovered->recovered_days = $rec['days'];
                    $oRecovered->end_date = $date_expired_recover;
                    $oRecovered->created_by = \Auth::user()->id;
                    $oRecovered->updated_by = \Auth::user()->id;
                    $oRecovered->save();
                }
            }

            $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
            $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($org_chart_job_id);
            $lUsers = \DB::table('users as u')
                        ->where('u.is_delete', 0)
                        ->where('u.is_active', 1)
                        ->whereIn('u.org_chart_job_id', $arrOrgJobs)
                        ->orderBy('full_name_ui')
                        ->get();

            foreach($lUsers as $user){
                $user = recoveredVacationsUtils::getExpiredVacations($user);
            }
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al recuperar los días de vacaciones', 'icon' => 'error']);
        }

        $lUsers = usersInSystemUtils::FilterUsersInSystem($lUsers, 'id');
        return json_encode(['success' => true, 'lUsers' => $lUsers]);
    }

    public function indexManagment(){
        $lUsers = \DB::table('users as u')
                    ->where('u.is_delete', 0)
                    ->where('u.is_active', 1)
                    ->where('id', '!=', 1)
                    ->orderBy('full_name_ui')
                    ->get();

        foreach($lUsers as $user){
            $user = recoveredVacationsUtils::getExpiredVacations($user);
        }

        $lUsers = usersInSystemUtils::FilterUsersInSystem($lUsers, 'id');
        return view('emp_vacations.recovered_vacations_managment')->with('lUsers', $lUsers);
    }

    public function saveManagment(Request $request){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $daysToRecover = $request->daysToRecover;
            $user = \DB::table('users as u')
                        ->where('id', $request->user_id)
                        ->first();
            
            $user = recoveredVacationsUtils::getExpiredVacations($user);
            $oRecoverDays = [];

            foreach($user->vacationsExpired as $vac){
                if($vac->vacRemaining > 0){
                    $days = $daysToRecover <= $vac->vacRemaining ? $daysToRecover : $vac->vacRemaining;
                    $daysToRecover = $daysToRecover - $days;
                    $oRecoverDays[] = ['vacation_id' => $vac->id_vacation_user, 'days' => $days];
                    if($daysToRecover <= 0){
                        break;
                    }
                }
            }

            $date_expired_recover = Carbon::now()->addYears($config->expiration_recover_vacations->years)
                                                ->addMonths($config->expiration_recover_vacations->months)
                                                ->addDays($config->expiration_recover_vacations->days)
                                                ->toDateString();

            $now_date = Carbon::now()->toDateString();

            \DB::beginTransaction();
            foreach($oRecoverDays as $rec){
                $oRecovered = RecoveredVacation::where('user_id', $user->id)
                                                ->where('vacation_user_id', $rec['vacation_id'])
                                                ->where(function($query) use($now_date){
                                                    $query->where('end_date', '>=', $now_date)
                                                        ->orWhere('is_used', 1);
                                                })
                                                ->where('is_deleted', 0)
                                                ->first();

                if(!is_null($oRecovered)){
                    $oRecovered->recovered_days = $oRecovered->recovered_days + $rec['days'];
                    $oRecovered->update();
                }else{
                    $oRecovered = new RecoveredVacation();
                    $oRecovered->user_id = $user->id;
                    $oRecovered->vacation_user_id = $rec['vacation_id'];
                    $oRecovered->recovered_days = $rec['days'];
                    $oRecovered->end_date = $date_expired_recover;
                    $oRecovered->created_by = \Auth::user()->id;
                    $oRecovered->updated_by = \Auth::user()->id;
                    $oRecovered->save();
                }
            }

            $lUsers = \DB::table('users as u')
                        ->where('u.is_delete', 0)
                        ->where('u.is_active', 1)
                        ->where('id', '!=', 1)
                        ->orderBy('full_name_ui')
                        ->get();

            foreach($lUsers as $user){
                $user = recoveredVacationsUtils::getExpiredVacations($user);
            }
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al recuperar los días de vacaciones', 'icon' => 'error']);
        }

        $lUsers = usersInSystemUtils::FilterUsersInSystem($lUsers, 'id');
        return json_encode(['success' => true, 'lUsers' => $lUsers]);
    }
}

<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Adm\VacationAllocation;
use App\Models\Adm\VacationUser;
use App\Models\Vacations\Applications;
use App\Models\Vacations\Programed;
use App\Models\Vacations\SyncLog;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Arr;
use App\Utils\EmployeeVacationUtils;
use App\Constants\SysConst;

class VacationsController extends Controller
{
    public function saveVacFromJSON($lSiieVacs)
    {
        try {
            \DB::beginTransaction();
            foreach($lSiieVacs as $rVac){
                $user = User::where('external_id_n', $rVac->employee_id)->first();
                if(!is_null($user)){
                    $lIds = \DB::table('vacation_allocations')->where('user_id', $user->id)->get()->pluck('id_vacation_allocation')->toArray();
                    \DB::table('vacation_allocations')->where('user_id', $user->id)->delete();
                    // $lIdsProgrammed = \DB::table('programed_aux')->where('user_id', $user->id)->get()->pluck('id_vacation_allocation')->toArray();
                    \DB::table('programed_aux')->where('employee_id', $user->id)->delete();
                    foreach($rVac->rows as $index => $vac){
                        if($vac->vacation_consumed > 0){
                            $oVacAll = new VacationAllocation();
                            if($index < count($lIds)){
                                $oVacAll->id_vacation_allocation = $lIds[$index];
                            }
                            $oVacAll->user_id = $user->id;
                            $oVacAll->day_consumption = $vac->vacation_consumed;
                            $oVacAll->is_deleted = 0;
                            $oVacAll->created_by = 1;
                            $oVacAll->updated_by = 1;
                            $oVacAll->anniversary_count = $vac->anniversary;
                            $oVacAll->id_anniversary = $vac->year;
                            $oVacAll->save();
                        }
    
                        if($vac->vacation_programm > 0){
                            $this->insertProgramed($vac, $user->id);
                        }
                    }
                    EmployeeVacationUtils::syncVacConsumed($user->id);
                    
                    $oSyncLog = SyncLog::where('user_id', $user->id)->first();
                    if(!is_null($oSyncLog)){
                        $oSyncLog->last_sync = Carbon::now()->toDateTimeString();
                        $oSyncLog->update();
                    }else{
                        $oSyncLog = new SyncLog();
                        $oSyncLog->user_id = $user->id;
                        $oSyncLog->last_sync = Carbon::now()->toDateTimeString();
                        $oSyncLog->save();
                    }
                }
            }
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return false;
        }

        return true;
    }

    public function insertProgramed($vac, $user_id){
        $programed = new Programed();
        $programed->employee_id = $user_id;
        $programed->days_to_consumed = $vac->vacation_programm;
        $programed->anniversary = $vac->anniversary;
        $programed->year = $vac->year;
        $programed->is_deleted = 0;
        $programed->save();
    }
}

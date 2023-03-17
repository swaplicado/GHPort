<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Adm\VacationAllocation;
use App\Models\Adm\VacationUser;
use App\Models\Vacations\Applications;
use App\Models\Vacations\Programed;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Arr;
use App\Utils\EmployeeVacationUtils;

class VacationsController extends Controller
{
    public function saveVacFromJSON($lSiieVacs)
    {
        $arr_ids = [];
        try {
            \DB::table('vacation_allocations')->delete();
            \DB::table('programed_aux')->delete();
            \DB::statement("ALTER TABLE vacation_allocations AUTO_INCREMENT =  1");
            \DB::statement("ALTER TABLE programed_aux AUTO_INCREMENT =  1");
        } catch (\Throwable $th) {
        }
        foreach($lSiieVacs as $rVac){
            try {
                $user = User::where('external_id_n', $rVac->employee_id)->first();
                if(!is_null($user)){
                    foreach($rVac->rows as $vac){
                        if($vac->vacation_consumed > 0){
                            $oVacAll = new VacationAllocation();
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
    
                    /**
                     * Se quitó
                     */
                    // foreach($rVac->incidents as $inc){
                    //     if($inc->day_consumed > 0){
                    //         $oVacAll = new VacationAllocation();
                    //         $oVacAll->user_id = $user->id;
                    //         $oVacAll->day_consumption = $inc->day_consumed;
                    //         $oVacAll->application_breakdown_id = $inc->id_breakdown;
                    //         $oVacAll->is_deleted = 0;
                    //         $oVacAll->created_by = 1;
                    //         $oVacAll->updated_by = 1;
                    //         $oVacAll->anniversary_count = $inc->anniversary;
                    //         $oVacAll->id_anniversary = $inc->year;
                    //         $oVacAll->save();
                    //     }
                    // }
                    EmployeeVacationUtils::syncVacConsumed($user->id);
                }
            } catch (\Throwable $th) {
                dd($th);
            }
        }
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

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
    
                    foreach($rVac->incidents as $inc){
                        if($inc->day_consumed > 0){
                            $oVacAll = new VacationAllocation();
                            $oVacAll->user_id = $user->id;
                            $oVacAll->day_consumption = $inc->day_consumed;
                            $oVacAll->application_breakdown_id = $inc->id_breakdown;
                            $oVacAll->is_deleted = 0;
                            $oVacAll->created_by = 1;
                            $oVacAll->updated_by = 1;
                            $oVacAll->anniversary_count = $inc->anniversary;
                            $oVacAll->id_anniversary = $vac->year;
                            $oVacAll->save();
                        }
                    }
                    EmployeeVacationUtils::syncVacConsumed($user->id);
                }
            } catch (\Throwable $th) {
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

    public function dumySetVacationsUser(){
        $arrYearVac = [
            [	"year"	 => 	1	,	"vac"	 => 	7	],
            [	"year"	 => 	2	,	"vac"	 => 	9	],
            [	"year"	 => 	3	,	"vac"	 => 	11	],
            [	"year"	 => 	4	,	"vac"	 => 	13	],
            [	"year"	 => 	5	,	"vac"	 => 	15	],
            [	"year"	 => 	6	,	"vac"	 => 	15	],
            [	"year"	 => 	7	,	"vac"	 => 	15	],
            [	"year"	 => 	8	,	"vac"	 => 	15	],
            [	"year"	 => 	9	,	"vac"	 => 	15	],
            [	"year"	 => 	10	,	"vac"	 => 	17	],
            [	"year"	 => 	11	,	"vac"	 => 	17	],
            [	"year"	 => 	12	,	"vac"	 => 	17	],
            [	"year"	 => 	13	,	"vac"	 => 	17	],
            [	"year"	 => 	14	,	"vac"	 => 	17	],
            [	"year"	 => 	15	,	"vac"	 => 	18	],
            [	"year"	 => 	16	,	"vac"	 => 	18	],
            [	"year"	 => 	17	,	"vac"	 => 	18	],
            [	"year"	 => 	18	,	"vac"	 => 	18	],
            [	"year"	 => 	19	,	"vac"	 => 	18	],
            [	"year"	 => 	20	,	"vac"	 => 	20	],
            [	"year"	 => 	21	,	"vac"	 => 	20	],
            [	"year"	 => 	22	,	"vac"	 => 	20	],
            [	"year"	 => 	23	,	"vac"	 => 	20	],
            [	"year"	 => 	24	,	"vac"	 => 	20	],
            [	"year"	 => 	25	,	"vac"	 => 	22	],
            [	"year"	 => 	26	,	"vac"	 => 	22	],
            [	"year"	 => 	27	,	"vac"	 => 	22	],
            [	"year"	 => 	28	,	"vac"	 => 	22	],
            [	"year"	 => 	29	,	"vac"	 => 	22	],
            [	"year"	 => 	30	,	"vac"	 => 	24	],
            [	"year"	 => 	31	,	"vac"	 => 	24	],
            [	"year"	 => 	32	,	"vac"	 => 	24	],
            [	"year"	 => 	33	,	"vac"	 => 	24	],
            [	"year"	 => 	34	,	"vac"	 => 	24	],
            [	"year"	 => 	35	,	"vac"	 => 	26	],
            [	"year"	 => 	36	,	"vac"	 => 	26	],
            [	"year"	 => 	37	,	"vac"	 => 	26	],
            [	"year"	 => 	38	,	"vac"	 => 	26	],
            [	"year"	 => 	39	,	"vac"	 => 	26	],
            [	"year"	 => 	40	,	"vac"	 => 	28	],
            [	"year"	 => 	41	,	"vac"	 => 	28	],
            [	"year"	 => 	42	,	"vac"	 => 	28	],
            [	"year"	 => 	43	,	"vac"	 => 	28	],
            [	"year"	 => 	44	,	"vac"	 => 	28	],
            [	"year"	 => 	45	,	"vac"	 => 	30	],
            [	"year"	 => 	46	,	"vac"	 => 	30	],
            [	"year"	 => 	47	,	"vac"	 => 	30	],
            [	"year"	 => 	48	,	"vac"	 => 	30	],
            [	"year"	 => 	49	,	"vac"	 => 	30	],
            [	"year"	 => 	50	,	"vac"	 => 	32	],            
        ];

        $arrYearVac = collect($arrYearVac);
        \DB::table('vacation_users')->delete();
        \DB::statement("ALTER TABLE vacation_users AUTO_INCREMENT =  1");

        try {
            $lUsers = \DB::table('users as u')
                        ->leftJoin('user_admission_logs as al', 'al.user_id', '=', 'u.id')
                        ->where([['is_active', 1],['is_delete', 0],['id', '!=', 1]])
                        ->select('u.*', 'al.id_user_admission_log')
                        ->get();
    
    
            foreach($lUsers as $oUser){
                $date = Carbon::parse($oUser->last_admission_date);
                for($i=1; $i<=50; $i++){
                    $vacDays = Arr::collapse($arrYearVac->where('year', $i));

                    $oVacAll = new VacationUser();
                    $oVacAll->user_id = $oUser->id;
                    $oVacAll->user_admission_log_id = $oUser->id_user_admission_log;
                    $oVacAll->id_anniversary = $i;
                    $oVacAll->year = $date->year;
                    $oVacAll->date_start = $date->format('Y-m-d');
                    $oVacAll->date_end = $date->addYear(1)->subDays(1)->format('Y-m-d');
                    $oVacAll->vacation_days = $vacDays['vac'];
                    $oVacAll->is_closed = 0;
                    $oVacAll->is_closed_manually = 0;
                    $oVacAll->is_expired = $date->lt(Carbon::today()) ? $date->diffInYears(Carbon::today()) > 2 : 0;
                    $oVacAll->is_expired_manually = 0;
                    $oVacAll->is_deleted = 0;
                    $oVacAll->created_by = 1;
                    $oVacAll->updated_by = 1;
                    $oVacAll->save();

                    $date->addDays(1);
                }
            }
        } catch (\Throwable $th) {
        }
    }
}

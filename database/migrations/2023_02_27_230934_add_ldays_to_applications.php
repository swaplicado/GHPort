<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Vacations\Application;
use Carbon\Carbon;

class AddLdaysToApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('ldays')->after('return_date');
        });

        // $lApplications = \DB::table('applications')->get();
        $lApplications = Application::get();

        foreach($lApplications as $application){
            $lDays = [];
            $pay_id = \DB::table('users')->where('id', $application->user_id)->value('payment_frec_id');
            $start_date = Carbon::parse($application->start_date);
            $end_date = Carbon::parse($application->end_date);
            $oDate = Carbon::parse($application->start_date);
            $efective_days = $application->total_days;
            $calendar_days = $application->tot_calendar_days;
            $lHolidays = \DB::table('holidays')
                            ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                            ->where('is_deleted', 0)
                            ->pluck('fecha')
                            ->toArray();
                
            if($pay_id == 2){
                for ($i=0; $i < $calendar_days; $i++) { 
                    if($oDate->dayOfWeek != Carbon::SATURDAY && $oDate->dayOfWeek != Carbon::SUNDAY && 
                        !in_array($oDate->toDateString(), $lHolidays)){
                            $json = [
                                'date' => $oDate->toDateString(),
                                'bussinesDay' => true,
                                'taked' => true,
                                'isOptional' => false,
                            ];
                            array_push($lDays, $json);
                    }
                    $oDate->addDay();
                }
                if(count($lDays) < $efective_days){
                    $oDate = Carbon::parse($application->start_date);
                    for ($i=0; $i < $calendar_days; $i++) { 
                        if($oDate->dayOfWeek == Carbon::SATURDAY || $oDate->dayOfWeek == Carbon::SUNDAY || 
                            in_array($oDate->toDateString(), $lHolidays) ){
                                $json = [
                                    'date' => $oDate->toDateString(),
                                    'bussinesDay' => false,
                                    'taked' => true,
                                    'isOptional' => true,
                                ];
                                array_push($lDays, $json);
                        }
                        if(count($lDays) == $efective_days){
                            break;
                        }
                        $oDate->addDay();
                    }
                }
            }else{
                for ($i=0; $i < $calendar_days; $i++) { 
                    if($oDate->dayOfWeek != Carbon::SUNDAY && !in_array($oDate->toDateString(), $lHolidays)){
                        $json = [
                            'date' => $oDate->toDateString(),
                            'bussinesDay' => true,
                            'taked' => true,
                            'isOptional' => false,
                        ];
                        array_push($lDays, $json);
                    }
                    $oDate->addDay();
                }
                if(count($lDays) < $efective_days){
                    $oDate = Carbon::parse($application->start_date);
                    for ($i=0; $i < $calendar_days; $i++) { 
                        if($oDate->dayOfWeek == Carbon::SUNDAY || 
                                    in_array($oDate->toDateString(), $lHolidays) ){
                                        $json = [
                                            'date' => $oDate->toDateString(),
                                            'bussinesDay' => false,
                                            'taked' => true,
                                            'isOptional' => true,
                                        ];
                                        array_push($lDays, $json);
                        }
                        if(count($lDays) == $efective_days){
                            break;
                        }
                        $oDate->addDay();
                    }
                }
            }

            $application->ldays = $lDays;
            $application->update();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('ldays');
        });
    }
}

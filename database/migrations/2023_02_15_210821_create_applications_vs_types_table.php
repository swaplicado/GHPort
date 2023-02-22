<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Vacations\ApplicationVsTypes;

class CreateApplicationsVsTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications_vs_types', function (Blueprint $table) {
            $table->bigIncrements('id_application_vs_type');
            $table->unsignedBigInteger('application_id');
            $table->boolean('is_normal');
            $table->boolean('is_past');
            $table->boolean('is_advanced');
            $table->boolean('is_proportional');
            $table->boolean('is_season_special');
            $table->timestamps();
        });

        $applications = \DB::table('applications')
                            ->where('is_deleted', 0)
                            ->get();

        foreach($applications as $app){
            $applicationVsTypes = new ApplicationVsTypes();
            $applicationVsTypes->application_id = $app->id_application;
            $applicationVsTypes->is_normal = 1;
            $applicationVsTypes->is_past = 0;
            $applicationVsTypes->is_advanced = 0;
            $applicationVsTypes->is_proportional = 0;
            $applicationVsTypes->is_season_special = 0;
            $applicationVsTypes->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applications_vs_types');
    }
}

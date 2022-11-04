<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVacationPlanDaysLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vacation_plan_days_logs', function (Blueprint $table) {
            $table->bigIncrements('id_vacation_plan_day_log');
            $table->unsignedBigInteger('vacations_plan_id');
            $table->integer('until_year');
            $table->integer('vacation_days');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('vacations_plan_id')->references('id_vacation_plan')->on('cat_vacation_plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vacation_plan_days_logs');
    }
}

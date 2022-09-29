<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatVacationPlansDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cat_vacation_plans_days', function (Blueprint $table) {
            $table->bigIncrements('id_anniversary');
            $table->unsignedBigInteger('vacations_plan_id');
            $table->integer('vacation_days');
            $table->timestamps();

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
        Schema::dropIfExists('cat_vacation_plans_days');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsBreakdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications_breakdowns', function (Blueprint $table) {
            $table->bigIncrements('id_application_breakdown');
            $table->unsignedBigInteger('application_id');
            $table->integer('days_effective');
            $table->integer('application_year');
            $table->integer('admition_count');
            $table->timestamps();

            $table->foreign('application_id')->references('id_application')->on('applications');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applications_breakdowns');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialSeasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_season', function (Blueprint $table) {
            $table->bigIncrements('id_special_season');
            $table->unsignedBigInteger('org_chart_job_id')->nullable();
            $table->unsignedBigInteger('depto_id')->nullable();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedBigInteger('special_season_type_id');
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('org_chart_job_id')->references('id_org_chart_job')->on('org_chart_jobs');
            $table->foreign('depto_id')->references('id_department')->on('ext_departments');
            $table->foreign('job_id')->references('id_job')->on('ext_jobs');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('company_id')->references('id_company')->on('ext_company');
            $table->foreign('special_season_type_id')->references('id_special_season_type')->on('special_season_types');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_season');
    }
}

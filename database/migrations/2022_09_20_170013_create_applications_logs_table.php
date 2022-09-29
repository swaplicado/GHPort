<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications_logs', function (Blueprint $table) {
            $table->bigIncrements('id_application_log');
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('application_status_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('application_id')->references('id_application')->on('applications');
            $table->foreign('application_status_id')->references('id_applications_st')->on('sys_applications_sts');
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
        Schema::dropIfExists('applications_logs');
    }
}

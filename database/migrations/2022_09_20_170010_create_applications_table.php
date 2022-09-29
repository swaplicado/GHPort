<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->bigIncrements('id_application');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('request_status_id');
            $table->unsignedBigInteger('type_incident_id');
            $table->boolean('is_deleted');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('request_status_id')->references('id_applications_st')->on('sys_applications_sts');
            $table->foreign('type_incident_id')->references('id_incidence_tp')->on('cat_incidence_tps');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applications');
    }
}

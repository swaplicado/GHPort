<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTpIncidentsPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tp_incidents_pivot', function (Blueprint $table) {
            $table->bigIncrements('id_pivot');
            $table->unsignedBigInteger('tp_incident_id');
            $table->bigInteger('ext_tp_incident_id');
            $table->bigInteger('ext_cl_incident_id');
            $table->unsignedBigInteger('int_sys_id');
            $table->boolean('is_deleted')->default(0);

            $table->foreign('tp_incident_id')->references('id_incidence_tp')->on('cat_incidence_tps');
            $table->foreign('int_sys_id')->references('id_int_sys')->on('interact_systems');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tp_incidents_pivot');
    }
}

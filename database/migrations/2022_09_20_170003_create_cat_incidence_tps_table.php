<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatIncidenceTpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cat_incidence_tps', function (Blueprint $table) {
            $table->bigIncrements('id_incidence_tp');
            $table->unsignedBigInteger('incidence_cl_id');
            $table->string('incidence_tp_name');
            $table->bigInteger('external_id');
            $table->boolean('is_active');
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('incidence_cl_id')->references('id_incidence_cl')->on('cat_incidence_cls');
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
        Schema::dropIfExists('cat_incidence_tps');
    }
}

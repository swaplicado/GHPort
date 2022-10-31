<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramedAuxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programed_aux', function (Blueprint $table) {
            $table->bigIncrements('id_programed');
            $table->unsignedBigInteger('employee_id');
            $table->integer('days_to_consumed');
            $table->integer('anniversary');
            $table->integer('year');
            $table->boolean('is_deleted');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('programed_aux');
    }
}

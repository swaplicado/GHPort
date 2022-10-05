<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAdmissionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_admission_logs', function (Blueprint $table) {
            $table->bigIncrements('id_user_admission_log');
            $table->unsignedBigInteger('user_id');
            $table->date('user_admission_date');
            $table->date('user_leave_date')->nullable();
            $table->integer('admission_count');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_admission_logs');
    }
}

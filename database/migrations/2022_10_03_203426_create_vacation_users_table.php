<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVacationUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vacation_users', function (Blueprint $table) {
            $table->bigIncrements('id_vacation_user');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('user_admission_log_id');
            $table->integer('id_anniversary');
            $table->integer('year');
            $table->date('date_start');
            $table->date('date_end');
            $table->integer('vacation_days');
            $table->boolean('is_closed');
            $table->boolean('is_closed_manually');
            $table->unsignedBIgInteger('closed_by_n')->nullable();
            $table->boolean('is_expired');
            $table->boolean('is_expired_manually');
            $table->unsignedBigInteger('expired_by_n')->nullable();
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_admission_log_id')->references('id_user_admission_log')->on('user_admission_logs')->onDelete('cascade');
            $table->foreign('closed_by_n')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('expired_by_n')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vacation_users');
    }
}
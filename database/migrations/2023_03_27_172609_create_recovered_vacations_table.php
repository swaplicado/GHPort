<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecoveredVacationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recovered_vacations', function (Blueprint $table) {
            $table->bigIncrements('id_recovered_vacation');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('vacation_user_id');
            $table->integer('recovered_days');
            $table->integer('used_days_n')->nullable();
            $table->integer('consumed_days_n')->nullable();
            $table->date('end_date');
            $table->boolean('is_used')->default(0);
            $table->boolean('is_deleted')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->ondelete('cascade');
            $table->foreign('vacation_user_id')->references('id_vacation_user')->on('vacation_users')->ondelete('cascade');
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
        Schema::dropIfExists('recovered_vacations');
    }
}

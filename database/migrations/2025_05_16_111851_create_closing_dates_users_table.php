<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClosingDatesUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('closing_dates_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('closing_date_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->foreign('closing_date_id')->references('id_closing_dates')->on('closing_dates')->onDelete('cascade');
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
        Schema::dropIfExists('closing_dates_users');
    }
}

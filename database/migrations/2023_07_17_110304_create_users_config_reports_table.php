<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersConfigReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_config_reports', function (Blueprint $table) {
            $table->bigIncrements('id_config_report');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_active');
            $table->boolean('all_employees')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_config_reports');
    }
}

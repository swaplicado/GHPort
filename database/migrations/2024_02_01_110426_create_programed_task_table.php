<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramedTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysqlGlobalUsers')->create('programed_task', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('global_user_id');
            $table->unsignedBigInteger('system_id');
            $table->unsignedBigInteger('status_id');
            $table->date('executed_on');
            $table->timestamps();

            $table->foreign('system_id')->references('id_system')->on('systems');
            $table->foreign('status_id')->references('id_status_task')->on('status_task');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysqlGlobalUsers')->dropIfExists('programed_task');
    }
}

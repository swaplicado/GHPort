<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_status', function (Blueprint $table) {
            $table->bigIncrements('id_task_status');
            $table->string('status');
            $table->timestamps();
        });

        \DB::table('task_status')->insert([
            ['status' => 'Pendiente'],
            ['status' => 'Realizado'],
            ['status' => 'Cancelado'],
            ['status' => 'Error'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_status');
    }
}

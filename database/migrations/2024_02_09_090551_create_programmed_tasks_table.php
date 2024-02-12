<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgrammedTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programmed_tasks', function (Blueprint $table) {
            $table->bigIncrements('id_task');
            $table->time('execute_on')->nullable();
            $table->time('donde_at')->nullable();
            $table->string('cfg');
            $table->unsignedBigInteger('task_type_id');
            $table->unsignedBigInteger('status');
            $table->boolean('is_deleted')->default(0);
            $table->timestamps();

            $table->foreign('task_type_id')->references('id_task_type')->on('task_types')->onDelete('cascade');
            $table->foreign('status')->references('id_task_status')->on('task_status')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('programmed_tasks');
    }
}

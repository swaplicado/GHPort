<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsExecutionIncidencesReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs_execution_incidences_report', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type_report');
            $table->dateTime('executed_at');
            $table->text('applications_sended');
            $table->text('hours_leave_sended');
            $table->text('to_users');
            $table->dateTime('next_execution')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs_execution_incidences_report');
    }
}

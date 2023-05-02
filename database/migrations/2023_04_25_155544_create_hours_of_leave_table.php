<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHoursOfLeaveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hours_leave', function (Blueprint $table) {
            $table->bigIncrements('id_hours_leave');
            $table->string('folio_n');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('date_send_n');
            $table->integer('total_days');
            $table->integer('tot_calendar_days');
            $table->text('ldays');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('request_status_id');
            $table->unsignedBigInteger('type_permission_id');
            $table->unsignedBigInteger('user_apr_rej_id');
            $table->date('approved_date_n');
            $table->date('rejected_date_n');
            $table->text('sup_comments_n');
            $table->text('emp_comments_n');
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('request_status_id')->references('id_applications_st')->on('sys_applications_sts');
            $table->foreign('type_permission_id')->references('id_permission_tp')->on('cat_permission_tp');
            $table->foreign('user_apr_rej_id')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');

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
        Schema::dropIfExists('hours_of_leave');
    }
}

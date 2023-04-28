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
            $table->unsignedBigInteger('type_incident_id');
            $table->unsignedBigInteger('user_apr_rej_id');
            $table->date('approved_date_n');
            $table->date('rejected_date_n');
            $table->text('sup_comments_n');
            $table->text('emp_comments_n');
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

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

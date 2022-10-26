<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->bigIncrements('id_mail_log');
            $table->date('date_log');
            $table->unsignedBigInteger('to_user_id');
            $table->unsignedBigInteger('application_id_n')->nullable();
            $table->unsignedBigInteger('sys_mails_st_id');
            $table->unsignedBigInteger('type_mail_id');
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('application_id_n')->references('id_application')->on('applications')->onDelete('cascade');
            $table->foreign('sys_mails_st_id')->references('id_mail_st')->on('sys_mails_sts')->onDelete('cascade');
            $table->foreign('type_mail_id')->references('id_mail_tp')->on('cat_mails_tps')->onDelete('cascade');
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('mail_logs');
    }
}

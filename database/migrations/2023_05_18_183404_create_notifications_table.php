<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id_notification');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('message');
            $table->string('url');
            $table->boolean('is_revised');
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('type_id');
            $table->integer('priority');
            $table->string('icon');
            $table->boolean('is_pendent');
            $table->unsignedBigInteger('row_type_id')->nullable();
            $table->unsignedBigInteger('row_id')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('type_id')->references('id_notify_type')->on('cat_notifications_type');
            $table->foreign('row_type_id')->references('id_incidence_tp')->on('cat_incidence_tps');
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
        Schema::dropIfExists('notifications');
    }
}

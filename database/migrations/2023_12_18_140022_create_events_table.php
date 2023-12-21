<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cat_events', function (Blueprint $table) {
            $table->bigIncrements('id_event');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('ldays');
            $table->integer('total_days');
            $table->date('return_date');
            $table->integer('tot_calendar_days');
            $table->integer('priority');
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
        Schema::dropIfExists('cat_events');
    }
}

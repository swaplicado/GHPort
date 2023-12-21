<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsAssignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events_assigns', function (Blueprint $table) {
            $table->bigIncrements('id_event_assign');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id_n')->nullable();
            $table->unsignedBigInteger('group_id_n')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->foreign('event_id')->references('id_event')->on('cat_events')->onDelete('cascade');
            $table->foreign('user_id_n')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('group_id_n')->references('id_group')->on('groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events_assigns');
    }
}

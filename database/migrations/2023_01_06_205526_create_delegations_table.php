<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDelegationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delegations', function (Blueprint $table) {
            $table->bigIncrements('id_delegation');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('user_delegation_id');
            $table->unsignedBigInteger('user_delegated_id');
            $table->boolean('is_active');
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('user_delegation_id')->references('id')->on('users')->ondelete('cascade');
            $table->foreign('user_delegated_id')->references('id')->on('users')->ondelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->ondelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->ondelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delegations');
    }
}

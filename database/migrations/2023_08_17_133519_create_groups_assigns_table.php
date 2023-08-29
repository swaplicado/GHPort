<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsAssignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups_assigns', function (Blueprint $table) {
            $table->bigIncrements('id_group_assign');
            $table->unsignedBigInteger('user_id_n')->nullable();
            $table->unsignedBigInteger('org_chart_job_id_n')->nullable();
            $table->unsignedBigInteger('group_id_n')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

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
        Schema::dropIfExists('groups_assigns');
    }
}

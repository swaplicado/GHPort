<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientToHoursLeave extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hours_leave', function (Blueprint $table) {
            $table->integer('requested_client')->nullable()->after('cl_permission_id');
            $table->integer('authorized_client')->nullable()->after('requested_client');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hours_leave', function (Blueprint $table) {
            $table->dropColumn('authorized_client');
            $table->dropColumn('requested_client');
        });
    }
}
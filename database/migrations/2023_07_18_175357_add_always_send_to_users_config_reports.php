<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlwaysSendToUsersConfigReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_config_reports', function (Blueprint $table) {
            $table->boolean('always_send')->default(0)->after('all_employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_config_reports', function (Blueprint $table) {
            $table->dropColumn('always_send');
        });
    }
}

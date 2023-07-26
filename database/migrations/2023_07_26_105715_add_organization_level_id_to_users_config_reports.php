<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrganizationLevelIdToUsersConfigReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_config_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_level_id')->nullable()->after('always_send');

            $table->foreign('organization_level_id')->references('id_organization_level')->on('organization_levels');
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
            $table->dropForeign(['organization_level_id']);
            $table->dropColumn('organization_level_id');
        });
    }
}

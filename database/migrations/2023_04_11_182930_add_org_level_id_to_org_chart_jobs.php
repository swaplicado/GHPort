<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrgLevelIdToOrgChartJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('org_chart_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('org_level_id')->after('area_name_ui')->default(1);
            $table->foreign('org_level_id')->references('id_organization_level')->on('organization_levels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('org_chart_jobs', function (Blueprint $table) {
            $table->dropForeign(['org_level_id']);
            $table->dropColumn('org_level_id');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLeaderAreaToOrgChartJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('org_chart_jobs', function (Blueprint $table) {
            $table->boolean('is_leader_area')->default(0)->after('is_boss');
            $table->boolean('is_leader_config')->default(0)->after('is_leader_area');
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
            $table->dropColumn('is_leader_config');
            $table->dropColumn('is_leader_area');
        });
    }
}

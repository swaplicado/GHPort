<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsBossToOrgChartJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('org_chart_jobs', function (Blueprint $table) {
            $table->boolean('is_boss')->after('positions');
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
            $table->dropForeign(['is_boss']);
        });
    }
}

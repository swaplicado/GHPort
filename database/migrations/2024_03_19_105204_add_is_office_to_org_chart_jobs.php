<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsOfficeToOrgChartJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('org_chart_jobs', function (Blueprint $table) {
            $table->boolean('is_office')->default(true)->after('is_area');
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
            $table->dropColumn('is_office');
        });
    }
}

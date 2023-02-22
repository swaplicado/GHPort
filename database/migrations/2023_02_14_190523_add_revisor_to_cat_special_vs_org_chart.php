<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRevisorToCatSpecialVsOrgChart extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_special_vs_org_chart', function (Blueprint $table) {
            $table->unsignedBigInteger('revisor_id')->after('depto_id_n');
            $table->foreign('revisor_id')->references('id_org_chart_job')->on('org_chart_jobs')->ondelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_special_vs_org_chart', function (Blueprint $table) {
            $table->dropForeign('revisor_id');
        });
    }
}

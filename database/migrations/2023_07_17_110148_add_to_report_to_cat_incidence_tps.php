<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToReportToCatIncidenceTps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_incidence_tps', function (Blueprint $table) {
            $table->boolean('to_report')->default(1)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_incidence_tps', function (Blueprint $table) {
            $table->dropColumn('to_report');
        });
    }
}

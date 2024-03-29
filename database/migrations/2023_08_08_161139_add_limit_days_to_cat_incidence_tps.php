<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLimitDaysToCatIncidenceTps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_incidence_tps', function (Blueprint $table) {
            $table->integer('limit_days_n')->nullable()->after('is_active');
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
            $table->dropColumn('limit_days_n');
        });
    }
}

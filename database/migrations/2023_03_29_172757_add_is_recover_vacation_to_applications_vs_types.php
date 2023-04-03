<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsRecoverVacationToApplicationsVsTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications_vs_types', function (Blueprint $table) {
            $table->boolean('is_recover_vacation')->default(0)->after('is_season_special');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applications_vs_types', function (Blueprint $table) {
            $table->dropColumn('is_recover_vacation');
        });
    }
}

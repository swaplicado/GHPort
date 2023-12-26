<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsEventToApplicationsVsTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications_vs_types', function (Blueprint $table) {
            $table->boolean('is_event')->default(false)->after('is_recover_vacation');
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
            $table->dropColumn('is_event'); // remove the column 'is_event' from the 'applications_vs_types' table
        });
    }
}

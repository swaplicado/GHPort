<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntermediateToHoursLeave extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hours_leave', function (Blueprint $table) {
            $table->time('intermediate_return')->nullable()->after('minutes');
            $table->time('intermediate_out')->nullable()->after('minutes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hours_leave', function (Blueprint $table) {
            $table->dropColumn('intermediate_return');
            $table->dropColumn('intermediate_out');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToClosingDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('closing_dates', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->nullable()->after('end_date');

            $table->foreign('type_id')->references('id')->on('closing_dates_type')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('closing_dates', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
        });
    }
}

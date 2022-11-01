<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdAnniversaryToVacationsAllocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vacation_allocations', function (Blueprint $table) {
            $table->integer('id_anniversary')->after('anniversary_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vacations_allocations', function (Blueprint $table) {
            $table->dropColumn('id_anniversary');
        });
    }
}

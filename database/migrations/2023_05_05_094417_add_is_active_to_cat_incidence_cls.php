<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToCatIncidenceCls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_incidence_cls', function (Blueprint $table) {
            $table->boolean('is_active')->default(0)->after('incidence_cl_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_incidence_cls', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}

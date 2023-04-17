<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInfoToCatIncidenceTps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_incidence_tps', function (Blueprint $table) {

            $table->boolean('is_active')->default(0)->after('incidence_tp_name');
            $table->boolean('need_auth')->default(0)->after('is_active');
            $table->unsignedBigInteger('interact_system_id')->default(1)->after('need_auth');

            $table->foreign('interact_system_id')->references('id_int_sys')->on('interact_systems');
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
            $table->dropForeign(['interact_system_id']);
            $table->dropColumn('interact_system_id');
            $table->dropColumn('need_auth');
            $table->dropColumn('is_active');
        });
    }
}

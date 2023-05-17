<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNeedAuthorizationToCatPermissionTp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_permission_tp', function (Blueprint $table) {
            $table->boolean('need_authorization')->after('permission_tp_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_permission_tp', function (Blueprint $table) {
            $table->dropColumn('need_authorization');
        });
    }
}

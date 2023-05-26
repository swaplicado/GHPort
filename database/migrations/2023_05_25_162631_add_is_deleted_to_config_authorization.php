<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDeletedToConfigAuthorization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config_authorization', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(0)->after('need_auth');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config_authorization', function (Blueprint $table) {
            $table->dropColumn('is_deleted');
        });
    }
}

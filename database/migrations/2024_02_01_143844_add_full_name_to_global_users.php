<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFullNameToGlobalUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysqlGlobalUsers')->table('global_users', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('email');
            $table->string('external_id')->nullable()->after('username');
            $table->string('employee_num')->nullable()->after('external_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysqlGlobalUsers')->table('global_users', function (Blueprint $table) {
            $table->dropColumn('full_name');
            $table->dropColumn('external_id');
            $table->dropColumn('employee_num');
        });
    }
}

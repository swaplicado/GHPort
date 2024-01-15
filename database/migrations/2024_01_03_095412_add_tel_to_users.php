<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTelToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_directory')->nullable()->after('institutional_mail');
            $table->string('tel_area')->nullable()->after('email_directory');
            $table->string('tel_num')->nullable()->after('tel_area');
            $table->string('tel_ext')->nullable()->after('tel_num');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_directory');
            $table->dropColumn('tel_area');
            $table->dropColumn('tel_num');
            $table->dropColumn('tel_ext');
        });
    }
}

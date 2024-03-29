<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysqlGlobalUsers')->create('global_users', function (Blueprint $table) {
            $table->bigIncrements('id_global_user');
            $table->string('username');
            $table->string('password');
            $table->string('email')->nullable();
            $table->string('full_name');
            $table->string('external_id')->nullable();
            $table->string('employee_num')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_deleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysqlGlobalUsers')->dropIfExists('global_users');
    }
}

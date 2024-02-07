<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersVsSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysqlGlobalUsers')->create('users_vs_systems', function (Blueprint $table) {
            $table->bigIncrements('id_user_vs_system');
            $table->unsignedBigInteger('global_user_id'); //el id de usuario de la tabla global
            $table->unsignedBigInteger('system_id'); //el id de sistema
            $table->unsignedBigInteger('user_system_id'); //el id de usuario en el sistema
            $table->timestamps();

            $table->foreign('global_user_id')->references('id_global_user')->on('global_users')->onDelete('cascade');
            $table->foreign('system_id')->references('id_system')->on('systems')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysqlGlobalUsers')->dropIfExists('users_vs_systems');
    }
}

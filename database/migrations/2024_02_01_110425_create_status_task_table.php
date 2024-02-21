<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysqlGlobalUsers')->create('status_task', function (Blueprint $table) {
            $table->bigIncrements('id_status_task');
            $table->string('status');
            $table->timestamps();
        });

        \DB::connection('mysqlGlobalUsers')->table('status_task')->insert([
            ['status' => 'Realizado',
            'created_at' => now(),
            'updated_at' => now(),],

            ['status' => 'Pendiente',
            'created_at' => now(),
            'updated_at' => now(),],

            ['status' => 'Error',
            'created_at' => now(),
            'updated_at' => now(),]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysqlGlobalUsers')->dropIfExists('status_task');
    }
}

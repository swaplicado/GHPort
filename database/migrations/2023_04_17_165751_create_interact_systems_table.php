<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInteractSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interact_systems', function (Blueprint $table) {
            $table->bigIncrements('id_int_sys');
            $table->string('name');
            $table->string('url');
            $table->boolean('is_deleted')->default(0);
        });

        DB::table('interact_systems')->insert([
            ['id_int_sys' => 1, 'name' => 'Pendiente', 'url' => ' ' ],
            ['id_int_sys' => 2, 'name' => 'SIIE', 'url' => '192.168.1.233:8080' ],
            ['id_int_sys' => 3, 'name' => 'CAP', 'url' => '192.168.1.16:444' ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interact_systems');
    }
}

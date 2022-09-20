<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdmRolTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_rol', function (Blueprint $table) {
            $table->bigIncrements('id_rol');
            $table->string('rol',100);
            $table->boolean('is_delete');
            $table->timestamp();    
        });

        DB::table('adm_rol')->insert([
            ['id_rol' => 1, 'rol' => 'Estandar', 'is_delete' => 0 ],
            ['id_rol' => 2, 'rol' => 'Jefe', 'is_delete' => 0 ],
            ['id_rol' => 3, 'rol' => 'GH', 'is_delete' => 0],
            ['id_rol' => 4, 'rol' => 'Administrador', 'is_delete' => 0]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adm_rol');
    }
}

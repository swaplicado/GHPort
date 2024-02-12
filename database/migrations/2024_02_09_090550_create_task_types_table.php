<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_types', function (Blueprint $table) {
            $table->bigIncrements('id_task_type');
            $table->string('task');
            $table->boolean('is_deleted')->default(0);
            $table->timestamps();
        });

        \DB::table('task_types')->insert([
            ['task' => 'Insertar usuario en Usuarios Globales', 'is_deleted' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['task' => 'Insertar usuario en PGH', 'is_deleted' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['task' => 'Insertar usuario en UNIV', 'is_deleted' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['task' => 'Insertar usuario en CAP', 'is_deleted' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['task' => 'Insertar usuario en EVAL', 'is_deleted' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['task' => 'Actualizar usuario en Usuario Globales', 'is_deleted' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['task' => 'Actualizar usuario en PGH', 'is_deleted' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['task' => 'Actualizar usuario en UNIV', 'is_deleted' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['task' => 'Actualizar usuario en CAP', 'is_deleted' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['task' => 'Actualizar usuario en EVAL', 'is_deleted' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_types');
    }
}

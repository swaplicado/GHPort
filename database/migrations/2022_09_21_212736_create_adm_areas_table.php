<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_areas', function (Blueprint $table) {
            $table->bigIncrements('id_area');
            $table->string('area');
            $table->integer('father_area_id')->nullable();
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by_id');
            $table->unsignedBigInteger('updated_by_id');
            $table->timestamps();

            $table->foreign('created_by_id')->references('id')->on('users');
            $table->foreign('updated_by_id')->references('id')->on('users');
        });

        $areas = [
            ['id_area' => 1, 'area' => 'Dirección general', 'father_area_id' => null, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 2, 'area' => 'Gerente de planta e innovación', 'father_area_id' => 1, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 3, 'area' => 'Gerente de gestión humana', 'father_area_id' => 1, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 4, 'area' => 'Responsable de compensaciones y beneficios', 'father_area_id' => 3, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 5, 'area' => 'Responsable de desarrollo organizacional', 'father_area_id' => 3, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 6, 'area' => 'Responsable de reclutamiento y selección', 'father_area_id' => 3, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 7, 'area' => 'Médico de planta', 'father_area_id' => 3, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 8, 'area' => 'Encargado de seguridad', 'father_area_id' => 3, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 9, 'area' => 'Gerente de compras , granos y semillas', 'father_area_id' => 1, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 10, 'area' => 'Promotor de cultivos', 'father_area_id' => 9, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 11, 'area' => 'Gerente de contabilidad y administración', 'father_area_id' => 1, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 12, 'area' => 'Contador', 'father_area_id' => 11, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 13, 'area' => 'Auxiliar contable', 'father_area_id' => 12, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 14, 'area' => 'Auxiliar devolución impuestos', 'father_area_id' => 12, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 15, 'area' => 'Auxiliar contable', 'father_area_id' => 11, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 16, 'area' => 'Auxiliar administrativo Recepción', 'father_area_id' => 11, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 17, 'area' => 'Responsable de sistemas', 'father_area_id' => 11, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 18, 'area' => 'Analista de sistemas', 'father_area_id' => 17, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 19, 'area' => 'Gerente de desarrollo de software', 'father_area_id' => 1, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 20, 'area' => 'Desarrollador de software', 'father_area_id' => 19, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 21, 'area' => 'Encargado de tesorería corporativo', 'father_area_id' => 1, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 22, 'area' => 'Encargado de riesgos y bancos , corporativo', 'father_area_id' => 1, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 23, 'area' => 'Encargado de tesorería', 'father_area_id' => 1, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 24, 'area' => 'Encargado de embarques y facturación', 'father_area_id' => 1, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 25, 'area' => 'Administrador de personal y RH', 'father_area_id' => 3, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 26, 'area' => 'Analista DO', 'father_area_id' => 3, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 27, 'area' => 'Desarrollador y difusión interna , comunicación', 'father_area_id' => 3, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 28, 'area' => 'Analista de GH', 'father_area_id' => 3, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 29, 'area' => 'Intendencia', 'father_area_id' => 3, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 30, 'area' => 'Chofer', 'father_area_id' => 8, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 31, 'area' => 'Vigilante', 'father_area_id' => 8, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 32, 'area' => 'Gerente de compras y fruta', 'father_area_id' => 1, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 33, 'area' => 'Encargado de logística', 'father_area_id' => 32, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 34, 'area' => 'Auxiliar administrativo', 'father_area_id' => 32, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 35, 'area' => 'Auxiliar contable', 'father_area_id' => 32, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 36, 'area' => 'Jefe de operaciones', 'father_area_id' => 2, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 37, 'area' => 'Jefe de calidad', 'father_area_id' => 2, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 38, 'area' => 'Responsable de normatividad', 'father_area_id' => 37, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 39, 'area' => 'Encargado de laboratorio', 'father_area_id' => 37, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 40, 'area' => 'Analista de laboratorio', 'father_area_id' => 39, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 41, 'area' => 'Supervisor de aseguramiento de calidad', 'father_area_id' => 37, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 42, 'area' => 'Supervisor de seguridad e higiene', 'father_area_id' => 37, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 43, 'area' => 'Intendencia', 'father_area_id' => 41, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 44, 'area' => 'Jefe de investigación y desarrollo', 'father_area_id' => 2, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 45, 'area' => 'Jefe de planeación', 'father_area_id' => 2, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
            ['id_area' => 46, 'area' => 'Encargado de báscula', 'father_area_id' => 45, 'is_deleted' => 0, 'created_by_id' => 1, 'updated_by_id' => 1, 'created_at' => '2022-09-05 00:00:00', 'updated_at' => '2022-09-05 00:00:00'],
        ];

        \DB::table('adm_areas')->insert($areas);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adm_areas');
    }
}

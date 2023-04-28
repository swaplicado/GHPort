<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_levels', function (Blueprint $table) {
            $table->bigIncrements('id_organization_level');
            $table->string('name')->nullable();
            $table->integer('level');
        });

        DB::table('organization_levels')->insert([
            ['id_organization_level' => 1, 'name' => 'Pendiente', 'level' => 0 ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organization_levels');
    }
}

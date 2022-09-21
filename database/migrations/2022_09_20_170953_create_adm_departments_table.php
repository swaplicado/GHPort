<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_departments', function (Blueprint $table) {
            $table->bigIncrements('id_department');
            $table->string('name',100);
            $table->string('abbreviation',50);
            $table->unsignedBigInteger('department_n_id')->nullable();
            $table->unsignedBigInteger('head_user_n_id')->nullable();
            $table->boolean('is_delete');
            $table->integer('external_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('department_n_id')->references('id_department')->on('adm_departments')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });

        DB::table('adm_departments')->insert(
            array(
                'name' => 'DEFAULT',
                'abbreviation' => 'DEF',
                'department_n_id' => null,
                'head_user_n_id' => null,
                'is_delete' => 0,
                'external_id' => 0,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adm_departments');
    }
}

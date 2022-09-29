<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_departments', function (Blueprint $table) {
            $table->bigIncrements('id_department');
            $table->string('department_code');
            $table->string('department_name');
            $table->string('department_name_ui');
            $table->unsignedBigInteger('department_id_n')->nullable();
            $table->bigInteger('external_id_n')->nullable();
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('department_id_n')->references('id_department')->on('ext_departments')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        DB::table('ext_departments')->insert(
            array(
                'department_code' => 'DEF',
                'department_name' => 'DEFAULT',
                'department_name_ui' => '',
                'department_id_n' => null,
                'external_id_n' => null,
                'is_deleted' => 0,
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
        Schema::dropIfExists('ext_departments');
    }
}

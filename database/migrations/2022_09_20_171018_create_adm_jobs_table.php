<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_jobs', function (Blueprint $table) {
            $table->bigIncrements('id_job');
            $table->string('name');
            $table->string('abbreviation');
            $table->integer('hierarchical_level');
            $table->unsignedBigInteger('department_id');
            $table->boolean('is_delete');
            $table->integer('external_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('department_id')->references('id_department')->on('adm_departments')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });

        DB::table('adm_jobs')->insert(
            array(
                'name' => 'DEF',
                'abbreviation' => 'DEF',
                'hierarchical_level' => 0,
                'department_id' => 1,
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
        Schema::dropIfExists('adm_jobs');
    }
}

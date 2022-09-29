<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_jobs', function (Blueprint $table) {
            $table->bigIncrements('id_job');
            $table->unsignedBigInteger('department_id');
            $table->string('job_code');
            $table->string('job_name');
            $table->string('job_name_ui');
            $table->bigInteger('external_id_n')->nullable();
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('department_id')->references('id_department')->on('ext_departments')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        DB::table('ext_jobs')->insert(
            array(
                'department_id' => 1,
                'job_code' => 'DEF',
                'job_name' => 'DEFAULT',
                'job_name_ui' => '',
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
        Schema::dropIfExists('ext_jobs');
    }
}

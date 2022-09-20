<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('num_employee')->after('password');
            $table->string('first_name',100)->after('num_employee');
            $table->string('last_name',100)->after('first_name');
            $table->string('full_name',200)->after('last_name');
            $table->boolean('is_active')->after('full_name');
            $table->unsignedBigInteger('department_id')->after('is_active');
            $table->unsignedBigInteger('job_id')->after('department_id');
            $table->integer('external_id')->after('job_id');
            $table->boolean('is_delete')->after('external_id');
            $table->unsignedBigInteger('created_by')->after('is_delete');
            $table->unsignedBigInteger('updated_by')->after('created_by');

            $table->foreign('department_id')->references('id_department')->on('adm_departments');
            $table->foreign('job_id')->references('id_job')->on('jobs');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}

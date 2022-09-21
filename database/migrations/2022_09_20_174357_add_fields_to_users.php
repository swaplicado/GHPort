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
            $table->unsignedBigInteger('department_id')->default(1)->after('is_active');
            $table->unsignedBigInteger('job_id')->default(1)->after('department_id');
            $table->integer('external_id')->after('job_id');
            $table->boolean('is_delete')->after('external_id');
            $table->unsignedBigInteger('created_by')->default(1)->after('is_delete');
            $table->unsignedBigInteger('updated_by')->default(1)->after('created_by');

            $table->foreign('department_id')->references('id_department')->on('adm_departments')->onDelete('cascade');
            $table->foreign('job_id')->references('id_job')->on('adm_jobs')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });

        DB::table('users')
        ->where('id', 1)
        ->update(
            array(
                'username' => 'admin',
                'email' => 'adrian.aviles@swaplicado.com.mx',
                'password' => bcrypt('123456'),
                'num_employee' => 0,
                'is_active' => 1,
                'department_id' => 1,
                'job_id' => 1,
                'external_id' => 0,
                'is_delete' => 0,
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['job_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });
    }
}

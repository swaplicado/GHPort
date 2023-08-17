<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionClTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_cl', function (Blueprint $table) {
            $table->bigIncrements('id_permission_cl');
            $table->string('permission_cl_name');
            $table->integer('max_minutes')->default(0);
            $table->integer('min_minutes')->default(0);
            $table->boolean('is_active')->default(0);
            $table->boolean('is_deleted')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
        });

        DB::table('permission_cl')->insert(
            array(
                'permission_cl_name' => 'Permiso personal',
                'max_minutes' => 120,
                'min_minutes' => 0,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            )
        );

        DB::table('permission_cl')->insert(
            array(
                'permission_cl_name' => 'Permiso laboral',
                'max_minutes' => 720,
                'min_minutes' => 0,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            )
        );

        Schema::table('hours_leave', function (Blueprint $table) {
            $table->unsignedBigInteger('cl_permission_id')->after('type_permission_id')->default(1);

            $table->foreign('cl_permission_id')->references('id_permission_cl')->on('permission_cl'); 
        });         
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hours_leave', function (Blueprint $table) {
            $table->dropForeign('cl_permission_id');
            $table->dropColumn('cl_permission_id');
        });
        Schema::dropIfExists('permission_cl');
    }
}

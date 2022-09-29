<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_company', function (Blueprint $table) {
            $table->bigIncrements('id_company');
            $table->string('company_code');
            $table->string('company_name');
            $table->string('company_name_ui');
            $table->string('company_db_name');
            $table->boolean('is_active');
            $table->bigInteger('external_id');
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        DB::table('ext_company')->insert(
            array(
                'company_code' => 'AETH',
                'company_name' => 'Aceites especiales TH',
                'company_name_ui' => 'AETH',
                'company_db_name' => 'erp_aeth',
                'is_active' => 1,
                'external_id' => 1,
                'is_deleted' => 1,
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
        Schema::dropIfExists('ext_company');
    }
}

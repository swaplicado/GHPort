<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrgChartJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('org_chart_jobs', function (Blueprint $table) {
            $table->bigIncrements('id_org_chart_job');
            $table->string('job_code');
            $table->string('job_name');
            $table->string('job_name_ui');
            $table->unsignedBigInteger('top_org_chart_job_id_n')->nullable();
            $table->integer('positions');
            $table->boolean('is_area');
            $table->string('area_code');
            $table->string('area_name');
            $table->string('area_name_ui');
            $table->bigInteger('external_id_n')->nullable();
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('top_org_chart_job_id_n')->references('id_org_chart_job')->on('org_chart_jobs');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        DB::table('org_chart_jobs')->insert(
            array(
                'job_code' => 'DEF',
                'job_name' => 'DEFAULT',
                'job_name_ui' => 'DEF',
                'top_org_chart_job_id_n' => null,
                'positions' => 0,
                'is_area' => 0,
                'area_code' => '',
                'area_name' => '',
                'area_name_ui' => '',
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
        Schema::dropIfExists('org_chart_jobs');
    }
}
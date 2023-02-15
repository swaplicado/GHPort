<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatSpecialVsOrgChartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cat_special_vs_org_chart', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cat_special_id');
            $table->unsignedBigInteger('user_id_n')->nullable();
            $table->unsignedBigInteger('org_chart_job_id_n')->nullable();
            $table->unsignedBigInteger('company_id_n')->nullable();
            $table->unsignedBigInteger('depto_id_n')->nullable();
            $table->boolean('is_deleted')->default(0);
            // $table->unsignedBigInteger('employee_id_n')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('cat_special_id')->references('id_special_type')->on('cat_special_type')->ondelete('cascade');
            $table->foreign('user_id_n')->references('id')->on('users')->ondelete('cascade');
            $table->foreign('org_chart_job_id_n')->references('id_org_chart_job')->on('org_chart_jobs')->ondelete('cascade');
            $table->foreign('company_id_n')->references('id_company')->on('ext_company')->ondelete('cascade');
            $table->foreign('depto_id_n')->references('id_department')->on('ext_departments')->ondelete('cascade');
            // $table->foreign('employee_id_n')->references('id')->on('users')->ondelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->ondelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->ondelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cat_special_vs_org_chart');
    }
}

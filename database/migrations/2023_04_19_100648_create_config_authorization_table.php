<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigAuthorizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_authorization', function (Blueprint $table) {
            $table->bigIncrements('id_config_auth');
            $table->unsignedBigInteger('tp_incidence_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('org_chart_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('need_auth')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('tp_incidence_id')->references('id_incidence_tp')->on('cat_incidence_tps');
            $table->foreign('company_id')->references('id_company')->on('ext_company');
            $table->foreign('org_chart_id')->references('id_org_chart_job')->on('org_chart_jobs');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('config_authorization');
    }
}

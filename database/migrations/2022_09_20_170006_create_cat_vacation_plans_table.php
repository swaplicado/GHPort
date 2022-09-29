<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatVacationPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cat_vacation_plans', function (Blueprint $table) {
            $table->bigIncrements('id_vacation_plan');
            $table->string('vacation_plan_name');
            $table->unsignedBigInteger('payment_frec_id_n')->nullable();
            $table->boolean('is_unionized_n')->nullable();
            $table->date('start_date_n')->nullable();
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('payment_frec_id_n')->references('id_payment_frec')->on('cat_payment_frecs');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        DB::table('cat_vacation_plans')->insert(
            array(
                'vacation_plan_name' => 'DEFAULT',
                'payment_frec_id_n' => null,
                'is_unionized_n' => null,
                'start_date_n' => null,
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
        Schema::dropIfExists('cat_vacation_plans');
    }
}

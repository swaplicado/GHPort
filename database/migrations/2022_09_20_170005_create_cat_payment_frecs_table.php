<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatPaymentFrecsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cat_payment_frecs', function (Blueprint $table) {
            $table->bigIncrements('id_payment_frec');
            $table->string('payment_frec_code');
            $table->string('payment_frec_name');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        DB::table('cat_payment_frecs')->insert([
        	['id_payment_frec' => '1','payment_frec_code' => 'SEM', 'payment_frec_name' => 'semana', 'created_by' => '1', 'updated_by' => '1' ],
        	['id_payment_frec' => '2','payment_frec_code' => 'QNA', 'payment_frec_name' => 'quincena', 'created_by' => '1', 'updated_by' => '1'],
        ]);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cat_payment_frecs');
    }
}

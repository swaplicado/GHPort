<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RegisterTableProof extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('register_proof_personal', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_user_appl');
            $table->unsignedBigInteger('id_user_proof');
            $table->unsignedBigInteger('id_comp');
            $table->boolean('isSalary');
            $table->timestamps(); 

            $table->foreign('id_user_appl')->references('id')->on('users')->ondelete('cascade');
            $table->foreign('id_user_proof')->references('id')->on('users')->ondelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('register_proof_personal');
    }
}

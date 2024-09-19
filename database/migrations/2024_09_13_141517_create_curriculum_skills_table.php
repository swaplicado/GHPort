<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurriculumSkillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('curriculum_skills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('skill');
            $table->boolean('is_deleted')->default(0);
            $table->unsignedBigInteger('curriculum_id');
            $table->timestamps();
            
            $table->foreign('curriculum_id')->references('id')->on('curriculum')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('curriculum_skills');
    }
}

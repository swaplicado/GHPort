<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_template', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->boolean('is_deleted')->default(0);
            $table->timestamps();
        });

        $data = [
            [
                'name' => 'oficinas administrativas contingencia',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'oficinas planta 8',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'oficinas planta 8:30',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'mixto',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('schedule_template')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_template');
    }
}

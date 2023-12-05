<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleDayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_day', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('day_name');
            $table->integer('day_num');
            $table->time('entry')->nullable();
            $table->time('departure')->nullable();
            $table->boolean('is_working');
            $table->unsignedBigInteger('schedule_template_id');
            $table->boolean('is_deleted')->default(0);
            $table->timestamps();

            $table->foreign('schedule_template_id')->references('id')->on('schedule_template')->ondelete('cascade');
        });

        $data = [
            [ 'day_name' => 'Lunes', 'day_num' => 1, 'entry' => '08:30:00', 'departure' => '17:30:00',
                'is_working' => true, 'schedule_template_id' => 1, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Martes', 'day_num' => 2, 'entry' => '08:30:00', 'departure' => '17:30:00',
                'is_working' => true, 'schedule_template_id' => 1, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Miércoles', 'day_num' => 3, 'entry' => '08:30:00', 'departure' => '17:30:00',
                'is_working' => true, 'schedule_template_id' => 1, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Jueves', 'day_num' => 4, 'entry' => '08:30:00', 'departure' => '17:30:00',
                'is_working' => true, 'schedule_template_id' => 1, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Viernes', 'day_num' => 5, 'entry' => '08:30:00', 'departure' => '17:30:00',
                'is_working' => true, 'schedule_template_id' => 1, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Sábado', 'day_num' => 6, 'entry' => null, 'departure' => null,
                'is_working' => false, 'schedule_template_id' => 1, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Domingo', 'day_num' => 0, 'entry' => null, 'departure' => null,
                'is_working' => false, 'schedule_template_id' => 1, 'created_at' => now(), 'updated_at' => now() ],

            [ 'day_name' => 'Lunes', 'day_num' => 1, 'entry' => '08:00:00', 'departure' => '17:30:00',
                'is_working' => true, 'schedule_template_id' => 2, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Martes', 'day_num' => 2, 'entry' => '08:00:00', 'departure' => '17:30:00',
                'is_working' => true, 'schedule_template_id' => 2, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Miércoles', 'day_num' => 3, 'entry' => '08:00:00', 'departure' => '17:30:00',
                'is_working' => true, 'schedule_template_id' => 2, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Jueves', 'day_num' => 4, 'entry' => '08:00:00', 'departure' => '17:30:00',
                'is_working' => true, 'schedule_template_id' => 2, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Viernes', 'day_num' => 5, 'entry' => '08:00:00', 'departure' => '17:30:00',
                'is_working' => true, 'schedule_template_id' => 2, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Sábado', 'day_num' => 6, 'entry' => '09:00:00', 'departure' => '12:30:00',
                'is_working' => true, 'schedule_template_id' => 2, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Domingo', 'day_num' => 0, 'entry' => null, 'departure' => null,
                'is_working' => false, 'schedule_template_id' => 2, 'created_at' => now(), 'updated_at' => now() ],
            
            [ 'day_name' => 'Lunes', 'day_num' => 1, 'entry' => '08:30:00', 'departure' => '18:00:00',
                'is_working' => true, 'schedule_template_id' => 3, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Martes', 'day_num' => 2, 'entry' => '08:30:00', 'departure' => '18:00:00',
                'is_working' => true, 'schedule_template_id' => 3, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Miércoles', 'day_num' => 3, 'entry' => '08:30:00', 'departure' => '18:00:00',
                'is_working' => true, 'schedule_template_id' => 3, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Jueves', 'day_num' => 4, 'entry' => '08:30:00', 'departure' => '18:00:00',
                'is_working' => true, 'schedule_template_id' => 3, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Viernes', 'day_num' => 5, 'entry' => '08:30:00', 'departure' => '18:00:00',
                'is_working' => true, 'schedule_template_id' => 3, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Sábado', 'day_num' => 6, 'entry' => '09:00:00', 'departure' => '12:30:00',
                'is_working' => true, 'schedule_template_id' => 3, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Domingo', 'day_num' => 0, 'entry' => null, 'departure' => null,
                'is_working' => false, 'schedule_template_id' => 3, 'created_at' => now(), 'updated_at' => now() ],

            [ 'day_name' => 'Lunes', 'day_num' => 1, 'entry' => '08:30:00', 'departure' => '16:30:00',
                'is_working' => true, 'schedule_template_id' => 4, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Martes', 'day_num' => 2, 'entry' => '08:30:00', 'departure' => '16:30:00',
                'is_working' => true, 'schedule_template_id' => 4, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Miércoles', 'day_num' => 3, 'entry' => '08:30:00', 'departure' => '16:30:00',
                'is_working' => true, 'schedule_template_id' => 4, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Jueves', 'day_num' => 4, 'entry' => '08:30:00', 'departure' => '16:30:00',
                'is_working' => true, 'schedule_template_id' => 4, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Viernes', 'day_num' => 5, 'entry' => '08:30:00', 'departure' => '16:30:00',
                'is_working' => true, 'schedule_template_id' => 4, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Sábado', 'day_num' => 6, 'entry' => '08:00:00', 'departure' => '13:00:00',
                'is_working' => true, 'schedule_template_id' => 4, 'created_at' => now(), 'updated_at' => now() ],
            [ 'day_name' => 'Domingo', 'day_num' => 0, 'entry' => null, 'departure' => null,
                'is_working' => false, 'schedule_template_id' => 4, 'created_at' => now(), 'updated_at' => now() ],
        ];

        DB::table('schedule_day')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_day');
    }
}

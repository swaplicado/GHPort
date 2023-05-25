<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatNotificationsTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cat_notifications_type', function (Blueprint $table) {
            $table->bigIncrements('id_notify_type');
            $table->string('type');
            $table->boolean('is_deleted');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
        DB::table('cat_notifications_type')->insert([
            ['id_notify_type' => 1, 'type' => 'VACACIONES', 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_notify_type' => 2, 'type' => 'INCIDENCIA', 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_notify_type' => 3, 'type' => 'PERMISO', 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_notify_type' => 4, 'type' => 'GLOBAL', 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cat_notifications_type');
    }
}

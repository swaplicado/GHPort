<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSynchronizeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('synchronize_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('last_sync');
        });

        $lUsers = \DB::table('users')
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->get()
                    ->pluck('id')
                    ->toArray();

        $config = \App\Utils\Configuration::getConfigurations();
        $data = [];
        foreach($lUsers as $user){
            array_push($data, ['user_id' => $user, 'last_sync' => $config->lastSyncDateTime]);
        }

        \DB::table('synchronize_log')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('synchronize_log');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysqlGlobalUsers')->create('systems', function (Blueprint $table) {
            $table->bigIncrements('id_system');
            $table->string('system');
            $table->string('url');
            $table->boolean('is_deleted');
            $table->timestamps();
        });

        \DB::connection('mysqlGlobalUsers')->table('systems')->insert(
            [
                [
                    'system' => 'PortalProveedores',
                    'url' => 'XXXXXXXXXXXXXXXXXXXXX',
                    'is_deleted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                [
                    'system' => 'PortalAutorizaciones',
                    'url' => 'XXXXXXXXXXXXXXXXXXXXX',
                    'is_deleted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                [
                    'system' => 'AppsManager',
                    'url' => 'XXXXXXXXXXXXXXXXXXXXX',
                    'is_deleted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                [
                    'system' => 'SIIE',
                    'url' => 'XXXXXXXXXXXXXXXXXXXXX',
                    'is_deleted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                [
                    'system' => 'PGH',
                    'url' => 'XXXXXXXXXXXXXXXXXXXXX',
                    'is_deleted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                [
                    'system' => 'UnivAETH',
                    'url' => 'XXXXXXXXXXXXXXXXXXXXX',
                    'is_deleted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                [
                    'system' => 'CAP',
                    'url' => 'XXXXXXXXXXXXXXXXXXXXX',
                    'is_deleted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                [
                    'system' => 'EvaluacionDesempeno',
                    'url' => 'XXXXXXXXXXXXXXXXXXXXX',
                    'is_deleted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ],
        );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysqlGlobalUsers')->dropIfExists('systems');
    }
}

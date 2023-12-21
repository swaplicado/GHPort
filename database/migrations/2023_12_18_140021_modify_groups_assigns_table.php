<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyGroupsAssignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups_assigns', function (Blueprint $table) {
            $table->foreign('group_id_n')->references('id_group')->on('groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('groups_assigns', function (Blueprint $table) {
            $table->dropForeign(['group_id_n']);
        });
    }
}

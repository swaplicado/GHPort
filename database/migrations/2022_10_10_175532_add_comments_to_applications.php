<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentsToApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('sup_comments_n')->nullable()->default(null)->after('rejected_date_n');
            $table->text('emp_comments_n')->nullable()->default(null)->after('sup_comments_n');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('sup_comments_n');
            $table->dropColumn('emp_comments_n');
        });
    }
}

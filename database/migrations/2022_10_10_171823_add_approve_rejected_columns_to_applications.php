<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApproveRejectedColumnsToApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->date('approved_date_n')->nullable()->default(null)->after('type_incident_id');
            $table->date('rejected_date_n')->nullable()->default(null)->after('approvede_date_n');
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
            $table->dropColumn('approved_date_n');
            $table->dropColumn('rejected_date_n');
        });
    }
}

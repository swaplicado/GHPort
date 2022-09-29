<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('employee_num')->after('password');
            $table->string('first_name',100)->after('employee_num');
            $table->string('last_name',100)->after('first_name');
            $table->string('full_name',200)->after('last_name');
            $table->string('full_name_ui',100)->after('full_name');
            $table->string('short_name',100)->after('full_name_ui');
            $table->date('benefits_date')->default('2022-09-28')->after('short_name');
            $table->date('vacation_date')->default('2022-09-28')->after('benefits_date');
            $table->date('last_admission_date')->default('2022-09-28')->after('vacation_date');
            $table->date('last_dismiss_date_n')->default('2022-09-28')->nullable()->after('last_admission_date');
            $table->integer('current_hire_log_id')->after('last_dismiss_date_n');
            $table->boolean('is_unionized')->after('current_hire_log_id');
            $table->unsignedBigInteger('company_id')->default(1)->after('is_unionized');
            // $table->unsignedBigInteger('department_id')->default(1)->after('company_id');
            $table->unsignedBigInteger('job_id')->default(1)->after('company_id');
            $table->unsignedBigInteger('org_chart_job_id')->default(1)->after('job_id');
            $table->unsignedBigInteger('vacation_plan_id')->default(1)->after('org_chart_job_id');
            $table->boolean('is_active')->after('vacation_plan_id');
            $table->bigInteger('external_id_n')->nullable()->after('is_active');
            $table->boolean('is_delete')->after('external_id_n');
            $table->unsignedBigInteger('created_by')->default(1)->after('is_delete');
            $table->unsignedBigInteger('updated_by')->default(1)->after('created_by');

            $table->foreign('company_id')->references('id_company')->on('ext_company')->onDelete('cascade');
            // $table->foreign('department_id')->references('id_department')->on('ext_departments')->onDelete('cascade');
            $table->foreign('job_id')->references('id_job')->on('ext_jobs')->onDelete('cascade');
            $table->foreign('org_chart_job_id')->references('id_org_chart_job')->on('org_chart_jobs')->onDelete('cascade');
            $table->foreign('vacation_plan_id')->references('id_vacation_plan')->on('cat_vacation_plans')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });

        DB::table('users')
        ->where('id', 1)
        ->update(
            array(
                'employee_num' => 0,
                'first_name' => '',
                'last_name' => '',
                'full_name' => '',
                'full_name_ui' => '',
                'short_name' => '',
                'benefits_date' => date('Y-m-d'),
                'vacation_date' => date('Y-m-d'),
                'last_admission_date' => date('Y-m-d'),
                'last_dismiss_date_n' => null,
                'current_hire_log_id' => 1,
                'is_unionized' => 0,
                'is_active' => 1,
                'external_id_n' => null,
                'is_delete' => 0,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['job_id']);
            $table->dropForeign(['org_chart_job_id']);
            $table->dropForeign(['vacation_plan_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });
    }
}

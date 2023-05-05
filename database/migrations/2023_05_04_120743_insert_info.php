<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('cat_incidence_cls')->insert([
            ['id_incidence_cl' => 2, 'incidence_cl_name' => 'INASISTENCIA', 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1 ],
            ['id_incidence_cl' => 3, 'incidence_cl_name' => 'INCAPACIDAD', 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1 ], 
        ]);

        DB::table('cat_incidence_tps')->insert([
            ['id_incidence_tp' => 2, 'incidence_cl_id' => 2, 'incidence_tp_name' => 'INASISTENCIA', 'is_active' => 1, 'need_auth' => 1, 'interact_system_id' => 1, 'external_id' => 1, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_incidence_tp' => 3, 'incidence_cl_id' => 2, 'incidence_tp_name' => 'INASISTENCIA ADMINISTRATIVA', 'is_active' => 1, 'need_auth' => 1, 'interact_system_id' => 1, 'external_id' => 1, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_incidence_tp' => 4, 'incidence_cl_id' => 2, 'incidence_tp_name' => 'PERMISO SIN GOCE', 'is_active' => 1, 'need_auth' => 1, 'interact_system_id' => 1, 'external_id' => 1, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_incidence_tp' => 5, 'incidence_cl_id' => 2, 'incidence_tp_name' => 'PERMISO CON GOCE', 'is_active' => 1, 'need_auth' => 1, 'interact_system_id' => 1, 'external_id' => 1, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_incidence_tp' => 6, 'incidence_cl_id' => 2, 'incidence_tp_name' => 'PERMISO PATERNIDAD', 'is_active' => 1, 'need_auth' => 1, 'interact_system_id' => 1, 'external_id' => 1, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_incidence_tp' => 7, 'incidence_cl_id' => 2, 'incidence_tp_name' => 'PRESCRIPCIÓN MEDICA', 'is_active' => 1, 'need_auth' => 1, 'interact_system_id' => 1, 'external_id' => 1, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_incidence_tp' => 8, 'incidence_cl_id' => 2, 'incidence_tp_name' => 'TEMA LABORAL', 'is_active' => 1, 'need_auth' => 1, 'interact_system_id' => 1, 'external_id' => 1, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_incidence_tp' => 9, 'incidence_cl_id' => 2, 'incidence_tp_name' => 'CUMPLEAÑOS', 'is_active' => 1, 'need_auth' => 1, 'interact_system_id' => 1, 'external_id' => 1, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_incidence_tp' => 10, 'incidence_cl_id' => 2, 'incidence_tp_name' => 'HOMEOFFICE', 'is_active' => 1, 'need_auth' => 1, 'interact_system_id' => 1, 'external_id' => 1, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
        ]);

        DB::table('tp_incidents_pivot')->insert([
            ['id_pivot' => 1, 'tp_incident_id' => 1, 'ext_tp_incident_id' => 1, 'ext_cl_incident_id' => 3, 'int_sys_id' => 2, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_pivot' => 2, 'tp_incident_id' => 2, 'ext_tp_incident_id' => 1, 'ext_cl_incident_id' => 1, 'int_sys_id' => 2, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_pivot' => 3, 'tp_incident_id' => 3, 'ext_tp_incident_id' => 5, 'ext_cl_incident_id' => 1, 'int_sys_id' => 2, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_pivot' => 4, 'tp_incident_id' => 4, 'ext_tp_incident_id' => 2, 'ext_cl_incident_id' => 1, 'int_sys_id' => 2, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_pivot' => 5, 'tp_incident_id' => 5, 'ext_tp_incident_id' => 3, 'ext_cl_incident_id' => 1, 'int_sys_id' => 2, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_pivot' => 6, 'tp_incident_id' => 6, 'ext_tp_incident_id' => 8, 'ext_cl_incident_id' => 1, 'int_sys_id' => 2, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_pivot' => 7, 'tp_incident_id' => 7, 'ext_tp_incident_id' => 9, 'ext_cl_incident_id' => 1, 'int_sys_id' => 2, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_pivot' => 8, 'tp_incident_id' => 8, 'ext_tp_incident_id' => 10, 'ext_cl_incident_id' => 1, 'int_sys_id' => 2, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_pivot' => 9, 'tp_incident_id' => 9, 'ext_tp_incident_id' => 7, 'ext_cl_incident_id' => 1, 'int_sys_id' => 2, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
            ['id_pivot' => 10, 'tp_incident_id' => 10, 'ext_tp_incident_id' => 10, 'ext_cl_incident_id' => 1, 'int_sys_id' => 2, 'is_deleted' => 0, 'created_by' => 1, 'updated_by' => 1],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

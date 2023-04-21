<?php

namespace App\Utils;

use \App\Constants\SysConst;

class incidencesUtils {
    public static function getUserIncidences($user_id){
        $lIncidences = \DB::table('applications as ap')
                        ->leftJoin('cat_incidence_tps as tp', 'tp.id_incidence_tp', '=', 'ap.type_incident_id')
                        ->leftJoin('cat_incidence_cls as cl', 'cl.id_incidence_cl', '=', 'tp.incidence_cl_id')
                        ->leftJoin('sys_applications_sts as st', 'st.id_applications_st', '=', 'ap.request_status_id')
                        ->where('type_incident_id', '!=', SysConst::TYPE_VACACIONES)
                        ->where('ap.is_deleted', 0)
                        ->where('ap.user_id', $user_id)
                        ->select(
                            'ap.*',
                            'tp.id_incidence_tp',
                            'tp.incidence_tp_name',
                            'cl.id_incidence_cl',
                            'cl.incidence_cl_name',
                            'st.applications_st_name',
                        )
                        ->get();

        return $lIncidences;
    }
}
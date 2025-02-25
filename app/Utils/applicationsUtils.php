<?php

namespace App\Utils;

use \App\Constants\SysConst;
use Carbon\Carbon;

class applicationsUtils {
    
    /**
     * Metodo para obtener la lista de empleados a revisar, regresa los empleados directos del
     * usuario en sesion o en delegacion, asi como los usuarios que no tienen jefe directo que esten por
     * debajo de la piramide.
     */
    public static function getEmployeesToRevise(){
        try {
            $org_chart_job_id = delegationUtils::getOrgChartJobIdUser(); //obtiene el area del usuario en sesion o en delegacion
            $arrOrgJobs = orgChartUtils::getAllChildsToRevice($org_chart_job_id); //obtienes todas las areas hijos a las que revisar
            $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs); //obtienes a los empleados de las areas
            $config = \App\Utils\Configuration::getConfigurations();
            
            //Si el area "$org_chart_job_id" es el area default, obtiene tambien a los empleados sin un jefe directo
            if($org_chart_job_id == $config->default_node){
                $arrOrgJobsWitoutSuperviser = \DB::table('applications as a')
                                                ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
                                                ->where('a.send_default', 1)
                                                ->where('a.is_deleted', 0)
                                                ->where('a.request_status_id', SysConst::APPLICATION_ENVIADO)
                                                ->pluck('org_chart_job_id')
                                                ->toArray();
    
                $result = array_diff($arrOrgJobsWitoutSuperviser, $arrOrgJobs);
    
                $lEmployeesWitoutSuperviser = EmployeeVacationUtils::getlEmployees($result);
    
                $lEmployees = $lEmployees->merge($lEmployeesWitoutSuperviser);
            }
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lEmployees' => $lEmployees]);
    }

    /**
     * Metodo para obtener la lista de todos los empleados que estan por debajo en la piramide
     */
    public static function getAllEmployees(){
        try {
            if(\Auth::user()->rol_id == SysConst::ADMINISTRADOR){
                $org_chart_job_id = 2;
            }else{
                $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
            }

            $lChildAreas = orgChartUtils::getAllChildsOrgChartJob($org_chart_job_id);
            $lEmployees = \DB::table('users')
                            ->where('is_active', 1)
                            ->where('is_delete', 0)
                            ->whereIn('org_chart_job_id', $lChildAreas)
                            ->select(
                                'id',
                                'full_name_ui as employee',
                            )
                            ->get();

        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al obtener a los colaboradores', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lEmployees' => $lEmployees]);
    }

    /**
     * Metodo para obtener un array con las vacaciones de los empleados a revisar
     */
    public static function getVacations($year, $allEmployees = false, $dataEmployees = false){
        try {
            if($dataEmployees == false){
                if(!$allEmployees){
                    $data = json_decode(self::getEmployeesToRevise());
                }else{
                    $data = json_decode(self::getAllEmployees());
                }
        
                if(!$data->success){
                    return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
                }
        
                $lEmployees = $data->lEmployees;
            }else{
                $lEmployees = $dataEmployees;
            }
            $lVacations = [];
            foreach($lEmployees as $emp){
                $applications_enviado = EmployeeVacationUtils::getApplications(
                                                                $emp->id,
                                                                null,
                                                                [ SysConst::APPLICATION_ENVIADO ]
                                                            );
    
                $applications_revision = EmployeeVacationUtils::getApplications(
                                                                $emp->id,
                                                                $year,
                                                                [ 
                                                                  SysConst::APPLICATION_APROBADO,
                                                                  SysConst::APPLICATION_CONSUMIDO,
                                                                  SysConst::APPLICATION_RECHAZADO,
                                                                  SysConst::APPLICATION_CANCELADO,
                                                                ]
                                                            );
    
                $objApplications = $applications_enviado->merge($applications_revision);

                foreach($objApplications as $app){
                    $app->employee = $emp->full_name;
                    $app->type = '';
                    if($app->is_normal){
                        $app->type = $app->type . "Normal\n";
                    }
        
                    if($app->is_past){
                        $app->type = $app->type . "Días pasados\n";
                    }
        
                    if($app->is_advanced){
                        $app->type = $app->type . "Días adelantados\n";
                    }
        
                    if($app->is_proportional){
                        $app->type = $app->type . "Días proporcionales\n";
                    }
        
                    if($app->is_season_special){
                        $app->type = $app->type . "Temporada especial\n";
                    }
        
                    if($app->is_event){
                        $app->type = $app->type . "Días en evento\n";
                    }
        
                    if($app->is_recover_vacation){
                        $app->type = $app->type . "Con días vencidos\n";
                    }
        
                    array_push($lVacations, $app);
                }
            }
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']); 
        }

        return json_encode(['success' => true, 'lVacations' => $lVacations]);
    }

    /**
     * Metodo para obtener las incidencias de los empleados a revisar
     */
    public static function getIncidences($year, $allEmployees = false, $dataEmployees = false){
        try {
            if($dataEmployees == false){
                if(!$allEmployees){
                    $data = json_decode(self::getEmployeesToRevise());
                }else{
                    $data = json_decode(self::getAllEmployees());
                }
        
                if(!$data->success){
                    return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
                }
        
                $lEmployees = $data->lEmployees;
            }else{
                $lEmployees = $dataEmployees;
            }
            $lIncidences = [];
            foreach($lEmployees as $emp){
                $arrIncidencies = incidencesUtils::getUserIncidences($emp->id);
                foreach($arrIncidencies as $inc){
                    $inc->employee = $emp->full_name;
                    array_push($lIncidences, $inc);
                }
            }

        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }

    public static function getPermissionsPersonal($year, $allEmployees = false, $dataEmployees = false){
        try {
            if($dataEmployees == false){
                if(!$allEmployees){
                    $data = json_decode(self::getEmployeesToRevise());
                }else{
                    $data = json_decode(self::getAllEmployees());
                }
        
                if(!$data->success){
                    return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
                }
        
                $lEmployees = $data->lEmployees;
            }else{
                $lEmployees = $dataEmployees;
            }
            $lPermissions = [];
            foreach($lEmployees as $emp){
                $arrPermissions = permissionsUtils::getUserPermissions($emp->id, SysConst::PERMISO_PERSONAL);
                foreach($arrPermissions as $perm){
                    $perm->employee = $emp->full_name;
                    array_push($lPermissions, $perm);
                }
            }
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lPermissions' => $lPermissions]);
    }

    public static function getPermissionsLaboral($year, $allEmployees = false, $dataEmployees = false){
        try {
            if($dataEmployees == false){
                if(!$allEmployees){
                    $data = json_decode(self::getEmployeesToRevise());
                }else{
                    $data = json_decode(self::getAllEmployees());
                }
        
                if(!$data->success){
                    return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
                }
        
                $lEmployees = $data->lEmployees;
            }else{
                $lEmployees = $dataEmployees;
            }
            $lPermissions = [];
            foreach($lEmployees as $emp){
                $arrPermissions = permissionsUtils::getUserPermissions($emp->id, SysConst::PERMISO_LABORAL);
                foreach($arrPermissions as $perm){
                    $perm->employee = $emp->full_name;
                    array_push($lPermissions, $perm);
                }
            }
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lPermissions' => $lPermissions]);
    }

    public static function getApplicationVacation($application_id){
        $oApplication = \DB::table('applications as a')
                                ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
                                ->leftJoin('sys_applications_sts as ap_st', 'ap_st.id_applications_st', '=', 'a.request_status_id')
                                ->leftJoin('users as u_rev', 'u_rev.id', '=', 'a.user_apr_rej_id')
                                ->leftJoin('applications_vs_types as at', 'at.application_id', '=', 'a.id_application')
                                ->where('a.id_application', $application_id)
                                ->where('a.is_deleted', 0)
                                ->select(
                                    'a.*',
                                    'at.is_normal',
                                    'at.is_past',
                                    'at.is_advanced',
                                    'at.is_proportional',
                                    'at.is_season_special',
                                    'at.is_recover_vacation',
                                    'at.is_event',
                                    'u.birthday_n',
                                    'u.benefits_date',
                                    'u.payment_frec_id',
                                    'ap_st.applications_st_name',
                                    'u_rev.full_name_ui as revisor',
                                )
                                ->first();

        $oUser = EmployeeVacationUtils::getEmployeeDataForMyVacation($oApplication->user_id);
        $lEvents = EmployeeVacationUtils::getEmployeeEvents($oApplication->user_id);
        
        return json_encode(['success' => true, 
            'oApplication' => $oApplication, 
            'tot_vacation_remaining' => $oUser->tot_vacation_remaining,
            'lEvents' => $lEvents,
        ]);
    }

    public static function getApplicationIncidence($application_id){
        $oApplication = \DB::table('applications as ap')
                                ->leftJoin('cat_incidence_tps as tp', 'tp.id_incidence_tp', '=', 'ap.type_incident_id')                    
                                ->leftJoin('cat_incidence_cls as cl', 'cl.id_incidence_cl', '=', 'tp.incidence_cl_id')
                                ->leftJoin('applications_vs_types as at', 'at.application_id', '=', 'ap.id_application')
                                ->leftJoin('users as u_rev', 'u_rev.id', '=', 'ap.user_apr_rej_id')
                                ->where('id_application', $application_id)
                                ->select(
                                    'ap.*',
                                    'at.is_normal',
                                    'at.is_past',
                                    'at.is_season_special',
                                    'tp.id_incidence_tp',
                                    'tp.incidence_tp_name',
                                    'cl.id_incidence_cl',
                                    'cl.incidence_cl_name',
                                    'u_rev.full_name_ui as revisor',
                                )
                                ->first();
        $lEvents = EmployeeVacationUtils::getEmployeeEvents(delegationUtils::getIdUser());

        return json_encode(['success' => true, 'oApplication' => $oApplication, 'lEvents' => $lEvents]);
    }

    public static function getApplicationPermission($application_id){
        $oPermission = permissionsUtils::getPermission($application_id);
            
        $schedule = \DB::table('users as u')
                    ->join('schedule_template as st', 'st.id', '=', 'u.schedule_template_id')
                    ->join('schedule_day as sd', 'sd.schedule_template_id', '=', 'st.id')
                    ->where('u.id', $oPermission->user_id)
                    ->where('sd.is_working', 1)
                    ->where('sd.is_deleted', 0)
                    ->select(
                        'st.name',
                        'sd.day_name',
                        'sd.day_num',
                        \DB::raw("DATE_FORMAT(sd.entry, '%H:%i') as entry"),
                        \DB::raw("DATE_FORMAT(sd.departure, '%H:%i') as departure")
                    )
                    ->get();

        foreach($schedule as $sc){
            $sc->entry = Carbon::parse($sc->entry)->format('g:i A');
            $sc->departure = Carbon::parse($sc->departure)->format('g:i A');
        }

        $permission = "";
        if(count($schedule) > 0){
            if($oPermission->type_permission_id == SysConst::PERMISO_ENTRADA){
                $permission = Carbon::parse($schedule[0]->entry)->addMinutes($oPermission->minutes)->format('g:i A');
            }else if($oPermission->type_permission_id == SysConst::PERMISO_SALIDA){
                $permission = Carbon::parse($schedule[0]->departure)->subMinutes($oPermission->minutes)->format('g:i A');
            }
        }

        if($oPermission->type_permission_id == SysConst::PERMISO_INTERMEDIO){
            $permission = new \stdClass;
            $permission->inter_out = Carbon::parse($oPermission->intermediate_out)->format('g:i A');
            $permission->inter_ret = Carbon::parse($oPermission->intermediate_return)->format('g:i A');

            $hora1_24 = Carbon::parse($oPermission->intermediate_out)->format('H:i');
            $hora2_24 = Carbon::parse($oPermission->intermediate_return)->format('H:i');

            // Calcular la diferencia en minutos
            $diferencia_minutos = Carbon::parse($hora1_24)->diffInMinutes(Carbon::parse($hora2_24));

            // Convertir la diferencia de vuelta a formato de 12 horas si es necesario
            $diferencia_horas = floor($diferencia_minutos / 60);
            $diferencia_minutos_restantes = $diferencia_minutos % 60;
            $oPermission->time = $diferencia_horas.' hrs. '.$diferencia_minutos_restantes.' minutos';
        }

        return json_encode(['success' => true, 'oApplication' => $oPermission, 'schedule' => $schedule, 'permission' => $permission]);
    }

    public static function getEmployee($user_id){
        $oUser = \DB::table('users as u')
                    ->leftJoin('users_vs_photos as up', 'up.user_id', '=', 'u.id')
                    ->where('u.id', $user_id)
                    ->select(
                        'u.*',
                        'up.photo_base64_n as photo64',
                    )
                    ->first();

        $from = Carbon::parse($oUser->benefits_date);
        $to = Carbon::today()->locale('es');

        $human = $to->diffForHumans($from, true, false, 6);

        $oUser->antiquity = $human;

        return $oUser;
    }
}
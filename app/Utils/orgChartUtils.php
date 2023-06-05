<?php namespace App\Utils;

use App\Models\Adm\OrgChartJob;

class OrgChartUtils {

    /**
     * Obtiene el org chart job de un usuario
     */
    public static function getUserOrgChartJob($id){

    }

    /**
     * Obtiene los org chart jobs directamente inferiores al org chart job
     */
    public static function getDirectChildsOrgChartJob($id){
        $group = OrgChartJob::find($id);
        $group->child = $group->children()->get();
        $arrayAreas = $group->getArrayChilds();
        return $arrayAreas;
    }

    /**
     * Obtiene todos los org chart jobs inferiores al org chart job
     */
    public static function getAllChildsOrgChartJob($id){
        $group = OrgChartJob::find($id);
        $group->child = $group->getChildrens();
        $arrayAreas = $group->getArrayChilds();
        return $arrayAreas;
    }

    /**
     * Obtiene todos los org chart jobs inferiores al org chart job que no sean boss
     */
    public static function getAllChildsOrgChartJobNoBoss($id){
        $group = OrgChartJob::find($id);
        $group->child = $group->getChildrensNoBoss();
        $arrayAreas = $group->getArrayChilds();
        return $arrayAreas;
    }

    /**
     * Obtiene el org chart jobs directamente superior al org chart job que sea boss
     */
    public static function getDirectFatherBossOrgChartJob($id){
        $oOrgChart = $group = OrgChartJob::find($id);
        $oOrgChart->parent = $oOrgChart->getParentsBoss();
        $arrayAreas = $group->getArrayParentsBoss();
        return $arrayAreas;
    }

    /**
     * Obtiene todos los org chart jobs superiores al org chart job que sean boss
     */
    public static function getAllFatherBossOrgChartJob($id){
        $oOrgChart = $group = OrgChartJob::find($id);
        $oOrgChart->parent = $oOrgChart->getAllParents();
        $arrayAreas = $group->getArrayParentsBoss();
        return $arrayAreas;
    }

    /**
     * Obtiene el org chart jobs directamente superior al org chart job
     */
    public static function getDirectFatherOrgChartJob($id){
        $oOrgChart = $group = OrgChartJob::find($id);
        $oOrgChart->parent = $oOrgChart->getParent()->get();
        $arrayAreas = $group->getArrayParents();
        return $arrayAreas;
    }

    /**
     * Obtiene el supervisor directamente superior existente al org chart job recibido
     */
    public static function getExistDirectSuperviserOrgChartJob($id){
        $arrOrgJobs = orgChartUtils::getAllFatherBossOrgChartJob($id);
        $superviser = null;
        $org_chart_id = $id;
        for($i = 0; $i < count($arrOrgJobs); $i++){
            $dirOrgJobs = orgChartUtils::getDirectFatherBossOrgChartJob($org_chart_id);
            $superviser = \DB::table('users')
                                ->where('is_delete', 0)
                                ->where('is_active', 1)
                                ->whereIn('org_chart_job_id', $dirOrgJobs)
                                ->first();
                                
            if(!is_null($superviser)){
                break;
            }
            $org_chart_id = $dirOrgJobs[0];
        }

        return $superviser;
    }

    /**
     * Obtiene todos los org chart jobs superiores al org chart job
     */
    public static function getAllFathersOrgChartJob($id){
        
    }

    /**
     * Obtiene los empleados directos
     */
    public static function getMyEmployees($id){
        
    }

    /**
     * Obtiene todos los encargados de area
     */
    public static function getAllManagers($arrExcept = []){
        $lOrgCharts = OrgChartJob::where('positions', 1)
                                ->where('is_deleted', 0)
                                ->pluck('id_org_chart_job');

        $lUsers = \DB::table('users')
                    ->whereIn('org_chart_job_id', $lOrgCharts)
                    ->whereNotIn('id', $arrExcept)
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->get();

        return $lUsers;
    }

    /**
     * Obtine todos los encargados de area solo por debajo del usuario
     */
    public static function getMyManagers($id){
        // $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($id);
        $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJob($id);

        $lOrgCharts = OrgChartJob::where('is_boss', 1)
                                ->where('is_deleted', 0)
                                ->whereIn('id_org_chart_job', $arrOrgJobs)
                                ->pluck('id_org_chart_job');

        $lUsers = \DB::table('users')
                    ->whereIn('org_chart_job_id', $lOrgCharts)
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->get();

        return $lUsers;
    }

    public static function getAllUsersByOrgChartJob($org_chart_id){
        $lUsers = \DB::table('users')
                    ->where('org_chart_job_id', $org_chart_id)
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->select(
                        'id',
                        'full_name_ui',
                        'institutional_mail',
                        )
                    ->get();

        return $lUsers;
    }
}
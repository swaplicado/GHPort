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
     * Obtiene el org chart jobs directamente superior al org chart job
     */
    public static function getDirectFatherOrgChartJob($id){
        $oOrgChart = $group = OrgChartJob::find($id);
        $oOrgChart->parent = $oOrgChart->getParent()->get();
        $arrayAreas = $group->getArrayParents();
        return $arrayAreas;
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

        $lOrgCharts = OrgChartJob::where('positions', 1)
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
}
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
}
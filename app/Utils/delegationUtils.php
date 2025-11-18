<?php namespace App\Utils;

use App\Constants\SysConst;
use App\Models\Adm\Delegation;
use App\Models\Adm\OrgChartJob;
use \App\User;
use Carbon\Carbon;
use \Session;

class delegationUtils {

    public static function getUsersDelegationByOrgChart($org_chart_id){
        $today = Carbon::now()->toDateString();

        $oOrgChart = OrgChartJob::find($org_chart_id);
        $users = User::where([['is_delete', 0], ['org_chart_job_id', $oOrgChart->id_org_chart_job]])
                                ->select(
                                        'id',
                                    )
                                ->get()
                                ->pluck('id')
                                ->toArray();

        $lDelegations = Delegation::whereIn('user_delegated_id', $users)
                                ->where('delegations.is_active', 1)
                                ->where('delegations.is_deleted', 0)
                                ->where('start_date', '<=', $today)
                                ->where('end_date', '>=', $today)
                                ->get();

        $arrDelegations = $lDelegations->map(function($item){
            return $item->user_delegation_id;
        })->toArray();

        $lUsers = User::whereIn('id', $arrDelegations)
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->select('*')
                    ->selectRaw('1 as is_delegation')
                    ->get();

        foreach($lUsers as $user){
            $oDelegation = $lDelegations->where('user_delegation_id', $user->id)->first();
            $delegated = User::find($oDelegation->user_delegated_id);
            $user->is_delegation = 1;
            $user->delegated = $delegated->full_name;
            $user->delegation_start_date = dateUtils::formatDate($oDelegation->start_date, 'dd-M-Y');
            $user->delegation_end_date = dateUtils::formatDate($oDelegation->end_date, 'dd-M-Y');
        }

        return $lUsers;
    }

    /**
     * Si esta en modo delegación regresa el id del usuario delegado,
     * si no, regresa el id del usuario en sesión
     */
    public static function getIdUser(){
        if(!(Session::get('is_delegation'))){
            return \Auth::user()->id;    
        }else{
            return Session::get('user_delegated_id');
        }
    }

    /**
     * Si esta en modo delegación regresa el rol_id del usuario delegado,
     * si no, regresa el rol_id del usuario en sesión
     */
    public static function getRolIdUser(){
        if(!Session::get('is_delegation')){
            return \Auth::user()->rol_id;    
        }else{
            $rol_id = User::where('id', Session::get('user_delegated_id'))
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->value('rol_id');
                        
            return $rol_id;
        }
    }

    /**
     * Si esta en modo delegación regresa el org_chart_job_id del usuario delegado,
     * si no, regresa el org_chart_job_id del usuario en sesión
     */
    public static function getOrgChartJobIdUser(){
        if(!Session::get('is_delegation')){
            if(\Auth::user()->rol_id == SysConst::ADMINISTRADOR){
                return SysConst::ORG_CHART_JOB_ID_TO_ADMIN;
            }else{
                return \Auth::user()->org_chart_job_id;    
            }

        }else{
            $org_chart_job_id = User::where('id', Session::get('user_delegated_id'))
                        ->where('is_delete', 0)
                        // ->where('is_active', 1)
                        ->value('org_chart_job_id');

            return $org_chart_job_id;
        }
    }

    /**
     * Si esta en modo delegación regresa el objeto user del usuario delegado,
     * si no, regresa el objeto user del usuario en sesión
     */
    public static function getUser(){
        if(!Session::get('is_delegation')){
            return \Auth::user();    
        }else{
            $oUser = User::find(Session::get('user_delegated_id'));
            return $oUser;
        }
    }

    /**
     * Si esta en modo delegación ingresa al mètodo de authorizedRol del usuario delegado,
     * si no, ingresa al mètodo de authorizedRol del usuario en sesión
     */
    public static function getAutorizeRolUser($rol){
        if(!Session::get('is_delegation')){
            //\Log::error('Auth: ');
            //\Log::error(\Auth::user());
            \Auth::user()->authorizedRole($rol);
        }else{
            $oUser = User::find(Session::get('user_delegated_id'));
            //\Log::error('oUser: ');
            //\Log::error($oUser);
            $oUser->authorizedRole($rol);
        }
    }

    /**
     * Si esta en modo delegación ingresa al mètodo de IsMyEmployee del usuario delegado,
     * si no, ingresa al mètodo de IsMyEmployee del usuario en sesión
     */
    public static function getIsMyEmployeeUser($id_employee){
        if(!Session::get('is_delegation')){
            \Auth::user()->IsMyEmployee($id_employee);
        }else{
            $oUser = User::find(Session::get('user_delegated_id'));
            $oUser->IsMyEmployee($id_employee);
        }
    }

    /**
     * Si esta en modo delegación regresa el full_name del usuario delegado,
     * si no, regresa el full_name del usuario en sesión
     */
    public static function getFullNameUser(){
        if(!Session::get('is_delegation')){
            return \Auth::user()->full_name;
        }else{
            $full_name = User::where('id', Session::get('user_delegated_id'))
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->value('full_name');
            return $full_name;
        }
    }

    /**
     * Si esta en modo delegación regresa el username del usuario delegado,
     * si no, regresa el username del usuario en sesión
     */
    public static function getUsernameUser(){
        if(!Session::get('is_delegation')){
            return \Auth::user()->username;
        }else{
            $username = User::where('id', Session::get('user_delegated_id'))
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->value('username');
            return $username;
        }
    }

    /**
     * Si esta en modo delegación regresa el job_id del usuario delegado,
     * si no, regresa el job_id del usuario en sesión
     */
    public static function getJobIdUser(){
        if(!Session::get('is_delegation')){
            return \Auth::user()->job_id;
        }else{
            $username = User::where('id', Session::get('user_delegated_id'))
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->value('job_id');
            return $username;
        }
    }

    public static function getRolUser(){
        if(!Session::get('is_delegation')){
            return \Auth::user()->rol_id;
        }else{
            $rol_id = User::where('id', Session::get('user_delegated_id'))
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->value('rol_id');
            return $rol_id;
        }
    }

    public static function getFullNameUI(){
        if(!Session::get('is_delegation')){
            return \Auth::user()->full_name_ui;
        }else{
            $full_name_ui = User::where('id', Session::get('user_delegated_id'))
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->value('full_name_ui');
            return $full_name_ui;
        }
    }
}
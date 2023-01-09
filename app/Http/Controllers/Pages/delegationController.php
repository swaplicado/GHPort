<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adm\Delegation;
use App\Utils\OrgChartUtils;

class delegationController extends Controller
{
    public function index(){
        if(\Auth::user()->rol_id == 4){
            $lDelegations = Delegation::where('is_deleted', 0)->get();
            $is_admin = true;
        }else{
            $lDelegations = Delegation::where('user_delegation_id', \Auth::user()->id)
                                    ->orWhere('user_delegated_id', \Auth::user()->id)
                                    ->where('is_deleted', 0)
                                    ->get();

            $is_admin = false;
        }

        $lUsers = OrgChartUtils::getAllManagers();

        return view('delegations.delegations')->with('lUsers', $lUsers)
                                            ->with('lDelegations', $lDelegations)
                                            ->with('is_admin', $is_admin);
    }
}

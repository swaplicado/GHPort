<?php

namespace App\Http\Controllers\Pages;
use App\Http\Controllers\Controller;
use DB;

class configReportIncsController extends Controller
{
    public function index()
    {
        $users = DB::table('users as u')
            ->leftJoin('users_config_reports as ucr', 'u.id', '=', 'ucr.user_id')
            ->where('u.rol_id', 2)
            ->select('u.id as id', 'u.username as username','u.full_name as full_name', 'u.is_active as is_active', 'ucr.id_config_report as id_config_report', 'ucr.all_employees as all_employees', 'ucr.organization_level_id as organization_level_id')
            ->get();

        //dd($users);
        
        return view('configReportIncs.configReportsIncs')->with('users',$users);
    }
}
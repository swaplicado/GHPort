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
            ->get();

        //dd($users);
        
        return view('configReportIncs.configReportsIncs')->with('users',$users);
    }
}
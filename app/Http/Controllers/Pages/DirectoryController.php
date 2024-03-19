<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Sys\SyncController;
use Illuminate\Http\Request;
use App\Utils\usersInSystemUtils;

class DirectoryController extends Controller
{
    public function index(){
        $orgChartJob = \DB::table('org_chart_jobs')->where('id_org_chart_job', \Auth::user()->org_chart_job_id)->first();
        
        if( is_null($orgChartJob)){
            return redirect()->route('home');
        }
        if(!$orgChartJob->is_office){
            return redirect()->route('home');
        }

        $lUser = \DB::table('users as us')
                        ->join('org_chart_jobs as ocj', 'ocj.id_org_chart_job', '=', 'us.org_chart_job_id')
                        ->where('us.is_delete', 0)
                        ->where('us.is_active', 1)
                        ->where('us.employee_num', '!=' , 0)
                        ->select('us.id AS idUser',
                            'us.full_name AS fullname',
                            'us.email AS personalMail',
                            'us.institutional_mail AS institutionalMail',
                            'us.email_directory AS directoryMail',
                            'us.tel_area AS telArea', 
                            'us.tel_num AS telNum',
                            'us.tel_ext AS telExt',
                            'ocj.job_name_ui AS nameOrg',
                            'ocj.id_org_chart_job'
                            )
                        ->get();
        
        $lUser = usersInSystemUtils::FilterUsersInSystem($lUser, 'idUser');
        $lUser = usersInSystemUtils::FilterUsersByOfficeOrgChartJob($lUser, 'id_org_chart_job');
        return view('directory.index')->with('lUser', $lUser);

    }
}

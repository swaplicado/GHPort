<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\delegationUtils;
use App\Utils\orgChartUtils;
use App\Utils\EmployeeVacationUtils;

class usersInEventsController extends Controller
{
    public function index(){
        $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        $arrOrgJobs = orgChartUtils::getDirectChildsOrgChartJob($org_chart_job_id);
        $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
        $lEmployees = $lEmployees->pluck('id')->toArray();
        $lGroups = \DB::table('groups_assigns as g')
                        ->whereIn('user_id_n', $lEmployees)
                        ->get()
                        ->pluck('group_id_n')
                        ->toArray();

        $lEventsByEmp = \DB::table('cat_events as e')
                        ->join('events_assigns as ea', 'ea.event_id', '=', 'e.id_event')
                        ->join('users as u', 'ea.user_id_n', '=', 'u.id')
                        ->join('org_chart_jobs as o', 'u.org_chart_job_id', '=', 'o.id_org_chart_job')
                        ->whereIn('user_id_n', $lEmployees)
                        ->select(
                                'e.id_event',
                                'e.name as event',
                                'ea.id_event_assign',
                                'u.id as id_user',
                                'u.full_name as employee',
                                'o.job_name as area'
                            )
                        ->get();

        $lEventsByGroup = \DB::table('cat_events as e')
                            ->join('events_assigns as ea', 'ea.event_id', '=', 'e.id_event')
                            ->join('groups_assigns as g', 'ea.group_id_n', '=', 'g.group_id_n')
                            ->join('users as u', 'g.user_id_n', '=', 'u.id')
                            ->join('org_chart_jobs as o', 'u.org_chart_job_id', '=', 'o.id_org_chart_job')
                            ->whereIn('g.group_id_n', $lGroups)
                            ->whereIn('u.id', $lEmployees)
                            ->select(
                                'e.id_event',
                                'e.name as event',
                                'ea.id_event_assign',
                                'u.id as id_user',
                                'u.full_name as employee',
                                'o.job_name as area'
                            )
                            ->get();

        $lEvents = $lEventsByEmp->merge($lEventsByGroup);

        return view('usersInEvents.usersInEvents')->with('lEvents', $lEvents);
    }
}

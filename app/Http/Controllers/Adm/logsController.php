<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class logsController extends Controller
{
    public function index(){
        $lLogs = [
            ['name' => 'plan de vacaciones (días)', 'route' => route('bitacoras_VacationPlanDaysLogs')],
            ['name' => 'vacaciones colaboradores', 'route' => route('bitacoras_VacationUsersLogs')],
            ['name' => 'admisión colaboradores', 'route' => route('bitacoras_AdmissionUserLogs')],
            ['name' => 'solicitudes de vacaciones', 'route' => route('bitacoras_ApplicationLogs')],
        ];

        return view('logs.main_logs')->with('lLogs', $lLogs);
    }

    public function indexVacationPlanDaysLogs(){
        $logs = \DB::table('vacation_plan_days_logs as vpdl')
                    ->join('cat_vacation_plans as vp', 'vp.id_vacation_plan', '=', 'vpdl.vacations_plan_id')
                    ->join('users as u', 'u.id', '=', 'vpdl.created_by')
                    ->select('vpdl.*', 'vp.vacation_plan_name', 'u.full_name_ui')
                    ->orderBy('vpdl.updated_at', 'desc')
                    // ->orderBy('vpdl.vacations_plan_id', 'desc')
                    ->get();

        return view('logs.vacation_plan_days_logs')->with('logs', $logs);
    }

    public function indexVacationUsersLogs(){
        $logs = \DB::table('vacation_users_logs as vul')
                    ->join('users as u', 'vul.user_id', '=', 'u.id')
                    ->leftJoin('users as ucl', 'vul.closed_by_n', '=', 'ucl.id')
                    ->leftJoin('users as uexp', 'vul.expired_by_n', '=', 'uexp.id')
                    ->join('users as uc', 'vul.created_by', '=', 'uc.id')
                    ->join('users as uu', 'vul.created_by', '=', 'uu.id')
                    ->select(
                        'vul.*',
                        'u.full_name_ui',
                        'uc.full_name_ui as created_by_name',
                        'uu.full_name_ui as updated_by_name',
                        'ucl.full_name_ui as closed_by_name',
                        'uexp.full_name_ui as expired_by_name'
                    )
                    ->get();

        return view('logs.vacation_users_logs')->with('logs', $logs);
    }

    public function indexAdmissionUserLogs(){
        $logs = \DB::table('user_admission_logs as ual')
                    ->join('users as u', 'u.id', '=', 'ual.user_id')
                    ->where('ual.user_id', '!=', 1)
                    ->select('ual.*', 'u.full_name_ui')
                    ->get();

        return view('logs.admission_user_logs')->with('logs', $logs);
    }

    public function indexApplicationLogs(){
        $logs = \DB::table('applications_logs as al')
                        ->join('applications as ap', 'ap.id_application', '=', 'al.application_id')
                        ->join('users as u', 'u.id', '=', 'ap.user_id')
                        ->select(
                            'al.application_id',
                            'ap.*',
                            'u.full_name_ui as employee'
                        )
                        ->groupBy('application_id')
                        ->orderBy('al.updated_at', 'desc')
                        ->get();
// dd($logs);
        return view('logs.applications_logs')->with('logs', $logs);
    }

    public function getApplicationLogsData(Request $request){
        try {
            $applicationLogs = \DB::table('applications_logs as al')
                                    ->join('users as u', 'u.id', '=', 'al.created_by')
                                    ->join('sys_applications_sts as sts', 'sts.id_applications_st', '=', 'al.application_status_id')
                                    ->where('al.application_id', $request->application_id)
                                    ->select('al.*', 'u.full_name_ui as created_by_name', 'sts.applications_st_name as status')
                                    ->get();
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'applicationLogs' => $applicationLogs]);
    }
}

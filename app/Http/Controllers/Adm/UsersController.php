<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Carbon\Carbon;
use App\User;
use App\Models\Adm\Job;
use Illuminate\Support\Str;
class UsersController extends Controller
{
    private $lJobs;
    private $lOrgChartJobs;

    public function saveUsersFromJSON($lUsers)
    {
        $lGhPortUsers = User::pluck('id', 'external_id_n');

        $this->lJobs = Job::pluck('id_job', 'external_id_n');
        $this->lOrgChartJobs = \DB::table('ext_jobs_vs_org_chart_job')->get();
        
        foreach ($lUsers as $jUser) {
            try {
                if (isset($lGhPortUsers[$jUser->id_employee])) {
                    $id = $lGhPortUsers[$jUser->id_employee];
                    $this->updUser($jUser, $id);
                }
                else {
                    $this->insertUser($jUser);
                }
            }
            catch (\Throwable $th) {
                dd($th);
            }
        }
    }

    private function updUser($jUser, $id)
    {
        $currectDate = Carbon::now()->toDateString();
        $orgChartJob = $this->lOrgChartJobs->where('ext_job_id', $this->lJobs[$jUser->siie_job_id])->first();
        User::where('id', $id)
                    ->update(
                            [
                                'employee_num' => $jUser->num_employee,
                                'first_name' => $jUser->lastname1,
                                'last_name' => $jUser->lastname2,
                                'full_name' => $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname,
                                'full_name_ui' => $jUser->firstname.' - '.$jUser->num_employee,
                                'short_name' => $jUser->firstname,
                                'benefits_date' => $jUser->admission_date,
                                'vacation_date' => $jUser->admission_date,
                                'last_admission_date' => $jUser->admission_date,
                                'last_dismiss_date_n' => $jUser->leave_date,
                                'job_id' => $this->lJobs[$jUser->siie_job_id],
                                'org_chart_job_id' => !is_null($orgChartJob) ? $orgChartJob->org_chart_job_id_n : 1,
                                'vacation_plan_id' =>1,
                                'is_active' => $jUser->is_active,
                                'is_delete' => $jUser->is_deleted,

                            ]
                        );
    }

    private function insertUser($jUser)
    {
        if ((!$jUser->is_active) || $jUser->is_deleted) {
            return;
        }

        $name = str_replace([' LA ', ' DE ', ' LOS ', ' DEL ', ' LAS ', ' EL ', ], ' ', $jUser->firstname);
        $lastname1 = str_replace([' LA ', ' DE ', ' LOS ', ' DEL ', ' LAS ', ' EL ', ], ' ', $jUser->lastname1);
        $lastname2 = str_replace([' LA ', ' DE ', ' LOS ', ' DEL ', ' LAS ', ' EL ', ], ' ', $jUser->lastname2);
        // $usernameTmp = strtolower($jUser->num_employee.'.'.$jUser->lastname1.'.'.$jUser->lastname2);
        
        $names = explode(' ', $name);
        $lastname1s = explode(' ', $lastname1);
        $lastname2s = explode(' ', $lastname2);

        $usr = [];
        if (count($names) > 0 && count($lastname1s) > 0) {
            $usernameTmp = strtolower(str::slug($names[0]).'.'.str::slug($lastname1s[0]));
            $username = $this->getUserName($usernameTmp);
            $usr = User::where('username', $username)->first();
        }
        
        if ($usr != null) {
            if (count($names) > 1) {
                $usernameTmp = strtolower(str::slug($names[1]).'.'.str::slug($lastname1s[0]));
                $username = $this->getUserName($usernameTmp);
                $usr = User::where('username', $username)->first();
            }

            if ($usr != null) {
                if (count($lastname2s) > 0) {
                    $usernameTmp = strtolower(str::slug($names[0]).'.'.str::slug($lastname2s[0]));
                    $username = $this->getUserName($usernameTmp);
                    $usr = User::where('username', $username)->first();
                }
            }

            if ($usr != null) {
                $usernameTmp = strtolower($jUser->lastname1.'.'.$jUser->num_employee);
                $username = $this->getUserName($usernameTmp);
                $usr = User::where('username', $username)->first();

                if ($usr != null) {
                    return;
                }
            }
        }

        // $currectDate = Carbon::now()->toDateString();
        $orgChartJob = $this->lOrgChartJobs->where('ext_job_id', $this->lJobs[$jUser->siie_job_id])->first();
        $oUser = new User();

        $oUser->username = $username;
        $oUser->email = $jUser->email;
        $oUser->password = bcrypt($username);
        $oUser->employee_num = $jUser->num_employee;
        $oUser->first_name = $jUser->lastname1;
        $oUser->last_name = $jUser->lastname2;
        $oUser->full_name = $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname;
        $oUser->full_name_ui = $jUser->firstname.' - '.$jUser->num_employee;
        $oUser->short_name = $jUser->firstname;
        $oUser->benefits_date = $jUser->admission_date;
        $oUser->vacation_date = $jUser->admission_date;
        $oUser->last_admission_date = $jUser->admission_date;
        $oUser->last_dismiss_date_n = $jUser->leave_date;
        $oUser->current_hire_log_id = 1;
        $oUser->is_unionized = 0;
        $oUser->company_id = 1;
        // $oUser->department_id = 1;
        $oUser->job_id = $this->lJobs[$jUser->siie_job_id];
        $oUser->org_chart_job_id = !is_null($orgChartJob) ? $orgChartJob->org_chart_job_id_n : 1;
        $oUser->vacation_plan_id = 1;
        $oUser->is_active = $jUser->is_active;
        $oUser->external_id_n = $jUser->id_employee;
        $oUser->is_delete = $jUser->is_deleted;
        $oUser->created_by = 1;
        $oUser->updated_by = 1;

        $oUser->save();
    }

    private function getUserName($usernameTmp)
    {
        $username = str_replace(['ñ', 'Ñ'], 'n', $usernameTmp);
        $username = str_replace('-', '', $username);
        $username = str_replace(' ', '', $username);

        return $username;
    }
}
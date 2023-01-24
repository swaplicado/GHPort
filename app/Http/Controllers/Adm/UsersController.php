<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Carbon\Carbon;
use App\User;
use App\Models\Adm\Job;
use App\Models\Adm\UserAdmissionLog;
use Illuminate\Support\Str;
use App\Models\Adm\UsersPhotos;

class UsersController extends Controller
{
    private $lJobs;
    private $lOrgChartJobs;
    private $lCompanies;

    public function saveUsersFromJSON($lUsers)
    {
        $lGhPortUsers = User::pluck('id', 'external_id_n');
        $this->lCompanies = \DB::table('ext_company')
                        ->where('is_deleted', 0)
                        ->get();

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
            }
        }
    }

    private function updUser($jUser, $id)
    {
        $config = \App\Utils\Configuration::getConfigurations();
        $tokName = ['firstname' => $jUser->firstname, 'lastname1' => $jUser->lastname1, 'lastname2' => $jUser->lastname2, 'num_employee' => $jUser->num_employee];
        $currectDate = Carbon::now()->toDateString();
        $orgChartJob = $this->lOrgChartJobs->where('ext_job_id', $this->lJobs[$jUser->siie_job_id])->first();
        $comp = !is_null($this->lCompanies->where('external_id', $jUser->company_id)->first()) ? $this->lCompanies->where('external_id', $jUser->company_id)->first()->id_company : 6;
        $full_name_ui = '';
        $ui = $config->name_ui;
        for($i = 0; $i < sizeof($ui); $i++){
            $full_name_ui = $full_name_ui.$tokName[$ui[$i]];
            if($i < (sizeof($ui) - 1)){
                $full_name_ui = $full_name_ui.' - ';
            }
        }
        User::where('id', $id)
                    ->update(
                            [
                                'employee_num' => $jUser->num_employee,
                                'first_name' => $jUser->lastname1,
                                'last_name' => $jUser->lastname2,
                                'full_name' => $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname,
                                'full_name_ui' => $full_name_ui,
                                'short_name' => $jUser->firstname,
                                'benefits_date' => $jUser->benefit_date,
                                'vacation_date' => $jUser->admission_date,
                                'payment_frec_id' => $jUser->way_pay,
                                'last_admission_date' => $jUser->admission_date,
                                'last_dismiss_date_n' => $jUser->leave_date,
                                'job_id' => $this->lJobs[$jUser->siie_job_id],
                                'vacation_plan_id' => 1,
                                'is_active' => $jUser->is_active,
                                'is_delete' => $jUser->is_deleted,
                                'company_id' => $comp
                            ]
                        );

        $oUser = User::find($id);
        if(!is_null($oUser)){
            $this->updateUserAdmissionLog($oUser);
        }

        $oUsersPhotos = UsersPhotos::where('user_id', $oUser->id)->first();

        if(is_null($oUsersPhotos)){
            $oUsersPhotos = new UsersPhotos();
            $oUsersPhotos->user_id = $oUser->id;
            $oUsersPhotos->photo_base64_n = null;
            $oUsersPhotos->is_deleted = 0;
            $oUsersPhotos->created_by = 1;
            $oUsersPhotos->updated_by = 1;
            $oUsersPhotos->save();
        }
    }

    private function insertUser($jUser)
    {
        if ((!$jUser->is_active) || $jUser->is_deleted) {
            return;
        }
        $config = \App\Utils\Configuration::getConfigurations();
        $tokName = ['firstname' => $jUser->firstname, 'lastname1' => $jUser->lastname1, 'lastname2' => $jUser->lastname2, 'num_employee' => $jUser->num_employee];
        $name = str_replace([' LA ', ' DE ', ' LOS ', ' DEL ', ' LAS ', ' EL ', ], ' ', $jUser->firstname);
        $lastname1 = str_replace([' LA ', ' DE ', ' LOS ', ' DEL ', ' LAS ', ' EL ', ], ' ', $jUser->lastname1);
        $lastname2 = str_replace([' LA ', ' DE ', ' LOS ', ' DEL ', ' LAS ', ' EL ', ], ' ', $jUser->lastname2);
        
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

        $full_name_ui = '';
        $ui = $config->name_ui;
        for($i = 0; $i < sizeof($ui); $i++){
            $full_name_ui = $full_name_ui.$tokName[$ui[$i]];
            if($i < (sizeof($ui) - 1)){
                $full_name_ui = $full_name_ui.' - ';
            }
        }

        $orgChartJob = $this->lOrgChartJobs->where('ext_job_id', $this->lJobs[$jUser->siie_job_id])->first();
        $comp = !is_null($this->lCompanies->where('external_id', $jUser->company_id)->first()) ? $this->lCompanies->where('external_id', $jUser->company_id)->first()->id_company : 6;
        $oUser = new User();

        $oUser->username = $username;
        $oUser->email = $jUser->email;
        $oUser->password = bcrypt($username);
        $oUser->employee_num = $jUser->num_employee;
        $oUser->first_name = $jUser->lastname1;
        $oUser->last_name = $jUser->lastname2;
        $oUser->full_name = $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname;
        $oUser->full_name_ui = $full_name_ui;
        $oUser->short_name = $jUser->firstname;
        $oUser->benefits_date = $jUser->benefit_date;
        $oUser->vacation_date = $jUser->admission_date;
        $oUser->last_admission_date = $jUser->admission_date;
        $oUser->last_dismiss_date_n = $jUser->leave_date;
        $oUser->current_hire_log_id = 1;
        $oUser->is_unionized = 0;
        $oUser->company_id = $comp;
        $oUser->job_id = $this->lJobs[$jUser->siie_job_id];
        $oUser->org_chart_job_id = !is_null($orgChartJob) ? $orgChartJob->org_chart_job_id_n : 1;
        $oUser->vacation_plan_id = 1;
        $oUser->payment_frec_id = $jUser->way_pay;
        $oUser->is_active = $jUser->is_active;
        $oUser->external_id_n = $jUser->id_employee;
        $oUser->is_delete = $jUser->is_deleted;
        $oUser->created_by = 1;
        $oUser->updated_by = 1;

        $oUser->save();

        $this->setUserAdmissionLog($oUser);

        $oUsersPhotos = new UsersPhotos();
        $oUsersPhotos->user_id = $oUser->id;
        $oUsersPhotos->photo_base64_n = null;
        $oUsersPhotos->is_deleted = 0;
        $oUsersPhotos->created_by = 1;
        $oUsersPhotos->updated_by = 1;
        $oUsersPhotos->save();
    }

    private function getUserName($usernameTmp)
    {
        $username = str_replace(['ñ', 'Ñ'], 'n', $usernameTmp);
        $username = str_replace('-', '', $username);
        $username = str_replace(' ', '', $username);

        return $username;
    }

    public function setUserAdmissionLog($oUser){
        $oLog = new UserAdmissionLog();

        $oLog->user_id = $oUser->id;
        $oLog->user_admission_date = $oUser->last_admission_date;
        $oLog->user_leave_date = $oUser->last_dismiss_date_n;
        $oLog->admission_count = 1;
        $oLog->save();
    }

    public function updateUserAdmissionLog($oUser){
        $oLog = UserAdmissionLog::where('user_id', $oUser->id)->first();

        $oLog->user_admission_date = $oUser->last_admission_date;
        $oLog->user_leave_date = $oUser->last_dismiss_date_n;
        $oLog->admission_count = 1;
        $oLog->update();
    }

    // public function dumySetUserAdmissionLog(){
    //     try {
    //         $lUsers = User::where([['is_active', 1],['is_delete', 0]])->get();
    
    //         foreach($lUsers as $oUser){
    //             $oLog = new UserAdmissionLog();
        
    //             $oLog->user_id = $oUser->id;
    //             $oLog->user_admission_date = $oUser->last_admission_date;
    //             $oLog->user_leave_date = $oUser->last_dismiss_date_n;
    //             $oLog->admission_count = 1;
    //             $oLog->save();
    //         }
    //     } catch (\Throwable $th) {
    //         echo $th;
    //     }
    // }
}
<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\Adm\ScheduleTemplate;
use Exception;
use Illuminate\Http\Request;
use \App\Utils\delegationUtils;
use Carbon\Carbon;
use App\User;
use App\Models\Adm\Job;
use App\Models\Adm\UserAdmissionLog;
use Illuminate\Support\Str;
use App\Models\Adm\UsersPhotos;
use App\Constants\SysConst;
use App\Utils\GlobalUsersUtils;
use App\Utils\programmedTaskUtils;
use App\Utils\usersInSystemUtils;

class UsersController extends Controller
{
    private $lJobs;
    private $lOrgChartJobs;
    private $lCompanies;
    private $loginUnivData = null;
    private $loginCAPData = null;
    private $loginEvalData = null;

    public function saveUsersFromJSON($lUsers)
    {
        ini_set('max_execution_time', 300);
        $lGhPortUsers = User::pluck('id', 'external_id_n');
        $this->lCompanies = \DB::table('ext_company')
                        ->where('is_deleted', 0)
                        ->get();

        $this->lJobs = Job::pluck('id_job', 'external_id_n');
        $this->lOrgChartJobs = \DB::table('ext_jobs_vs_org_chart_job')->get();
        $flag = '';
        try {
            $this->loginUnivData = globalUsersUtils::loginToUniv();
            globalUsersUtils::syncJobsAndDepartmentsUniv();
            $this->loginCAPData = globalUsersUtils::loginToCAP();
            $this->loginEvalData = globalUsersUtils::loginToEval();
            $output = new \Symfony\Component\Console\Output\ConsoleOutput();
            foreach ($lUsers as $jUser) {
                try {
                    \DB::beginTransaction();
                    \DB::connection('mysqlGlobalUsers')->beginTransaction();
                    if (isset($lGhPortUsers[$jUser->id_employee])) {
                        $id = $lGhPortUsers[$jUser->id_employee];
                        $flag = 'upd';
                        $this->updUser($jUser, $id);
                    }
                    else {
                        $flag = 'insert';
                        $output->writeln("Inserción de " . $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname);
                        $this->insertUser($jUser);
                    }
                    \DB::connection('mysqlGlobalUsers')->commit();
                    \DB::commit();
                } catch (\Throwable $th) { 
                    $output->writeln("catch del for principal " . $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname . " " . $th->getMessage());
                    \Log::error($th);
                    \DB::connection('mysqlGlobalUsers')->rollBack();
                    \DB::rollBack();
                }
            }

            globalUsersUtils::setupDeptsAndHeadersUniv();
        }catch (\Throwable $th) {
            $output->writeln("catch principal " . $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname . " " . $th->getMessage());
            \Log::error('login: ');
            \Log::error($th);
            return false;
        }
        return true;
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
                                'email' => $jUser->email,
                                'institutional_mail' => $jUser->emailEmp == null ? $jUser->email : $jUser->emailEmp,
                                'email_directory' => $jUser->emailEmp,
                                'tel_area' => $jUser->telArea,
                                'tel_num' => $jUser->telNum,
                                'tel_ext' => $jUser->ext,
                                'benefits_date' => $jUser->benefit_date,
                                'vacation_date' => $jUser->admission_date,
                                'payment_frec_id' => $jUser->way_pay,
                                'last_admission_date' => $jUser->admission_date,
                                'last_dismiss_date_n' => $jUser->leave_date,
                                'birthday_n' => $jUser->dt_bir,
                                'job_id' => $this->lJobs[$jUser->siie_job_id],
                                'vacation_plan_id' => 7,
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

        if(!is_null($oUser)){
            $result = json_decode(GlobalUsersUtils::findGlobalUser(null, $oUser->full_name, $oUser->external_id, $oUser->employee_num));
            
            if($result->success){
                $globalUser = $result->globalUser;
                if(!is_null($globalUser)){
                    //updateGlobal
                    $oUser->pass = $oUser->password;
                    $oUser->external_id = $oUser->external_id_n;
                    GlobalUsersUtils::globalUpdateFromSystem($oUser, SysConst::SYSTEM_PGH, $this->loginUnivData, $this->loginCAPData, $this->loginEvalData);
                }
            }else{
                throw new Exception($result->message);
            }
        }
    }

    private function insertUser($jUser)
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        if ((!$jUser->is_active) || $jUser->is_deleted) {
            $output->writeln("Usuario inactivo o eliminado " . $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname);
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
                if (count($names) > 1) {
                    $usernameTmp = strtolower(str::slug($names[0]).'.'.str::slug($names[0][0]).'.'.str::slug($lastname1s[0]));
                    $username = $this->getUserName($usernameTmp);
                    $usr = User::where('username', $username)->first();
                }

                if ($usr != null) {
                    if (count($lastname2s) > 0) {
                        $usernameTmp = strtolower(str::slug($names[0]).'.'.str::slug($lastname1s[0]).'.'.str::slug($lastname2s[0][0]));
                        $username = $this->getUserName($usernameTmp);
                        $usr = User::where('username', $username)->first();
                    }
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
        $oUser->institutional_mail = $jUser->emailEmp == null ? $jUser->email : $jUser->emailEmp;
        $oUser->email_directory = $jUser->emailEmp;
        $oUser->tel_area = $jUser->telArea;
        $oUser->tel_num  = $jUser->telNum;
        $oUser->tel_ext = $jUser->ext;
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
        $oUser->birthday_n = $jUser->dt_bir;
        $oUser->current_hire_log_id = 1;
        $oUser->is_unionized = 0;
        $oUser->company_id = $comp;
        $oUser->job_id = $this->lJobs[$jUser->siie_job_id];
        $oUser->org_chart_job_id = !is_null($orgChartJob) ? $orgChartJob->org_chart_job_id_n : 1;
        $oUser->vacation_plan_id = 7;
        $oUser->payment_frec_id = $jUser->way_pay;
        $oUser->is_active = $jUser->is_active;
        $oUser->external_id_n = $jUser->id_employee;
        $oUser->is_delete = $jUser->is_deleted;
        $oUser->created_by = 1;
        $oUser->updated_by = 1;
        $oUser->can_change_dp = 1;
        $oUser->can_change_cv = 1;

        $oUser->save();

        $this->setUserAdmissionLog($oUser);

        $oUsersPhotos = new UsersPhotos();
        $oUsersPhotos->user_id = $oUser->id;
        $oUsersPhotos->photo_base64_n = null;
        $oUsersPhotos->is_deleted = 0;
        $oUsersPhotos->created_by = 1;
        $oUsersPhotos->updated_by = 1;
        $oUsersPhotos->save();
        $output->writeln("Insertado en Gh " . $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname);
        $result = json_decode(GlobalUsersUtils::findGlobalUser(null, $oUser->full_name, $oUser->external_id, $oUser->employee_num));
        $globalUser = $result->globalUser;
        if(is_null($globalUser)){
            //global user
            try {
                $output->writeln("Insertar en global " . $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname);
                $globalUser = GlobalUsersUtils::insertNewGlobalUser(SysConst::SYSTEM_PGH, $oUser->id, $oUser->username, $oUser->password, $oUser->email, $oUser->full_name, $oUser->external_id_n, $oUser->employee_num, $oUser->is_active, $oUser->is_delete);
            } catch (\Throwable $th) {
                $output->writeln("catch de la insercion en users global " . $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname . " " . $th->getMessage());
                \DB::beginTransaction();
                    try {
                        $oUser->id_user_system = $oUser->id;
                        $oUser->id_global_user = null;
                        programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_INSERT_USERGLOBAL, $oUser, SysConst::SYSTEM_PGH);
                    } catch (\Throwable $th) {
                        \Log::error($th);
                        \DB::rollBack();
                    }
                \DB::commit();
                return;
            }
            $userUnivId = GlobalUsersUtils::getSystemUserId($globalUser->id_global_user, SysConst::SYSTEM_UNIVAETH);
            $oUser->id_user_system = $userUnivId;
            //univ
            try {
                $output->writeln("Sincronizar con la universidad " . $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname);
                $oUser->siie_job_id = $jUser->siie_job_id;
                $resultUniv = globalUsersUtils::syncUserToUniv($this->loginUnivData->token_type, $this->loginUnivData->access_token, $oUser, SysConst::USERGLOBAL_INSERT);
                if($resultUniv->status != 'success'){
                    throw new Exception($resultUniv->message);
                }
                $userUniv = $resultUniv->data;
                $output->writeln("Sincronizado con la universidad " . $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname);
                try {
                    $output->writeln("Buscar usuario global, segunda pasada " . $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname);
                    $resultFind = json_decode(globalUsersUtils::findSystemUser($globalUser->id_global_user, SysConst::SYSTEM_UNIVAETH, $userUniv->id));
                    if(!$resultFind->success){
                        //insertar en user_vs_system
                        GlobalUsersUtils::insertSystemUser($globalUser->id_global_user, SysConst::SYSTEM_UNIVAETH, $userUniv->id);
                    }
                } catch (\Throwable $th) {
                    $output->writeln("catch de usuario global, segunda pasada " . $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname);
                    $oUser->id_user_system = $userUniv->id;
                    $oUser->id_global_user = $globalUser->id_global_user;
                    programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_INSERT_SYSTEM_VS_USER, $oUser, null);
                }
            } catch (\Throwable $th) {
                $output->writeln("catch de la sincro con la uni " . $jUser->lastname1.' '.$jUser->lastname2.', '.$jUser->firstname . " " . $th->getMessage());
                \DB::beginTransaction();
                    try {
                        $oUser->id_user_system = $globalUser->id_global_user;
                        $oUser->id_global_user = null;
                        programmedTaskUtils::createTaskToUsersGlobal(SysConst::TASK_INSERT_UNIV, $oUser, null);
                    } catch (\Throwable $th) {
                        \Log::error($th);
                        \DB::rollBack();
                    }
                \DB::commit();
            }
        }
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

    public function index(){
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]);
        $lUser = \DB::table('users as us')
                        ->join('cat_vacation_plans as vp', 'vp.id_vacation_plan', '=', 'us.vacation_plan_id')
                        ->join('org_chart_jobs as ocj', 'ocj.id_org_chart_job', '=', 'us.org_chart_job_id')
                        ->leftJoin('schedule_template as st', 'st.id', '=', 'us.schedule_template_id')
                        ->where('us.is_delete', 0)
                        ->select(
                            'us.id AS idUser',
                            'us.username AS username',
                            'us.full_name AS fullname',
                            'us.email AS mail',
                            'us.employee_num AS numUser',
                            'us.benefits_date AS benDate',
                            'ocj.job_code AS nameOrg',
                            'ocj.id_org_chart_job AS idOrg',
                            'vp.vacation_plan_name AS nameVp',
                            'vp.id_vacation_plan AS idPlan',
                            'us.rol_id AS idRol',
                            'us.is_active AS active',
                            'st.id as schedule_id',
                            'st.name as schedule_name',
                            'us.can_change_dp',
                            'us.can_change_cv'
                        )
                        ->get();

        $orgChart = \DB::table('org_chart_jobs')
                        ->where('is_deleted', 0)
                        ->get();

        $planVacations = \DB::table('cat_vacation_plans')
                        ->where('is_deleted', 0)
                        ->get();

        $schedules = \DB::table('schedule_template as t')
                        // ->join('schedule_day as a', 'a.schedule_template_id', '=', 't.id')
                        ->where('t.is_deleted', 0)
                        // ->where('a.is_deleted', 0)
                        ->get();

        $roles = \DB::table('adm_rol as ar')
                    ->where('ar.is_delete', 0)
                    ->get();
        
        $lUser = usersInSystemUtils::FilterUsersInSystem($lUser, 'idUser');
        return view('Adm.indexUser')->with('lUser', $lUser)->with('lOrgChart',$orgChart)->with('lPlan',$planVacations)->with('schedules', $schedules)->with('lRol',$roles);
    }

    public function update(Request $request){
        delegationUtils::getAutorizeRolUser([SysConst::ADMINISTRADOR, SysConst::GH]); 
        
        if($request->passRess == 0){
            try {
                $vacController = new VacationPlansController();
                
                $us = User::findOrFail($request->idUser);

                $vacController->saveVacationUserLog($us);
                $vacController->generateVacationUser($us->id, $request->selVac);
                
                $us->username = $request->username;
                $us->email = $request->mail;
                $us->is_active = $request->active; 
                $us->vacation_plan_id = $request->selVac;
                $us->org_chart_job_id = $request->selArea;
                $us->rol_id = $request->selRol;
                $us->schedule_template_id = $request->selSchedule;
                $us->updated_by = \Auth::user()->id;
                $us->can_change_dp = $request->can_change_dp;
                $us->can_change_cv = $request->can_change_cv;
                $us->update();

                $us->pass = $us->password;
                $us->external_id = $us->external_id_n;
                $us->email = $us->institutional_mail;
                GlobalUsersUtils::globalUpdateFromSystem($us, SysConst::SYSTEM_PGH);
            } catch (\Throwable $th) {
                \DB::rollback();
                return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema']);
            }    
        }else{
            try {
                $vacController = new VacationPlansController();
                
                $us = User::findOrFail($request->idUser);

                $vacController->saveVacationUserLog($us);
                $vacController->generateVacationUser($us->id, $request->selVac);
                
                $us->username = $request->username;
                $us->email = $request->mail;
                $us->password = \Hash::make($request->username);
                $us->is_active = $request->active; 
                $us->vacation_plan_id = $request->selVac;
                $us->org_chart_job_id = $request->selArea;
                $us->rol_id = $request->selRol;
                $us->schedule_template_id = $request->selSchedule;
                $us->updated_by = \Auth::user()->id;
                $us->changed_password = 0;
                $us->can_change_dp = $request->can_change_dp;
                $us->can_change_cv = $request->can_change_cv;
                $us->update();

                $us->pass = $us->password;
                $us->external_id = $us->external_id_n;
                $us->email = $us->institutional_mail;
                GlobalUsersUtils::globalUpdateFromSystem($us, SysConst::SYSTEM_PGH);
            } catch (\Throwable $th) {
                \DB::rollback();
                return json_encode(['success' => false, 'message' => 'Error al crear el registro']);
            }
        }
        
        $lUser = \DB::table('users as us')
                        ->join('cat_vacation_plans as vp', 'vp.id_vacation_plan', '=', 'us.vacation_plan_id')
                        ->join('org_chart_jobs as ocj', 'ocj.id_org_chart_job', '=', 'us.org_chart_job_id')
                        ->leftJoin('schedule_template as st', 'st.id', '=', 'us.schedule_template_id')
                        ->where('us.is_delete',0)
                        ->select(
                            'us.id AS idUser', 
                            'us.schedule_template_id as schedule_id', 
                            'st.name as schedule_name', 
                            'us.username AS username', 
                            'us.full_name AS fullname', 
                            'us.email AS mail', 
                            'us.employee_num AS numUser', 
                            'us.benefits_date AS benDate', 
                            'ocj.job_code AS nameOrg',
                            'vp.vacation_plan_name AS nameVp',
                            'us.is_active AS active',
                            'ocj.id_org_chart_job AS idOrg',
                            'vp.id_vacation_plan AS idPlan',
                            'us.rol_id AS idRol',
                            'us.can_change_dp',
                            'us.can_change_cv'
                            )
                        ->get();

        $lUser = usersInSystemUtils::FilterUsersInSystem($lUser, 'idUser');
        return json_encode(['success' => true, 'message' => 'Registro actualizado con exitó', 'lUser' => $lUser]);
    }
}
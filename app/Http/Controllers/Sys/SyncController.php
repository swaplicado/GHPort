<?php

namespace App\Http\Controllers\Sys;

use App\Http\Controllers\Controller;
use App\Models\Vacations\Application;
use App\Utils\GlobalUsersUtils;
use Illuminate\Http\Request;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use App\Http\Controllers\Adm\DepartmentsController;
use App\Http\Controllers\Adm\JobsController;
use App\Http\Controllers\Adm\UsersController;
use App\Http\Controllers\Adm\holidaysController;
use App\Http\Controllers\Adm\VacationsController;
use App\Http\Controllers\Adm\UsersPhotosController;
use App\Models\Adm\UsersPhotos;
use App\User;
use App\Constants\SysConst;
use App\SReports\Vacations_report;
use App\Models\GlobalUsers\globalUser;
use App\Models\GlobalUsers\userVsSystem;

class SyncController extends Controller
{
    public static function toSynchronize($withRedirect = true)
    {
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            if(!$config->syncExecution){
                \App\Utils\Configuration::setConfiguration('syncExecution', true);
                $synchronized = SyncController::synchronizeWithERP($config->lastSyncDateTime);
                $photos = SyncController::SyncPhotos();
                // $synchronized = true;
        
                if($synchronized){
                     $newDate = Carbon::now();
                     $newDate->subMinutes(10);
            
                     \App\Utils\Configuration::setConfiguration('lastSyncDateTime', $newDate->toDateTimeString());
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th);
        }

        \App\Utils\Configuration::setConfiguration('syncExecution', false);
        return $synchronized;
    }

    public static function synchronizeWithERP($lastSyncDate = "")
    {
        $config = \App\Utils\Configuration::getConfigurations();
        // $lastSyncDate = Carbon::parse($lastSyncDate)->subDays($config->pastSyncDays)->startOfDay()->toDateTimeString();

        $client = new Client([
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
        ]);

        try {
            
            $response = $client->request('GET', 'getInfoERP/' . $lastSyncDate);
            $jsonString = $response->getBody()->getContents();
            $data = json_decode($jsonString);

            $deptCont = new DepartmentsController();
            $resDep = $deptCont->saveDeptsFromJSON($data->departments);
            if(!$resDep){
                return false;
            }
            
            $jobCont = new JobsController();
            $resJob = $jobCont->saveJobsFromJSON($data->positions);
            if(!$resJob){
                return false;
            }
            $jobCont->insertJobVsOrgJob();
            
            $usrCont = new UsersController();
            $resUs = $usrCont->saveUsersFromJSON($data->employees);
            if(!$resUs){
                return false;
            }

            // $usrCont->dumySetUserAdmissionLog();

            $holidaysCont = new holidaysController();
            $resHol = $holidaysCont->saveHolidaysFromJSON($data->holidays);

            if(!$resHol){
                return false;
            }

            $lCompany = \DB::table('ext_company')
                            ->select(
                                'id_company',
                                'external_id',
                                'company_db_name'
                            )
                            ->get();

            foreach($lCompany as $company){
                $company->last_sync_date = \DB::table('synchronize_log as s')
                                                ->join('users as u', 'u.id', '=', 's.user_id')
                                                ->where('u.external_id_n', '!=', null)
                                                ->where('company_id', $company->id_company)
                                                ->select(
                                                    'u.external_id_n',
                                                    's.last_sync'
                                                )->max(\DB::raw('DATE_FORMAT(s.last_sync, "%Y-%m-%d %H:%i:%s")'));

                $company->last_sync_date = Carbon::parse($company->last_sync_date)->subDays($config->pastSyncDays)->startOfDay()->toDateTimeString();
            }

            $jsonUsers = json_encode($lCompany->toArray());

            $responseVac = $client->request('GET', 'getPGHData/' . $jsonUsers);
            $jsonStringVac = $responseVac->getBody()->getContents();
            $dataVac = json_decode($jsonStringVac);

            $vacCont = new VacationsController();
            $resVac = $vacCont->saveVacFromJSON($dataVac->vacations);

            if(!$resVac){
                return false;
            }

            $resCons = SyncController::setVacationsConsumed();
            if(!$resCons){
                return false;
            }
        }
        catch (\Throwable $th) {
            return false;
        }
        
        return true;
    }

    public static function SyncPhotos(){
      $config = \App\Utils\Configuration::getConfigurations();
      $lUsersPhotos = UsersPhotos::where('photo_base64_n', null)
                                  ->where('is_deleted', 0)
                                  ->pluck('user_id');
                                  
      if(count($lUsersPhotos) > 0){
            $lUsers = User::whereIn('id', $lUsersPhotos)
                        ->where('is_delete', 0)
                        ->where('is_active', 1)
                        ->pluck('external_id_n')
                        ->toArray();

                        $lUsers = json_encode($lUsers);
            try {
                $client = new Client([
                    'base_uri' => $config->urlSync,
                    'timeout' => 30.0,
                ]);
        
                $response = $client->request('GET', 'getEmployeesPhoto/' . $lUsers);
                $jsonString = $response->getBody()->getContents();
                $data = json_decode($jsonString);

                $UsersPhotosController = new UsersPhotosController();
                $UsersPhotosController->saveUsersPhotosFromJSON($data);
            } catch (\Throwable $th) {
                return false;
            }
        }
    }

    public static function setVacationsConsumed(){
        try {
            $lVacations = Application::where('type_incident_id', SysConst::TYPE_VACACIONES)
                            ->where('is_deleted', 0)
                            ->where('request_status_id', SysConst::APPLICATION_APROBADO)
                            ->get();
    
            foreach($lVacations as $oVac){
                $oVac->request_status_id = SysConst::APPLICATION_CONSUMIDO;
                $oVac->update();
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return false;
        }
        return true;
    }

    public function reSync(){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            if(!$config->syncExecution){
                \App\Utils\Configuration::setConfiguration('syncExecution', true);
                $synchronized = SyncController::synchronizeWithERP($config->lastSyncDateTime);
                $photos = SyncController::SyncPhotos();
                // $synchronized = true;
        
                if($synchronized){
                     $newDate = Carbon::now();
                     $newDate->subMinutes(10);
            
                     \App\Utils\Configuration::setConfiguration('lastSyncDateTime', $newDate->toDateTimeString());
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th);
        }

        // GlobalUsersUtils::syncExternalWithGlobalUsers();

        return redirect()->back();
    }

    public function syncOnlyUsers(){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $lastSyncDate = $config->lastSyncDateTime;
            $lastSyncDate = Carbon::parse($lastSyncDate)->subDays($config->pastSyncDays)->startOfDay()->toDateTimeString();
            $client = new Client([
                'base_uri' => $config->urlSync,
                'timeout' => 30.0,
            ]);

            $response = $client->request('GET', 'getInfoERP/' . $lastSyncDate);
            $jsonString = $response->getBody()->getContents();
            $data = json_decode($jsonString);

            $usrCont = new UsersController();
            $resUs = $usrCont->saveUsersFromJSON($data->employees);
            if(!$resUs){
                return false;
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return false;
        }

        return true;
    }

    public function initialSync(){
        $config = \App\Utils\Configuration::getConfigurations();
        $client = new Client([
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
        ]);

        try {
            
            $response = $client->request('GET', 'getInfoERP'  );
            $jsonString = $response->getBody()->getContents();
            $data = json_decode($jsonString);
            
            $usrCont = new UsersController();
            $resUs = $usrCont->saveUsersFromJSON($data->employees);
            if(!$resUs){
                return false;
            }

        }
        catch (\Throwable $th) {
            return false;
        }
        
        return true; 
    }

    /**
     * Metodo que se encarga de llenar y actualizar los usuarios de la base de datos global
     * a partir de los usuarios de la base de datos especificada en el archivo de configuracion "getUsersToFillGlobalUsersFromConnection"
     */
    public static function fillGlobalUsers(){
        try {
            $config = \App\Utils\Configuration::getConfigurations();
            $connection = $config->getUsersToFillGlobalUsersFromConnection;
            $fillFromSystemId = $config->fillGlobalUsersFromSystemId;
            $fieldToFindUserInUniv = $config->fieldToFindUserInUniv;
            $lUsersToFindInUniv = [];
            $fieldToFindUserInCAP = $config->fieldToFindUserInCAP;
            $lUsersToFindInCAP = [];
            $fieldToFindUserInEval = $config->fieldToFindUserInEval;
            $lUsersToFindInEval = [];
    
//-------------------------SINCRONIZACION CON PGH-------------------------
            //lista de usuarios de pgh
            $lUsers = \DB::table('users')
                            ->where('id', '!=', 1)
                            ->where('is_delete', 0)
                            ->where('is_active', 1)
                            ->where('external_id_n', '!=', null)
                            ->select(
                                'id',
                                'username',
                                'password',
                                'institutional_mail as email',
                                'full_name',
                                'external_id_n as external_id',
                                'employee_num'
                            )
                            ->get();
    
            \DB::connection('mysqlGlobalUsers')->beginTransaction();
            foreach($lUsers as $user){
                $globalUser = null;
                //Se revisa si ya existe el usuario de pgh en global users
                $result = json_decode(GlobalUsersUtils::findGlobalUser(null, $user->full_name, $user->external_id, $user->employee_num));
                if($result->success){
                    $globalUser = $result->globalUser;
                }else{
                    \Log::error($result->message);
                    continue;
                }
                
                //si el usuario existe en global users se hace update si no se inserta
                if(is_null($globalUser)){
                    $globalUser = GlobalUsersUtils::insertNewGlobalUser(SysConst::SYSTEM_PGH, $user->id, $user->username, $user->password, $user->email, $user->full_name, $user->external_id, $user->employee_num);
                }else{
                    GlobalUsersUtils::updateGlobalUser($globalUser->id_global_user, $user->username, $user->password, $user->email, $user->full_name, $user->external_id, $user->employee_num);
                }

                //se añade a la lista de usuarios a buscar en la univ
                $lUsersToFindInUniv[] = [
                    'pgh_id' => $user->id,
                    'username' => $fieldToFindUserInUniv->username ? $user->username : null,
                    'full_name' => $fieldToFindUserInUniv->full_name ? $user->full_name : null,
                    'external_id' => $fieldToFindUserInUniv->external_id ? $user->external_id : null,
                    'employee_num' => $fieldToFindUserInUniv->employee_num ? $user->employee_num : null,
                ];

                //se añade a la lista de usuarios a buscar en el cap
                $lUsersToFindInCAP[] = [
                    'pgh_id' => $user->id,
                    'username' => $fieldToFindUserInCAP->username ? $user->username : null,
                    'full_name' => $fieldToFindUserInCAP->full_name ? $user->full_name : null,
                    'external_id' => $fieldToFindUserInCAP->external_id ? $user->external_id : null,
                    'employee_num' => $fieldToFindUserInCAP->employee_num ? $user->employee_num : null,
                ];

                //se añade a la lista de usuarios a buscar en el eval
                $lUsersToFindInEval[] = [
                    'pgh_id' => $user->id,
                    'username' => $fieldToFindUserInEval->username ? $user->username : null,
                    'full_name' => $fieldToFindUserInEval->full_name ? $user->full_name : null,
                    'external_id' => $fieldToFindUserInEval->external_id ? $user->external_id : null,
                    'employee_num' => $fieldToFindUserInEval->employee_num ? $user->employee_num : null,
                ];
            }
            
//-------------------------SINCRONIZACION CON UNIV-------------------------
            //Login a la universidad virtual
            $loginUnivData = globalUsersUtils::loginToUniv();
            if($loginUnivData->status == 'success'){
                //se envia la lista de usuarios a buscar a la universidad virtual y obtenemos el resultado
                $data = globalUsersUtils::getListUsersFromUnivAeth($loginUnivData->token_type, $loginUnivData->access_token, $lUsersToFindInUniv);
                if($data->status != 'error'){
                    //se recorre la lista de usuarios obtenidos de univ
                    foreach($data->data as $dataUser){
                        if($dataUser->status != 'error'){
                            if(!is_null($dataUser->user)){
                                $userUniv = $dataUser->user;
                                $globalUser = null;
                                //Se revisa si ya existe el usuario de univ en global users
                                $result = json_decode(GlobalUsersUtils::findGlobalUser(null, $userUniv->full_name, $userUniv->external_id, $userUniv->num_employee));
                                if($result->success){
                                    $globalUser = $result->globalUser;
                                }else{
                                    \Log::error($result->message);
                                    continue;
                                }
                                
                                if(!is_null($globalUser)){
                                    //si el usuario existe en global users se busca en usuario vs system
                                    $result = json_decode(GlobalUsersUtils::findSystemUser($globalUser->id_global_user, SysConst::SYSTEM_UNIVAETH, $userUniv->id));
                                    $userSystem = null;
                                    if($result->success){
                                        $userSystem = $result->userSystem;
                                    }
                                    //si el usuario no existe en users vs system se inserta
                                    if(is_null($userSystem)){
                                        GlobalUsersUtils::insertSystemUser($globalUser->id_global_user, SysConst::SYSTEM_UNIVAETH, $userUniv->id);
                                    }
                                }
                            }
                        }
                    }
                }
            }

//-------------------------SINCRONIZACION CON CAP-------------------------
            //Login a CAP
            $loginCAPData = globalUsersUtils::loginToCAP();
            if(isset($loginCAPData->access_token)){ //este se valida asi porque es lo que cap regresa (si le movia luego se descomponia algo :p)
                //se envia la lista de usuarios a buscar a la universidad virtual y obtenemos el resultado
                $data = globalUsersUtils::getListUsersFromCAP($loginCAPData->token_type, $loginCAPData->access_token, $lUsersToFindInCAP);
                if($data->status != 'error'){
                    //se recorre la lista de usuarios obtenidos de univ
                    foreach($data->data as $dataUser){
                        if($dataUser->status != 'error'){
                            if(!is_null($dataUser->user)){
                                $userCAP = $dataUser->user;
                                $globalUser = null;
                                //Se revisa si ya existe el usuario de univ en global users
                                $result = json_decode(GlobalUsersUtils::findGlobalUser(null, $userCAP->name, $userCAP->external_id, $userCAP->num_employee));
                                if($result->success){
                                    $globalUser = $result->globalUser;
                                }else{
                                    \Log::error($result->message);
                                    continue;
                                }

                                if(!is_null($globalUser)){
                                    //si el usuario existe en global users se busca en usuario vs system
                                    $result = json_decode(GlobalUsersUtils::findSystemUser($globalUser->id_global_user, SysConst::SYSTEM_CAP, $userCAP->id));
                                    $userSystem = null;
                                    if($result->success){
                                        $userSystem = $result->userSystem;
                                    }
                                    //si el usuario no existe en users vs system se inserta
                                    if(is_null($userSystem)){
                                        GlobalUsersUtils::insertSystemUser($globalUser->id_global_user, SysConst::SYSTEM_CAP, $userCAP->id);
                                    }
                                }
                            }
                        }
                    }
                }
            }

//-------------------------SINCRONIZACION CON EVALUACION-------------------------
            //Login a evaluacion
            $loginEvalData = globalUsersUtils::loginToEval();
            if($loginEvalData->status == 'success'){
                //se envia la lista de usuarios a buscar a evaluacion desempeño y obtenemos el resultado
                $data = globalUsersUtils::getListUsersFromEval($loginEvalData->token_type, $loginEvalData->access_token, $lUsersToFindInEval);
                if($data->status != 'error'){
                    //se recorre la lista de usuarios obtenidos de eval
                    foreach($data->data as $dataUser){
                        if($dataUser->status != 'error'){
                            if(!is_null($dataUser->user)){
                                $userEval = $dataUser->user;
                                $globalUser = null;
                                //Se revisa si ya existe el usuario de eval en global users
                                $result = json_decode(GlobalUsersUtils::findGlobalUser(null, $userEval->full_name, null, $userEval->num_employee));
                                if($result->success){
                                    $globalUser = $result->globalUser;
                                }else{
                                    \Log::error($result->message);
                                    continue;
                                }
                                if(!is_null($globalUser)){
                                    //si el usuario existe en global users se busca en usuario vs system
                                    $result = json_decode(GlobalUsersUtils::findSystemUser($globalUser->id_global_user, SysConst::SYSTEM_EVALUACIONDESEMPENO, $userEval->id));
                                    $userSystem = null;
                                    if($result->success){
                                        $userSystem = $result->userSystem;
                                    }
                                    //si el usuario no existe en users vs system se inserta
                                    if(is_null($userSystem)){
                                        GlobalUsersUtils::insertSystemUser($globalUser->id_global_user, SysConst::SYSTEM_EVALUACIONDESEMPENO, $userEval->id);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            \DB::connection('mysqlGlobalUsers')->commit();
        } catch (\Throwable $th) {
            \Log::error($th);
            \DB::connection('mysqlGlobalUsers')->rollBack();
            return false;
        }

        return true;
    }

    /**
     * Metodo para obtener la lista de usuarios de la tabla global users a partir de la fecha recibida.
     * 1.- Sincroniza los usuarios
     * 2.- Obtiene la lista de usuarios a partir de la fecha recibida
     * 3.- Regresa un array con los string json de los usuarios obtenidos* 
     * @param mixed $fromDateTime formato "YYYY-MM-DD HH:mm:ss" -> "2024-01-01 00:00:00"
     */
    public function getUsersFromGU(Request $request){
        try {
            $fromDateTime = $request->fromDateTime;
            $config = \App\Utils\Configuration::getConfigurations();
            if(!$config->syncExecution){
                \App\Utils\Configuration::setConfiguration('syncExecution', true);
                $synchronized = SyncController::synchronizeWithERP($config->lastSyncDateTime);
                $photos = SyncController::SyncPhotos();
                // $synchronized = true;
    
                if($synchronized){
                    $newDate = Carbon::now();
                    $newDate->subMinutes(10);
            
                    \App\Utils\Configuration::setConfiguration('lastSyncDateTime', $newDate->toDateTimeString());
                }
            }

            $lUsers = \DB::table('users as u')
                        ->join('ext_jobs as j', 'u.job_id', '=', 'j.id_job')
                        ->join('ext_departments as d', 'd.id_department', '=', 'j.department_id')
                        ->join('globalusers.users_vs_systems', 'u.id', '=', 'globalusers.users_vs_systems.user_system_id')
                        ->where('u.is_active', 1)
                        ->where('u.is_delete', 0)
                        ->where('globalusers.users_vs_systems.system_id', 5)
                        ->select(
                            'u.username',
                            'u.institutional_mail as email',
                            'u.full_name',
                            'u.employee_num',
                            'u.is_active',
                            'globalusers.users_vs_systems.global_user_id as id_global_user',
                            'j.id_job',
                            'j.job_name',
                            'd.id_department',
                            'd.department_name'
                        )
                        ->get()
                        ->toArray();

            \App\Utils\Configuration::setConfiguration('syncExecution', false);

            return response()->json([
                'status_code ' => 200,
                'status' => 'success',
                'message' => "Se sincronizaron los usuarios correctamente",
                'lUsers' => $lUsers
                ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            \Log::error($th);
            \App\Utils\Configuration::setConfiguration('syncExecution', false);
            return response()->json([
                'status_code ' => 500,
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}

<?php

namespace App\Http\Controllers\Sys;

use App\Http\Controllers\Controller;
use App\Models\Vacations\Application;
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

class SyncController extends Controller
{
    public static function toSynchronize($withRedirect = true)
    {
        $config = \App\Utils\Configuration::getConfigurations();
        $synchronized = SyncController::synchronizeWithERP($config->lastSyncDateTime);
        $photos = SyncController::SyncPhotos();
        // $synchronized = true;

        if($synchronized){
             $newDate = Carbon::now();
             $newDate->subMinutes(10);
    
             \App\Utils\Configuration::setConfiguration('lastSyncDateTime', $newDate->toDateTimeString());
        }

        return $synchronized;
    }

    public static function synchronizeWithERP($lastSyncDate = "")
    {
        $config = \App\Utils\Configuration::getConfigurations();
        $lastSyncDate = Carbon::parse($lastSyncDate)->subDays($config->pastSyncDays)->startOfDay()->toDateTimeString();

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
            // $jobCont->insertJobVsOrgJob();
            
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
        $config = \App\Utils\Configuration::getConfigurations();
        $synchronized = SyncController::synchronizeWithERP($config->lastSyncDateTime);
        $photos = SyncController::SyncPhotos();
        $synchronized = true;

        if($synchronized){
             $newDate = Carbon::now();
             $newDate->subMinutes(10);
    
             \App\Utils\Configuration::setConfiguration('lastSyncDateTime', $newDate->toDateTimeString());
        }

        return redirect()->back();
    }
}

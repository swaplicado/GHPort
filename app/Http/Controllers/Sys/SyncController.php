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

class SyncController extends Controller
{
    public static function toSynchronize($withRedirect = true)
    {
        //$config = \App\Utils\Configuration::getConfigurations();
        //$synchronized = SyncController::synchronizeWithERP($config->lastSyncDateTime);
        //$photos = SyncController::SyncPhotos();
        $synchronized = true;

        //if($synchronized){
             //$newDate = Carbon::now();
             //$newDate->subMinutes(10);
    
             //\App\Utils\Configuration::setConfiguration('lastSyncDateTime', $newDate->toDateTimeString());
        //}

        return $synchronized;
    }

    public static function synchronizeWithERP($lastSyncDate = "")
    {
        $config = \App\Utils\Configuration::getConfigurations();
        $client = new Client([
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
        ]);

        try {
            
            $response = $client->request('GET', 'getInfoERP/' . $lastSyncDate);
            $jsonString = $response->getBody()->getContents();
            $data = json_decode($jsonString);

            $deptCont = new DepartmentsController();
            $deptCont->saveDeptsFromJSON($data->departments);
            
            $jobCont = new JobsController();
            $jobCont->saveJobsFromJSON($data->positions);
            // $jobCont->insertJobVsOrgJob();
            
            $usrCont = new UsersController();
            $usrCont->saveUsersFromJSON($data->employees);

            // $usrCont->dumySetUserAdmissionLog();

            $holidaysCont = new holidaysController();
            $holidaysCont->saveHolidaysFromJSON($data->holidays);

            $vacCont = new VacationsController();
            // $newJsonString = json_encode($data->vacations, JSON_PRETTY_PRINT);
            // file_put_contents(base_path('vac.json'), stripslashes($newJsonString));
            $vacCont->saveVacFromJSON($data->vacations);
            // $vacCont->dumySetVacationsUser();

            SyncController::setVacationsConsumed();
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
        }
    }
}

<?php

namespace App\Http\Controllers\Sys;

use App\Http\Controllers\Controller;
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

class SyncController extends Controller
{
    public static function toSynchronize($withRedirect = true)
    {
        $config = \App\Utils\Configuration::getConfigurations();
        $synchronized = SyncController::synchronizeWithERP($config->lastSyncDateTime);
        $photos = SyncController::SyncPhotos();
        // $synchronized = true;

        $newDate = Carbon::now();
        $newDate->subMinutes(10);

        \App\Utils\Configuration::setConfiguration('lastSyncDateTime', $newDate->toDateTimeString());

        return $synchronized;
    }

    public static function synchronizeWithERP($lastSyncDate = "")
    {
        $client = new Client([
            'base_uri' => '192.168.1.233:9001',
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
            $vacCont->saveVacFromJSON($data->vacations);
            // $vacCont->dumySetVacationsUser();
        }
        catch (\Throwable $th) {
            return false;
        }
        
        return true;
    }

    public static function SyncPhotos(){
      $lUsersPhotos = UsersPhotos::where('photo_base64_n', null)
                                  ->where('is_deleted', 0)
                                  ->pluck('id');
                                  
      if(count($lUsersPhotos) > 0){
          $lUsers = User::whereIn('id', $lUsersPhotos)
                      ->where('is_delete', 0)
                      ->where('is_active', 1)
                      ->pluck('external_id_n');


          try {
              $client = new Client([
                  'base_uri' => '192.168.1.233:9001',
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
}

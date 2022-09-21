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

class SyncController extends Controller
{
    public static function toSynchronize($withRedirect = true)
    {
        $config = \App\Utils\Configuration::getConfigurations();
        $synchronized = SyncController::synchronizeWithERP($config->lastSyncDateTime);

        $newDate = Carbon::now();
        $newDate->subMinutes(10);

        \App\Utils\Configuration::setConfiguration('lastSyncDateTime', $newDate->toDateTimeString());

        return $synchronized;
    }

    public static function synchronizeWithERP($lastSyncDate = "")
    {
        $client = new Client([
            'base_uri' => '192.168.1.233:9001',
            'timeout' => 10.0,
        ]);

        try {
            
            $response = $client->request('GET', 'getInfoERP/' . $lastSyncDate);
            $jsonString = $response->getBody()->getContents();
            $data = json_decode($jsonString);

            $deptCont = new DepartmentsController();
            $deptCont->saveDeptsFromJSON($data->departments);
            
            $jobCont = new JobsController();
            $jobCont->saveJobsFromJSON($data->positions);
            
            $usrCont = new UsersController();
            $usrCont->saveUsersFromJSON($data->employees);
            
            $deptCont->setSupDeptAndHeadUser($data->departments);
        }
        catch (\Throwable $th) {
            //throw $th;
            return false;
        }
        
        return true;
    }
}

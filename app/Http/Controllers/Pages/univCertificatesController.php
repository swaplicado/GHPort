<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use \App\Utils\delegationUtils;
use App\Utils\EmployeeVacationUtils;
use App\Utils\orgChartUtils;

class univCertificatesController extends Controller
{
    public function index(){
        $lChildAreas = orgChartUtils::getAllChildsToRevice(delegationUtils::getOrgChartJobIdUser());
        $lEmployees = EmployeeVacationUtils::getlEmployees($lChildAreas);

        return view('univCertificates.univCertificates')->with('lEmployees', $lEmployees);
    }

    public function getCuadrants(Request $request){
        try {
            $lEmployeesCuadrants = $request->lEmployeesToCuadrants;

            $config = \App\Utils\Configuration::getConfigurations();
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ];
            
            $client = new Client([
                'base_uri' => $config->urlUniv,
                'timeout' => 30.0,
                'headers' => $headers
            ]);

            $start_date = Carbon::now()->startOfYear()->toDateString();
            $end_date = Carbon::now()->endOfYear()->toDateString();

            $lEmployees = \DB::table('users')
                            ->whereIn('id', $lEmployeesCuadrants)
                            ->get();

            $external_ids = $lEmployees->pluck('external_id_n');

            $body = '{
                "start_date": "'.$start_date.'",
                "end_date": "'.$end_date.'",
                "lEmployees": "'.$external_ids.'"
            }';
            
            $request = new \GuzzleHttp\Psr7\Request('POST', 'getCuadrants', $headers, $body);
            $response = $client->sendAsync($request)->wait();
            $jsonString = $response->getBody()->getContents();

            $oResponse = json_decode($jsonString);

            if($oResponse->status != 'success'){
                return json_encode(['success' => false, 'message' => $oResponse->message, 'icon' => 'error']);
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lEmployeesCuadrants' => $oResponse->data, 'icon' => 'success']);
    }

    public function getCertificates(Request $request){
        try {
            $lAssignmentsCertificates = $request->AssignmentsToCertificates;

            $config = \App\Utils\Configuration::getConfigurations();
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ];
            
            $client = new Client([
                'base_uri' => $config->urlUniv,
                'timeout' => 30.0,
                'headers' => $headers
            ]);

            $sAssigment = json_encode($lAssignmentsCertificates);

            $body = '{
                "lAssigments": '.$sAssigment.'
            }';

            $request = new \GuzzleHttp\Psr7\Request('POST', 'getCertificates', $headers, $body);
            $response = $client->sendAsync($request)->wait();
            $jsonString = $response->getBody()->getContents();

            $oResponse = json_decode($jsonString);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lCertificates' => $oResponse->data, 'icon' => 'success']);
    }
}

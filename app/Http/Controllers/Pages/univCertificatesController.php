<?php

namespace App\Http\Controllers\Pages;

use App\Constants\SysConst;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use \App\Utils\delegationUtils;
use App\Utils\EmployeeVacationUtils;
use App\Utils\orgChartUtils;
use App\Utils\usersInSystemUtils;

// Definir el tipo de contenido como texto/html
header('Content-Type: text/html');

// Definir cabeceras de caché para evitar que el navegador almacene en caché la página
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

class univCertificatesController extends Controller
{
    public function index(){
        $lChildAreas = orgChartUtils::getAllChildsToRevice(delegationUtils::getOrgChartJobIdUser());
        $lEmployees = EmployeeVacationUtils::getlEmployees($lChildAreas)->sortBy('full_name');

        foreach($lEmployees as $employee){
            $employee->area = \DB::table('org_chart_jobs')
                                ->where('id_org_chart_job', $employee->org_chart_job_id)
                                ->value('job_name');
        }

        $roles = [
            'ADMIN' => SysConst::ADMINISTRADOR,
            'GH' => SysConst::GH,
            'JEFE' => SysConst::JEFE,
            'ESTANDAR' => SysConst::ESTANDAR,
        ];

        $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
        return view('univCertificates.univCertificates')->with('lEmployees', $lEmployees)
                                                        ->with('rol', delegationUtils::getRolIdUser())
                                                        ->with('roles', $roles)
                                                        ->with('oUser', delegationUtils::getUser());
    }

    public function getAllEmployees(){
        try {
            $lEmployees = \DB::table('users as u')
                            ->leftJoin('org_chart_jobs as ocj', 'u.org_chart_job_id', 'ocj.id_org_chart_job')
                            ->leftJoin('ext_jobs as j', 'j.id_job', '=', 'u.job_id')
                            ->leftJoin('ext_departments as d', 'd.id_department', '=', 'j.department_id')
                            ->where('is_active', 1)
                            ->where('is_delete', 0)
                            ->where('id','!=', 1)
                            ->select(
                                'u.id',
                                'u.full_name',
                                'u.employee_num',
                                'ocj.job_name as area',
                                'd.department_name_ui',
                                'j.job_name_ui',
                            )
                            ->orderBy('full_name')
                            ->get()
                            ->toArray();
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }

        $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
        return json_encode(['success' => true, 'lEmployees' => $lEmployees]);
    }

    public function getAllMyEmployees(){
        try {
            $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJob(delegationUtils::getOrgChartJobIdUser());
            $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);

            foreach($lEmployees as $employee){
                $employee->area = \DB::table('org_chart_jobs')
                                    ->where('id_org_chart_job', $employee->org_chart_job_id)
                                    ->value('job_name');
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        
        }

        $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
        return json_encode(['success' => true, 'lEmployees' => $lEmployees]);
    }

    public function getMyEmployees(){
        try {
            $lChildAreas = orgChartUtils::getAllChildsToRevice(delegationUtils::getOrgChartJobIdUser());
            $lEmployees = EmployeeVacationUtils::getlEmployees($lChildAreas);

            foreach($lEmployees as $employee){
                $employee->area = \DB::table('org_chart_jobs')
                                    ->where('id_org_chart_job', $employee->org_chart_job_id)
                                    ->value('job_name');
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);            
        }

        $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
        return json_encode(['success' => true, 'lEmployees' => $lEmployees]);
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
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'lEmployeesCuadrants' => $oResponse->data]);
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

            $zip = new \ZipArchive;
            $zipFile = 'archivos_temporales.zip';
            foreach($oResponse->data as $data){
                $arrPdf = json_decode($data);
                file_put_contents($arrPdf->employee.'.pdf', base64_decode($arrPdf->pdf));
                if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
                    // Agregar los archivos PDF al archivo zip
                    $zip->addFile($arrPdf->employee.'.pdf', $arrPdf->employee.'.pdf');
                }
                // Cerrar el archivo zip
                $zip->close();
                
                unlink($arrPdf->employee.'.pdf');
            }

            // Devolver el archivo zip como respuesta
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="archivos.zip"');
            readfile($zipFile);

            // Eliminar el archivo zip después de la descarga
            unlink($zipFile);

        } catch (\Throwable $th) {
            \Log::error($th);
            unlink($zipFile);
            return json_encode(['success' => false, 'message' => $th->getMessage().' por favor contacte con el administrador del sistema', 'icon' => 'error']);
        }
    }
}

<?php

namespace App\Http\Controllers\Pages;

use App\Constants\SysConst;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use \App\Utils\delegationUtils;
use App\Utils\EmployeeVacationUtils;
use App\Utils\orgChartUtils;
use Carbon\Carbon;
use App\Utils\usersInSystemUtils;

class sanctionsController extends Controller
{
    public static function getSanctions($lData, $type){
        $config = \App\Utils\Configuration::getConfigurations();
        $client = new Client([
            'base_uri' => $config->urlSync,
            'timeout' => 30.0,
        ]);

        // $body = [
        //     'lData' => $lData,
        // ];

        $sBody = json_encode($lData);

        if($type == SysConst::ACTA){
            $response = $client->request('GET', 'getAdmRecInfo/' . $sBody);
        }else if($type == SysConst::SANCION){
            $response = $client->request('GET', 'getBreachData/' . $sBody);
        }
        $jsonString = $response->getBody()->getContents();
        $data = json_decode($jsonString);

        $dataEmp = collect($data->breach);

        $arrData = [];
        if($type == SysConst::ACTA){
            $lEmp = $dataEmp->filter(function ($item) {
                            return !empty($item->admRec);
                        });

            foreach ($lEmp as $emp) {
                $offender = \DB::table('users')
                                ->where('external_id_n', $emp->id_emp)
                                ->first();
                foreach ($emp->admRec as $admRec) {
                    $arrData[] = [ 
                                    'employee_id' => $emp->id_emp,
                                    'num' => $admRec->num,
                                    'startDate' => Carbon::parse($admRec->recDtSta)->toDateString(),
                                    'endDate' => Carbon::parse($admRec->recDtEnd)->toDateString(),
                                    'title' => $admRec->breachAbstract,
                                    'description' => $admRec->breachDescrip,
                                    'offender' => $offender->full_name,
                                ];
                }
            }
        }else if($type == SysConst::SANCION){
            $lEmp = $dataEmp->filter(function ($item) {
                            return !empty($item->breach);
                        });

            foreach ($lEmp as $emp) {
                $offender = \DB::table('users')
                                ->where('external_id_n', $emp->id_emp)
                                ->first();
                foreach ($emp->breach as $breach) {
                    $arrData[] = [ 
                                    'employee_id' => $emp->id_emp,
                                    'num' => $breach->num,
                                    'startDate' => Carbon::parse($breach->breachTs)->toDateString(),
                                    'endDate' => Carbon::parse($breach->breachTs)->toDateString(),
                                    'title' => $breach->breachAbstract,
                                    'description' => $breach->breachDescrip,
                                    'offender' => $offender->full_name,
                                ];
                }
            }
        }

        return $arrData;
    }

    public function getAllEmployees(Request $request){
        try {
            if(delegationUtils::getRolIdUser() == SysConst::GH){
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
                                    'u.company_id',
                                    'u.external_id_n',
                                    'ocj.job_name as area',
                                    'd.department_name_ui',
                                    'j.job_name_ui',
                                )
                                ->orderBy('full_name')
                                ->get()
                                ->toArray();
            }else{
                $arrOrgJobs = orgChartUtils::getAllChildsOrgChartJob(delegationUtils::getOrgChartJobIdUser());
                $lEmployees = EmployeeVacationUtils::getlEmployees($arrOrgJobs);
            }

            $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
            $arrEmployes = self::makeArraylEmployees($lEmployees);
            $lSanctions = self::getSanctions($arrEmployes, $request->type);
            if(count($lSanctions) > 0){
                $lSanctions = collect($lSanctions)->sortByDesc('startDate');
                $lSanctions = $lSanctions->values()->all();
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lSanctions' => $lSanctions]);
    }

    public function getMyEmployees(Request $request){
        try {
            $lChildAreas = orgChartUtils::getAllChildsToRevice(delegationUtils::getOrgChartJobIdUser());
            $lEmployees = EmployeeVacationUtils::getlEmployees($lChildAreas);
            $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
            $arrEmployes = self::makeArraylEmployees($lEmployees);
            $lSanctions = self::getSanctions($arrEmployes, $request->type);
            if(count($lSanctions) > 0){
                $lSanctions = collect($lSanctions)->sortByDesc('startDate');
                $lSanctions = $lSanctions->values()->all();
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);            
        }

        return json_encode(['success' => true, 'lSanctions' => $lSanctions]);
    }

    public function getMySanction(Request $request){
        try {
            $oUser = delegationUtils::getUser();
            $lEmployees = [$oUser];
            $lData = self::makeArraylEmployees($lEmployees);
            $lSanctions = self::getSanctions($lData, $request->type);
            if(count($lSanctions) > 0){
                $lSanctions = collect($lSanctions)->sortByDesc('startDate');
                $lSanctions = $lSanctions->values()->all();
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => $th->getMessage(), 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lSanctions' => $lSanctions]);
    }

    public static function makeArraylEmployees($lEmployees){
        $olEmployees = collect($lEmployees);
        foreach ($olEmployees as $emp) {
            $emp->ext_company_id = \DB::table('ext_company')->where('id_company', $emp->company_id)->value('external_id');
        }
        $lCompany = $olEmployees->pluck('ext_company_id')->unique();

        $lEmployeesArray = [];
        foreach($lCompany as $company){
            $lEmployeesArray[] = ['company' => $company, 'lEmployees' => $olEmployees->where('ext_company_id', $company)->pluck('external_id_n')->toArray()]; 
        }

        return $lEmployeesArray;
    }

    public function indexAdministrativeMinutes(){
        $oUser = delegationUtils::getUser();
        if($oUser->rol_id != SysConst::ESTANDAR){
            $lChildAreas = orgChartUtils::getAllChildsToRevice(delegationUtils::getOrgChartJobIdUser());
            $lEmployees = EmployeeVacationUtils::getlEmployees($lChildAreas);
        }else{
            $lEmployees = [$oUser];
        }
        $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
        $lData = self::makeArraylEmployees($lEmployees);
        $lSanctions = self::getSanctions($lData, SysConst::ACTA);
        if(count($lSanctions) > 0){
            $lSanctions = collect($lSanctions)->sortByDesc('startDate');
            $lSanctions = $lSanctions->values()->all();
        }

        $lTypes = [
            'ACTA' => SysConst::ACTA,
            'SANCION' => SysConst::SANCION,
        ];

        $lRoles = [
            'ESTANDAR' => SysConst::ESTANDAR,
            'GH' => SysConst::GH,
            'JEFE' => SysConst::JEFE,
        ];

        // $lSanctions = [];

        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->endOfMonth()->toDateString();

        return view('sanctions.administrativeMinutes')->with('lSanctions', $lSanctions)
                                                        ->with('oUser', $oUser)
                                                        ->with('type', SysConst::ACTA)
                                                        ->with('lTypes', $lTypes)
                                                        ->with('lRoles', $lRoles)
                                                        ->with('startDate', $startDate)
                                                        ->with('endDate', $endDate);
    }

    public function indexSanctions(){
        $oUser = delegationUtils::getUser();
        if($oUser->rol_id != SysConst::ESTANDAR){
            $lChildAreas = orgChartUtils::getAllChildsToRevice(delegationUtils::getOrgChartJobIdUser());
            $lEmployees = EmployeeVacationUtils::getlEmployees($lChildAreas);
        }else{
            $lEmployees = [$oUser];
        }
        $lEmployees = usersInSystemUtils::FilterUsersInSystem($lEmployees, 'id');
        $lData = self::makeArraylEmployees($lEmployees);
        $lSanctions = self::getSanctions($lData, SysConst::SANCION);
        if(count($lSanctions) > 0){
            $lSanctions = collect($lSanctions)->sortByDesc('startDate');
            $lSanctions = $lSanctions->values()->all();
        }

        $lTypes = [
            'ACTA' => SysConst::ACTA,
            'SANCION' => SysConst::SANCION,
        ];

        $lRoles = [
            'ESTANDAR' => SysConst::ESTANDAR,
            'GH' => SysConst::GH,
            'JEFE' => SysConst::JEFE,
        ];

        // $lSanctions = [];

        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->endOfMonth()->toDateString();
        
        return view('sanctions.sanctions')->with('lSanctions', $lSanctions)
                                            ->with('oUser', $oUser)
                                            ->with('type', SysConst::SANCION)
                                            ->with('lTypes', $lTypes)
                                            ->with('lRoles', $lRoles)
                                            ->with('startDate', $startDate)
                                            ->with('endDate', $endDate);
    }
}

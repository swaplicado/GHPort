<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seasons\SpecialSeasonType;
use App\Models\Seasons\SpecialSeason;
use App\Models\Adm\OrgChartJob;
use Carbon\Carbon;
use \App\Utils\delegationUtils;
use App\Utils\usersInSystemUtils;

class SpecialSeasonsController extends Controller
{
    public function index(){
        // $OrgChartJob = OrgChartJob::find(\Auth::user()->org_chart_job_id);
        if(\Auth::user()->rol_id == 4){
            $OrgChartJob = OrgChartJob::find(2);
        }else{
            $OrgChartJob = OrgChartJob::find(delegationUtils::getOrgChartJobIdUser());
        }
        $OrgChartJob->child = $OrgChartJob->getChildrens();
        $arrayOrgChartJobs = $OrgChartJob->getArrayChilds();

        $arrJobsId = \DB::table('ext_jobs_vs_org_chart_job')
                        ->whereIn('org_chart_job_id_n', $arrayOrgChartJobs)
                        ->pluck('ext_job_id');

        $arrDeptsId = \DB::table('ext_jobs')
                        ->whereIn('id_job', $arrJobsId)
                        ->where('is_deleted', 0)
                        ->pluck('department_id');

        $lDeptos = \DB::table('ext_departments')
                        ->whereIn('id_department', $arrDeptsId)
                        ->where('is_deleted', 0)
                        ->select('id_department', 'department_name_ui')
                        ->get();

        $lEmp = \DB::table('users')
                    // ->whereIn('job_id', $arrJobsId)
                    ->whereIn('org_chart_job_id', $arrayOrgChartJobs)
                    ->where('is_delete', 0)
                    ->where('is_active', 1)
                    ->select('id', 'full_name_ui')
                    ->get();

        $lAreas = \DB::table('org_chart_jobs')
                    ->whereIn('id_org_chart_job', $arrayOrgChartJobs)
                    // ->where('positions', '>', 1)
                    ->where('is_deleted', 0)
                    ->select('id_org_chart_job', 'job_name')
                    ->get();

        $lCompany = \DB::table('ext_company')
                        ->where('is_deleted', 0)
                        ->where('is_active', 1)
                        ->select('id_company', 'company_name_ui')
                        ->get();

        $lTypeSpecialSeasons = \DB::table('special_season_types')
                                    ->where('is_deleted', 0)
                                    ->get();

        $date = Carbon::now();
        $year = $date->year;
        
        $lSpecialSeasonType = SpecialSeasonType::leftJoin('users as u', 'u.id', '=', 'special_season_types.updated_by')
                                    ->where('special_season_types.is_deleted', 0)
                                    ->select(
                                        'special_season_types.*',
                                        'u.full_name_ui',
                                    )
                                    ->get();

        $lEmp = usersInSystemUtils::FilterUsersInSystem($lEmp, 'id');
        return view('seasons.seasons')->with('lDeptos', $lDeptos)
                                    ->with('lAreas', $lAreas)
                                    ->with('lEmp', $lEmp)
                                    ->with('lCompany', $lCompany)
                                    ->with('lTypeSpecialSeasons', $lTypeSpecialSeasons)
                                    ->with('lSpecialSeasonType', $lSpecialSeasonType)
                                    ->with('year', $year);
    }

    public function getSpecialSeason(Request $request){
        try {
            $arrIds = [];
            foreach ($request->options as $opt) {
                $arrIds[] = $opt['id'];
            }
            $lSpecialSeason = \DB::table('special_season as ss')
                                ->leftJoin('special_season_types as sst', 'ss.special_season_type_id', '=', 'sst.id_special_season_type');

            switch ($request->type) {
                case 'depto':
                    $lSpecialSeason = $lSpecialSeason->whereIn('ss.depto_id', $arrIds);
                    break;
                case 'user_id':
                    $lSpecialSeason = $lSpecialSeason->whereIn('ss.user_id', $arrIds);
                    break;
                case 'comp':
                    $lSpecialSeason = $lSpecialSeason->whereIn('ss.company_id', $arrIds);
                    break;
                case 'area':
                    $lSpecialSeason = $lSpecialSeason->whereIn('ss.org_chart_job_id', $arrIds);
                    break;
                
                default:
                    break;
            }

            $lSpecialSeason = $lSpecialSeason->whereYear('ss.start_date', $request->year)
                                            ->whereYear('ss.end_date', $request->year)
                                            ->where('ss.is_deleted', 0)
                                            ->where('sst.is_deleted', 0)
                                            ->select(
                                                'ss.*',
                                                'sst.id_special_season_type',
                                                'sst.name',
                                                'sst.key_code',
                                                'sst.priority',
                                                'sst.color',
                                                'sst.description',
                                            )
                                            ->get();

            $lMonths = [
                '',
                'Enero',
                'Febrero',
                'Marzo',
                'Abril',
                'Mayo',
                'Junio',
                'Julio',
                'Agosto',
                'Septiembre',
                'Octubre',
                'Noviembre',
                'Diciembre'
            ];

            foreach($lSpecialSeason as $oSeason){
                $oSeason->month = $lMonths[Carbon::parse($oSeason->start_date)->month];
            }
                                            
            $lSpecialSeasonType = SpecialSeasonType::leftJoin('users as u', 'u.id', '=', 'special_season_types.updated_by')
                                    ->where('special_season_types.is_deleted', 0)
                                    ->select(
                                        'special_season_types.*',
                                        'u.full_name_ui',
                                    )
                                    ->get();

            return json_encode(['success' => true, 'lSpecialSeason' => $lSpecialSeason, 'lSpecialSeasonType' => $lSpecialSeasonType]);
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al obtener los registros', 'icon' => 'error']);
        }
    }

    public function saveSpecialSeason(Request $request){
        try {
            \DB::beginTransaction();
            foreach ($request->table_class as $oKey => $data) {
                foreach($data as $key => $d){
                    if($d['priority'] > 0){
                        $oSpecialSeason = SpecialSeason::where('id_special_season', $d['season_id'])
                                                        ->whereYear('start_date', $request->year)
                                                        ->whereYear('end_date', $request->year)
                                                        ->where('is_deleted', 0)
                                                        ->first();

                        if(is_null($oSpecialSeason)){
                            $oSpecialSeason = new SpecialSeason();
                        }

                        switch ($request->type) {
                            case 'depto':
                                $oSpecialSeason->depto_id = $d['id_type'];
                                break;
                            case 'emp':
                                $oSpecialSeason->user_id = $d['id_type'];
                                break;
                            case 'area':
                                $oSpecialSeason->org_chart_job_id = $d['id_type'];
                                break;
                            case 'comp':
                                $oSpecialSeason->company_id = $d['id_type'];
                                break;
                            
                            default:
                                break;
                        }
    
                        switch ($key) {
                            case 'Enero':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-01-01');
                                break;
                            case 'Febrero':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-02-01');
                                break;
                            case 'Marzo':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-03-01');
                                break;
                            case 'Abril':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-04-01');
                                break;
                            case 'Mayo':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-05-01');
                                break;
                            case 'Junio':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-06-01');
                                break;
                            case 'Julio':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-07-01');
                                break;
                            case 'Agosto':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-08-01');
                                break;
                            case 'Septiembre':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-09-01');
                                break;
                            case 'Octubre':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-10-01');
                                break;
                            case 'Noviembre':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-11-01');
                                break;
                            case 'Diciembre':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-12-01');
                                break;
                            
                            default:
                                break;
                        }
    
                        $special_season_type = \DB::table('special_season_types')
                                                    ->where('priority', $d['priority'])
                                                    ->where('is_deleted', 0)
                                                    ->first();
    
                        $oSpecialSeason->start_date = $date->startOfMonth()->toDateString();
                        $oSpecialSeason->end_date = $date->endOfMonth()->toDateString();
                        $oSpecialSeason->special_season_type_id = $special_season_type->id_special_season_type;
                        $oSpecialSeason->is_deleted = 0;
                        // $oSpecialSeason->created_by = \Auth::user()->id;
                        // $oSpecialSeason->updated_by = \Auth::user()->id;
                        $oSpecialSeason->created_by = delegationUtils::getIdUser();
                        $oSpecialSeason->updated_by = delegationUtils::getIdUser();
                        $oSpecialSeason->save();
                    }else if ($d['season_id'] != null){
                        $oSpecialSeason = SpecialSeason::find($d['season_id']);
                        $oSpecialSeason->is_deleted = 1;
                        $oSpecialSeason->update();
                    }
                }
            }
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al guardar los registros', 'icon' => 'error']);
        }
        return json_encode(['success' => true, 'message' => 'Registros guardados con éxito', 'icon' => 'success']);
    }

    public function getSpecialSeasonByTypeYear($year, $type, $type_id){
        $oSpecialSeason = SpecialSeason::whereYear('start_date', $year)
                                        ->whereYear('end_date', $year);

        switch ($type) {
            case 'depto':
                $oSpecialSeason = $oSpecialSeason->where('depto_id', $type_id);
                break;
            case 'emp':
                $oSpecialSeason = $oSpecialSeason->where('user_id', $type_id);
                break;
            case 'area':
                $oSpecialSeason = $oSpecialSeason->where('org_chart_job_id', $type_id);
                break;
            case 'comp':
                $oSpecialSeason = $oSpecialSeason->where('company_id', $type_id);
                break;
            
            default:
                break;
        }

        $oSpecialSeason = $oSpecialSeason->where('is_deleted', 0)->get();
        
        return $oSpecialSeason;
    }

    public function copyToNextYear(Request $request){
        try {
            \DB::beginTransaction();
            
            foreach ($request->table_class as $oKey => $data) {
                foreach($data as $key => $d){
                    $oSpecialSeason = $this->getSpecialSeasonByTypeYear($request->year, $request->type, $d['id_type']);
                    foreach ($oSpecialSeason as $oSeason) {
                        $oSeason->is_deleted = 1;
                        $oSeason->update();
                    }
                }
            }

            foreach ($request->table_class as $oKey => $data) {
                foreach($data as $key => $d){
                    if($d['priority'] > 0){

                        $oSpecialSeason = new SpecialSeason();

                        switch ($request->type) {
                            case 'depto':
                                $oSpecialSeason->depto_id = $d['id_type'];
                                break;
                            case 'emp':
                                $oSpecialSeason->user_id = $d['id_type'];
                                break;
                            case 'area':
                                $oSpecialSeason->org_chart_job_id = $d['id_type'];
                                break;
                            case 'comp':
                                $oSpecialSeason->company_id = $d['id_type'];
                                break;

                            default:
                                break;
                        }
    
                        switch ($key) {
                            case 'Enero':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-01-01');
                                break;
                            case 'Febrero':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-02-01');
                                break;
                            case 'Marzo':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-03-01');
                                break;
                            case 'Abril':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-04-01');
                                break;
                            case 'Mayo':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-05-01');
                                break;
                            case 'Junio':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-06-01');
                                break;
                            case 'Julio':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-07-01');
                                break;
                            case 'Agosto':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-08-01');
                                break;
                            case 'Septiembre':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-09-01');
                                break;
                            case 'Octubre':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-10-01');
                                break;
                            case 'Noviembre':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-11-01');
                                break;
                            case 'Diciembre':
                                $date = Carbon::createFromFormat('Y-m-d', $request->year.'-12-01');
                                break;
                            
                            default:
                                break;
                        }
    
                        $special_season_type = \DB::table('special_season_types')
                                                    ->where('priority', $d['priority'])
                                                    ->where('is_deleted', 0)
                                                    ->first();
    
                        $oSpecialSeason->start_date = $date->startOfMonth()->toDateString();
                        $oSpecialSeason->end_date = $date->endOfMonth()->toDateString();
                        $oSpecialSeason->special_season_type_id = $special_season_type->id_special_season_type;
                        $oSpecialSeason->is_deleted = 0;
                        // $oSpecialSeason->created_by = \Auth::user()->id;
                        // $oSpecialSeason->updated_by = \Auth::user()->id;
                        $oSpecialSeason->created_by = delegationUtils::getIdUser();
                        $oSpecialSeason->updated_by = delegationUtils::getIdUser();
                        $oSpecialSeason->save();
                    }
                }
            }
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al guardar los registros', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'message' => 'Registros guardados con éxito', 'icon' => 'success']);
    }
}
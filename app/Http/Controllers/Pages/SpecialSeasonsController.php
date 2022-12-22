<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seasons\SpecialSeasonType;
use App\Models\Seasons\SpecialSeason;
use App\Models\Adm\OrgChartJob;

class SpecialSeasonsController extends Controller
{
    public function index(){
        // $OrgChartJob = OrgChartJob::find(\Auth::user()->org_chart_job_id);
        $OrgChartJob = OrgChartJob::find(37);
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
                    ->whereIn('job_id', $arrJobsId)
                    ->where('is_delete', 0)
                    ->where('is_active', 1)
                    ->select('id', 'full_name_ui')
                    ->get();

        $lAreas = \DB::table('org_chart_jobs')
                    ->whereIn('id_org_chart_job', $arrayOrgChartJobs)
                    ->where('positions', '>', 1)
                    ->where('is_deleted', 0)
                    ->select('id_org_chart_job', 'job_name')
                    ->get();

        $lTypeSpecialSeasons = \DB::table('special_season_types')
                                    ->where('is_deleted', 0)
                                    ->get();

        $lSpecialSeasonType = SpecialSeasonType::leftJoin('users as u', 'u.id', '=', 'special_season_types.updated_by')
                                    ->where('special_season_types.is_deleted', 0)
                                    ->select(
                                        'special_season_types.*',
                                        'u.full_name_ui',
                                    )
                                    ->get();

        return view('seasons.seasons')->with('lDeptos', $lDeptos)
                                    ->with('lAreas', $lAreas)
                                    ->with('lEmp', $lEmp)
                                    ->with('lTypeSpecialSeasons', $lTypeSpecialSeasons)
                                    ->with('lSpecialSeasonType', $lSpecialSeasonType);
    }

    public function getSpecialSeason(Request $request){
        try {
            $arrIds = [];
            foreach ($request->options as $opt) {
                $arrIds[] = $opt['id'];
            }
            $lSpecialSeason = \DB::table('special_season');

            switch ($request->type) {
                case 'Departamento':
                    $lSpecialSeason = $lSpecialSeason->whereIn('depto_id', $arrIds);
                    break;
                case 'Area funcional':
                    $lSpecialSeason = $lSpecialSeason->whereIn('org_chart_job_id', $arrIds);
                    break;
                case 'Empleado':
                    $lSpecialSeason = $lSpecialSeason->whereIn('user_id', $arrIds);
                    break;
                case 'Empresa':
                    break;
                
                default:
                    break;
            }

            $lSpecialSeason = $lSpecialSeason->where('is_deleted', 0)->get();
            return json_encode(['success' => true, 'lSpecialSeason' => $lSpecialSeason]);
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener los registros', 'icon' => 'error']);
        }
    }

    public function saveSpecialSeason(Request $request){
        try {
            
        } catch (\Throwable $th) {
            
        }
    }
}

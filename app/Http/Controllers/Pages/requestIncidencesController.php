<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Utils\incidencesUtils;
use \App\Utils\delegationUtils;
use \App\Models\Vacations\Application;
use App\Models\Vacations\ApplicationLog;
use App\Utils\orgChartUtils;
use App\Constants\SysConst;
use App\Utils\EmployeeVacationUtils;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class requestIncidencesController extends Controller
{
    public function index(){
        delegationUtils::getAutorizeRolUser([SysConst::JEFE, SysConst::ADMINISTRADOR]);
        if(\Auth::user()->rol_id == SysConst::ADMINISTRADOR){
            $myManagers = orgChartUtils::getMyManagers(2);
            $org_chart_job_id = 2;
        }else{
            $myManagers = orgChartUtils::getMyManagers(delegationUtils::getOrgChartJobIdUser());
            $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
        }
        
        $lIncidences = incidencesUtils::getMyEmployeeslIncidences();

        $constants = [
            'SEMANA' => SysConst::SEMANA,
            'QUINCENA' => SysConst::QUINCENA,
            'TYPE_CUMPLEAÑOS' => SysConst::TYPE_CUMPLEAÑOS,
        ];

        $lClass = \DB::table('cat_incidence_cls')
                        ->where('id_incidence_cl', '!=', SysConst::TYPE_VACACIONES)
                        ->where('is_deleted', 0)
                        ->get();

        $lTypes = \DB::table('cat_incidence_tps')
                        ->where('incidence_cl_id', '!=', SysConst::TYPE_VACACIONES)
                        ->where('is_deleted', 0)
                        ->where('is_active', 1)
                        ->get();

        // $lTemp_special = EmployeeVacationUtils::getEmployeeTempSpecial(delegationUtils::getOrgChartJobIdUser(), delegationUtils::getIdUser(), delegationUtils::getJobIdUser());
        $lTemp_special = [];

        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        $lChildAreas = orgChartUtils::getAllChildsOrgChartJob($org_chart_job_id);

        $lEmployees = \DB::table('users')
                        ->where('is_active', 1)
                        ->where('is_delete', 0)
                        ->whereIn('org_chart_job_id', $lChildAreas)
                        ->select(
                            'id',
                            'full_name_ui as text',
                        )
                        ->get();

        return view('Incidences.requestIncidences')->with('constants', $constants)
                                                    ->with('myManagers', $myManagers)
                                                    ->with('lIncidences', $lIncidences)
                                                    ->with('lClass', $lClass)
                                                    ->with('lTypes', $lTypes)
                                                    ->with('lTemp', $lTemp_special)
                                                    ->with('lHolidays', $lHolidays)
                                                    ->with('lEmployees', $lEmployees)
                                                    ->with('oUser', null);
    }

    public function getEmployee(Request $request){
        try {
            $oUser = \DB::table('users as u')
                        ->leftJoin('users_vs_photos as up', 'up.user_id', '=', 'u.id')
                        ->where('u.id', $request->user_id)
                        ->select(
                            'u.*',
                            'up.photo_base64_n as photo64',
                        )
                        ->first();

            $from = Carbon::parse($oUser->benefits_date);
            $to = Carbon::today()->locale('es');
    
            $human = $to->diffForHumans($from, true, false, 6);
    
            $oUser->antiquity = $human;

            $lTemp_special = EmployeeVacationUtils::getEmployeeTempSpecial($oUser->org_chart_job_id, $oUser->id, $oUser->job_id);

            $lIncidences = incidencesUtils::getUserIncidences($oUser->id);
        } catch (\Throwable $th) {
            return json_encode(['sucess' => false, 'message' => 'Error al obtener al colaborador', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oUser' => $oUser, 'lTemp' => $lTemp_special, 'lIncidences' => $lIncidences]);
    }

    public function approbeIncidence(Request $request){
        try {
            \DB::beginTransaction();
            $application = Application::findOrFail($request->application_id);
            $application->request_status_id = SysConst::APPLICATION_APROBADO;
            $application->user_apr_rej_id = delegationUtils::getIdUser();
            $application->approved_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $request->comments;
            $application->return_date = $request->returnDate;
            $application->update();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = delegationUtils::getIdUser();
            $application_log->updated_by = delegationUtils::getIdUser();
            $application_log->save();
            
            $lIncidences = incidencesUtils::getMyEmployeeslIncidences();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['sucess' => false, 'message' => 'Error al aprobar la incidencia', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }

    public function rejectIncidence(Request $request){
        try {
            \DB::beginTransaction();
            $application = Application::findOrFail($request->application_id);
            $application->request_status_id = SysConst::APPLICATION_RECHAZADO;
            $application->user_apr_rej_id = delegationUtils::getIdUser();
            $application->rejected_date_n = Carbon::now()->toDateString();
            $application->sup_comments_n = $request->comments;
            $application->return_date = $request->returnDate;
            $application->update();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = delegationUtils::getIdUser();
            $application_log->updated_by = delegationUtils::getIdUser();
            $application_log->save();

            $lIncidences = incidencesUtils::getMyEmployeeslIncidences();
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al rechazar la incidencia', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }

    public function getAllEmployees(){
        try {
            if(\Auth::user()->rol_id == SysConst::ADMINISTRADOR){
                $org_chart_job_id = 2;
            }else{
                $org_chart_job_id = delegationUtils::getOrgChartJobIdUser();
            }

            $lChildAreas = orgChartUtils::getAllChildsOrgChartJob($org_chart_job_id);

            $lEmployees = \DB::table('users')
                            ->where('is_active', 1)
                            ->where('is_delete', 0)
                            ->whereIn('org_chart_job_id', $lChildAreas)
                            ->select(
                                'id',
                                'full_name_ui as text',
                            )
                            ->get();

        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener a los colaboradores', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lEmployees']);
    }
}

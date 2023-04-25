<?php

namespace App\Http\Controllers\Pages;

use \App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Constants\SysConst;
use \App\Utils\EmployeeVacationUtils;
use \App\Utils\folioUtils;
use \App\Models\Vacations\Application;
use \App\Models\Vacations\ApplicationVsTypes;
use \App\Models\Vacations\ApplicationLog;
use \App\Utils\delegationUtils;
use \App\Utils\incidencesUtils;
use App\Utils\orgChartUtils;
use App\Models\Vacations\MailLog;
use Carbon\Carbon;

class incidencesController extends Controller
{
    public function getIncidences($user_id){
        $lIncidences = \DB::table('applications as ap')
                        ->leftJoin('cat_incidence_tps as tp', 'tp.id_incidence_tp', '=', 'ap.type_incident_id')
                        ->leftJoin('cat_incidence_cls as cl', 'cl.id_incidence_cl', '=', 'tp.incidence_cl_id')
                        ->leftJoin('applications_vs_types as at', 'at.application_id', '=', 'ap.id_application')
                        ->leftJoin('sys_applications_sts as st', 'st.id_applications_st', '=', 'ap.request_status_id')
                        ->leftJoin('users as u', 'u.id', '=', 'ap.user_apr_rej_id')
                        ->where('type_incident_id', '!=', SysConst::TYPE_VACACIONES)
                        ->where('ap.is_deleted', 0)
                        ->where('ap.user_id', $user_id)
                        ->select(
                            'ap.*',
                            'at.is_normal',
                            'at.is_past',
                            'at.is_season_special',
                            'tp.id_incidence_tp',
                            'tp.incidence_tp_name',
                            'cl.id_incidence_cl',
                            'cl.incidence_cl_name',
                            'st.applications_st_name',
                            'u.full_name_ui as user_apr_rej_name',
                        )
                        ->get();

        return $lIncidences;
    }

    public function index(){
        // $lIncidences = $this->getIncidences(delegationUtils::getIdUser());
        $lIncidences = incidencesUtils::getUserIncidences(delegationUtils::getIdUser());

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

        $lTemp_special = EmployeeVacationUtils::getEmployeeTempSpecial(delegationUtils::getOrgChartJobIdUser(), delegationUtils::getIdUser(), delegationUtils::getJobIdUser());

        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha');

        return view('Incidences.incidences')->with('lIncidences', $lIncidences)
                                            ->with('constants', $constants)
                                            ->with('lClass', $lClass)
                                            ->with('lTypes', $lTypes)
                                            ->with('lTemp', $lTemp_special)
                                            ->with('lHolidays', $lHolidays)
                                            ->with('oUser', \Auth::user());
    }

    public function createIncidence(Request $request){
        $start_date = $request->startDate;
        $end_date = $request->endDate;
        $comments = $request->comments;
        $takedDays = $request->takedDays;
        $return_date = $request->returnDate;
        $tot_calendar_days = $request->tot_calendar_days;
        $lDays = $request->lDays;
        $take_holidays = false;
        $take_rest_days = false;
        $employee_id = $request->employee_id;
        $type_incident_id = $request->incident_type_id;
        $class_incident_id = $request->incident_class_id;
        $is_normal = $request->is_normal;
        $is_past = $request->is_past;
        $is_season_special  = $request->is_season_special;
        try {
            $arrApplicationsEA = EmployeeVacationUtils::getEmpApplicationsEA($employee_id);

            foreach($arrApplicationsEA as $arr){
                $isBetWeen = Carbon::parse($arr)->between($startDate, $endDate);
                if($isBetWeen){
                    return json_encode(['success' => false, 'message' => 'Ya existe una incidencia para la fecha: '.Carbon::parse($arr)->locale('es-ES')->isoFormat('ddd D-MMM-YYYY'), 'icon' => 'warning']);
                }
            }

            \DB::beginTransaction();

            $application = new Application();
            $application->folio_n = folioUtils::makeFolio(Carbon::now(), $employee_id, $type_incident_id);
            $application->start_date = $start_date;
            $application->end_date = $end_date;
            $application->take_holidays = $take_holidays;
            $application->take_rest_days = $take_rest_days;
            $application->total_days = $takedDays;
            $application->tot_calendar_days = $tot_calendar_days;
            $application->return_date = $return_date;
            $application->ldays = json_encode($lDays);
            $application->user_id = $employee_id;
            $application->request_status_id = SysConst::APPLICATION_CREADO;
            $application->type_incident_id = $type_incident_id;
            $application->emp_comments_n = $comments;
            $application->is_deleted = false;
            $application->save();

            $applicationVsType = new ApplicationVsTypes();
            $applicationVsType->application_id = $application->id_application;
            $applicationVsType->is_past = $is_past;
            $applicationVsType->is_season_special = $is_season_special;
            $applicationVsType->is_recover_vacation = 0;
            $applicationVsType->is_normal = !($request->is_past || $request->is_season_special);
            $applicationVsType->save();

            $application_log = new ApplicationLog();
            $application_log->application_id = $application->id_application;
            $application_log->application_status_id = $application->request_status_id;
            $application_log->created_by = delegationUtils::getIdUser();
            $application_log->updated_by = delegationUtils::getIdUser();
            $application_log->save();

            $lIncidences = $this->getIncidences(delegationUtils::getIdUser());

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al crear la incidencia', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }

    public function updateIncidence(Request $request){
        $id_application = $request->id_application;
        $start_date = $request->startDate;
        $end_date = $request->endDate;
        $comments = $request->comments;
        $takedDays = $request->takedDays;
        $return_date = $request->returnDate;
        $tot_calendar_days = $request->tot_calendar_days;
        $lDays = $request->lDays;
        $take_holidays = false;
        $take_rest_days = false;
        $employee_id = $request->employee_id;
        $type_incident_id = $request->incident_type_id;
        $class_incident_id = $request->incident_class_id;
        $is_normal = $request->is_normal;
        $is_past = $request->is_past;
        $is_season_special  = $request->is_season_special;

        try {
            $application = Application::findOrFail($id_application);

            \DB::beginTransaction();

            $application->start_date = $start_date;
            $application->end_date = $end_date;
            $application->total_days = $takedDays;
            $application->tot_calendar_days = $tot_calendar_days;
            $application->return_date = $return_date;
            $application->ldays = json_encode($lDays);
            $application->emp_comments_n = $comments;
            $application->update();

            $applicationVsType = ApplicationVsTypes::where('application_id', $application->id_application)->first();
            $applicationVsType->is_past = $is_past;
            $applicationVsType->is_season_special = $is_season_special;
            $applicationVsType->is_normal = !($request->is_past || $request->is_season_special);
            $applicationVsType->update();

            $lIncidences = $this->getIncidences(delegationUtils::getIdUser());
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al actualizar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }

    public function getApplication(Request $request){
        $application_id = $request->application_id;
        try {
            $oApplication = \DB::table('applications as ap')
                                ->leftJoin('cat_incidence_tps as tp', 'tp.id_incidence_tp', '=', 'ap.type_incident_id')                    
                                ->leftJoin('cat_incidence_cls as cl', 'cl.id_incidence_cl', '=', 'tp.incidence_cl_id')
                                ->leftJoin('applications_vs_types as at', 'at.application_id', '=', 'ap.id_application')
                                ->where('id_application', $application_id)
                                ->select(
                                    'ap.*',
                                    'at.is_normal',
                                    'at.is_past',
                                    'at.is_season_special',
                                    'tp.id_incidence_tp',
                                    'tp.incidence_tp_name',
                                    'cl.id_incidence_cl',
                                    'cl.incidence_cl_name',
                                )
                                ->first();

        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al obtener el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'oApplication' => $oApplication]);
    }

    public function deleteIncidence(Request $request){
        $application_id = $request->application_id;
        try {
            $application = Application::findOrFail($application_id);

            \DB::beginTransaction();

            $application->is_deleted = 1;
            $application->update();

            $lIncidences = $this->getIncidences(delegationUtils::getIdUser());
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al eliminar el registro', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }

    public function sendIncident(Request $request){
        $application_id = $request->application_id;
        try {
            $application = Application::findOrFail($application_id);
            // $data = incidencesUtils::checkExternalIncident($application);

            // if($data->code == 500 || $data->code == 550){
            //     return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
            // }

            $date = Carbon::now();
            $application->request_status_id = SysConst::APPLICATION_ENVIADO;
            $application->date_send_n = $date->toDateString();
            $application->update();

            $user = \DB::table('users')
                        ->where('id', $application->user_id)
                        ->first();

            $superviser = orgChartUtils::getExistDirectSuperviserOrgChartJob($user->org_chart_job_id);

            // $mailLog = new MailLog();
            // $mailLog->date_log = Carbon::now()->toDateString();
            // $mailLog->to_user_id = $superviser->id;
            // $mailLog->application_id_n = $application->id_application;
            // $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
            // $mailLog->type_mail_id = SysConst::MAIL_SOLICITUD_VACACIONES;
            // $mailLog->is_deleted = 0;
            // $mailLog->created_by = delegationUtils::getIdUser();
            // $mailLog->updated_by = delegationUtils::getIdUser();
            // $mailLog->save();

            $lIncidences = $this->getIncidences(delegationUtils::getIdUser());

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al enviar la incidencia']);
        }

        // $mypool = Pool::create();
        //     $mypool[] = async(function () use ($application, $request, $superviser, $mailLog){
        //         try {
        //             Mail::to($superviser->institutional_mail)->send(new requestVacationMail(
        //                                                     $application->id_application,
        //                                                     $request->employee_id,
        //                                                     $application->ldays,
        //                                                     $request->returnDate
        //                                                 )
        //                                             );
        //         } catch (\Throwable $th) {
        //             $mailLog->sys_mails_st_id = SysConst::MAIL_NO_ENVIADO;
        //             $mailLog->update();   
        //             return null; 
        //         }

        //         $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
        //         $mailLog->update();
        //     })->then(function ($mailLog) {
                
        //     })->catch(function ($mailLog) {
                
        //     })->timeout(function ($mailLog) {
                
        //     });

        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }

    public function sendAndAuthorize(Request $request){
        try {
            $application_id = $request->application_id;

            \DB::beginTransaction();
            
            $application = Application::findOrFail($application_id);
            // $data = incidencesUtils::checkExternalIncident($application);

            // if($data->code == 500 || $data->code == 550){
            //     return json_encode(['success' => false, 'message' => $data->message, 'icon' => 'error']);
            // }

            $date = Carbon::now();
            $application->request_status_id = SysConst::APPLICATION_APROBADO;
            $application->date_send_n = $date->toDateString();
            $application->update();

            $lIncidences = $this->getIncidences(delegationUtils::getIdUser());
            
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'message' => 'Error al enviar y autorizar la solicitud', 'icon' => 'error']);
        }

        return json_encode(['success' => true, 'lIncidences' => $lIncidences]);
    }
}

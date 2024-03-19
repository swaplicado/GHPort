<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vacations\MailLog;
use App\Constants\SysConst;
use Illuminate\Support\Facades\Mail;
use App\Mail\requestVacationMail;
use App\Mail\authorizeVacationMail;
use Carbon\Carbon;
use \App\Utils\delegationUtils;
use App\Utils\dateUtils;

class MailsLogscontroller extends Controller
{
    public function index(){
        if(\Auth::user()->rol_id == 4){
            $lMails = \DB::table('mail_logs as ml')
                        ->Join('sys_mails_sts as ms', 'ml.sys_mails_st_id',  '=', 'ms.id_mail_st')
                        ->Join('cat_mails_tps as mt', 'mt.id_mail_tp', '=', 'ml.type_mail_id')
                        ->Join('users as u', 'u.id', '=', 'ml.to_user_id')
                        ->where('ml.is_deleted', 0)
                        ->select('ml.*', 'ms.mail_st_name', 'mt.mail_tp_name', 'u.full_name_ui')
                        ->get();
        }else{
            $lMails = \DB::table('mail_logs as ml')
                        ->Join('sys_mails_sts as ms', 'ml.sys_mails_st_id',  '=', 'ms.id_mail_st')
                        ->Join('cat_mails_tps as mt', 'mt.id_mail_tp', '=', 'ml.type_mail_id')
                        ->Join('users as u', 'u.id', '=', 'ml.to_user_id')
                        // ->where('ml.created_by', \Auth::user()->id)
                        ->where('ml.created_by', delegationUtils::getIdUser())
                        ->where('ml.is_deleted', 0)
                        ->select('ml.*', 'ms.mail_st_name', 'mt.mail_tp_name', 'u.full_name_ui')
                        ->get();
        }

        $constants = [
            'MAIL_EN_PROCESO' => SysConst::MAIL_EN_PROCESO,
            'MAIL_ENVIADO' => SysConst::MAIL_ENVIADO,
            'MAIL_NO_ENVIADO' => SysConst::MAIL_NO_ENVIADO,
            'MAIL_SOLICITUD_VACACIONES' => SysConst::MAIL_SOLICITUD_VACACIONES,
            'MAIL_ACEPT_RECH_SOLICITUD' => SysConst::MAIL_ACEPT_RECH_SOLICITUD,
        ];

        $year = Carbon::now()->year;

        return view('mails.mailLog')->with('lMails', $lMails)
                                    ->with('constants', $constants)
                                    ->with('year', $year);
    }

    public function getlDays($application){
        $lDays = [];
        $start_date = Carbon::parse($application->start_date);
        $end_date = Carbon::parse($application->end_date);
        $oDate = Carbon::parse($application->start_date);
        $efective_days = $application->total_days;
        $calendar_days = $application->tot_calendar_days;
        $lHolidays = \DB::table('holidays')
                        ->where('fecha', '>', Carbon::now()->subDays(30)->toDateString())
                        ->where('is_deleted', 0)
                        ->pluck('fecha')
                        ->toArray();
             
        for ($i=0; $i < $calendar_days; $i++) { 
            if($oDate->dayOfWeek != Carbon::SATURDAY && $oDate->dayOfWeek != Carbon::SUNDAY && 
                    !in_array($oDate->toDateString(), $lHolidays)){
                array_push($lDays, $oDate->toDateString());
            }
            $oDate->addDay();
        }

        if(count($lDays) < $efective_days){
            $oDate = Carbon::parse($application->start_date);
            for ($i=0; $i < $calendar_days; $i++) { 
                if($oDate->dayOfWeek == Carbon::SATURDAY || $oDate->dayOfWeek == Carbon::SUNDAY || 
                            in_array($oDate->toDateString(), $lHolidays) ){
                    array_push($lDays, $oDate->toDateString());
                }
                if(count($lDays) == $efective_days){
                    break;
                }
                $oDate->addDay();
            }
        }
        sort($lDays, SORT_STRING);
        // foreach($lDays as $key => $d){
        //     $lDays[$key] = dateUtils::formatDate($d, 'D/m/Y dddd');
        // }
        return $lDays;
    }

    public function sendMail(Request $request){
        try {
            $mailLog = MailLog::find($request->id_mailLog);
    
            $user = \DB::table('users')
                            ->where('id', $mailLog->to_user_id)
                            ->where('is_active', 1)
                            ->where('is_delete', 0)
                            ->first();
            if($mailLog->application_id_n == null){
                $application = \DB::table('applications')
                            ->where('id_application', $mailLog->hours_leave_id_n)
                            ->first();
            }else{
                $application = \DB::table('applications')
                            ->where('id_application', $mailLog->application_id_n)
                            ->first();
            }
            $lDays = $this->getlDays($application);
            
            if($mailLog->type_mail_id == SysConst::MAIL_SOLICITUD_VACACIONES){
                $this->sendMailRequestVac($user->institutional_mail, $application, $lDays);
            }else if($mailLog->type_mail_id == SysConst::MAIL_ACEPT_RECH_SOLICITUD){
                $this->sendMailAuthVac($user->institutional_mail, $application, $user->id, $lDays);
            }
            
        } catch (\Throwable $th) {
            \Log::error($th);
            return json_encode(['success' => false, 'message' => 'Error al enviar el e-mail', 'icon' => 'error']);    
        }

        $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
        $mailLog->update();

        if(\Auth::user()->rol_id == 4){
            $lMails = \DB::table('mail_logs as ml')
                        ->Join('sys_mails_sts as ms', 'ml.sys_mails_st_id',  '=', 'ms.id_mail_st')
                        ->Join('cat_mails_tps as mt', 'mt.id_mail_tp', '=', 'ml.type_mail_id')
                        ->Join('users as u', 'u.id', '=', 'ml.to_user_id')
                        ->where('ml.is_deleted', 0)
                        ->select('ml.*', 'ms.mail_st_name', 'mt.mail_tp_name', 'u.full_name_ui')
                        ->get();
        }else{
            $lMails = \DB::table('mail_logs as ml')
                        ->Join('sys_mails_sts as ms', 'ml.sys_mails_st_id',  '=', 'ms.id_mail_st')
                        ->Join('cat_mails_tps as mt', 'mt.id_mail_tp', '=', 'ml.type_mail_id')
                        ->Join('users as u', 'u.id', '=', 'ml.to_user_id')
                        // ->where('ml.created_by', \Auth::user()->id)
                        ->where('ml.created_by', delegationUtils::getIdUser())
                        ->where('ml.is_deleted', 0)
                        ->select('ml.*', 'ms.mail_st_name', 'mt.mail_tp_name', 'u.full_name_ui')
                        ->get();
        }

        return json_encode(['success' => true, 'message' => 'E-mail enviado con exito', 'icon' => 'success', 'lMails'=> $lMails]);
    }

    public function sendMailRequestVac($email, $application, $lDays){
        Mail::to($email)->send(new requestVacationMail(
                $application->id_application,
                $application->user_id,
                $lDays,
                $application->return_date
            )
        );
    }

    public function sendMailAuthVac($email, $application, $user_id, $lDays){
        Mail::to($email)->send(new authorizeVacationMail(
                $application->id_application,
                $user_id,
                $lDays,
                $application->return_date
            )
        );
    }

    public function delete(Request $request){
        try {
            $mailLog = MailLog::findOrFail($request->id_mailLog);
            $mailLog->is_deleted = 1;
            $mailLog->update();
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al eliminar el registro', 'icon' => false]);
        }

        if(\Auth::user()->rol_id == 4){
            $lMails = \DB::table('mail_logs as ml')
                        ->Join('sys_mails_sts as ms', 'ml.sys_mails_st_id',  '=', 'ms.id_mail_st')
                        ->Join('cat_mails_tps as mt', 'mt.id_mail_tp', '=', 'ml.type_mail_id')
                        ->Join('users as u', 'u.id', '=', 'ml.to_user_id')
                        ->where('ml.is_deleted', 0)
                        ->select('ml.*', 'ms.mail_st_name', 'mt.mail_tp_name', 'u.full_name_ui')
                        ->get();
        }else{
            $lMails = \DB::table('mail_logs as ml')
                        ->Join('sys_mails_sts as ms', 'ml.sys_mails_st_id',  '=', 'ms.id_mail_st')
                        ->Join('cat_mails_tps as mt', 'mt.id_mail_tp', '=', 'ml.type_mail_id')
                        ->Join('users as u', 'u.id', '=', 'ml.to_user_id')
                        // ->where('ml.created_by', \Auth::user()->id)
                        ->where('ml.created_by', delegationUtils::getIdUser())
                        ->where('ml.is_deleted', 0)
                        ->select('ml.*', 'ms.mail_st_name', 'mt.mail_tp_name', 'u.full_name_ui')
                        ->get();
        }

        return json_encode(['success' => true, 'message' => 'Registro eliminado con éxito', 'icon' => 'success', 'lMails' => $lMails]);
    }

    public function filterYear(Request $request){
        try {
            $date = $request->year.'-01-01';
            $startOfYear = Carbon::parse($date)->startOfYear()->toDateString();
            $endOfYear   = Carbon::parse($date)->endOfYear()->toDateString();

            if(\Auth::user()->rol_id == 4){
                $lMails = \DB::table('mail_logs as ml')
                            ->Join('sys_mails_sts as ms', 'ml.sys_mails_st_id',  '=', 'ms.id_mail_st')
                            ->Join('cat_mails_tps as mt', 'mt.id_mail_tp', '=', 'ml.type_mail_id')
                            ->Join('users as u', 'u.id', '=', 'ml.to_user_id')
                            ->where('ml.is_deleted', 0)
                            ->whereBetween('ml.date_log', [$startOfYear, $endOfYear])
                            ->select('ml.*', 'ms.mail_st_name', 'mt.mail_tp_name', 'u.full_name_ui')
                            ->get();
            }else{
                $lMails = \DB::table('mail_logs as ml')
                            ->Join('sys_mails_sts as ms', 'ml.sys_mails_st_id',  '=', 'ms.id_mail_st')
                            ->Join('cat_mails_tps as mt', 'mt.id_mail_tp', '=', 'ml.type_mail_id')
                            ->Join('users as u', 'u.id', '=', 'ml.to_user_id')
                            // ->where('ml.created_by', \Auth::user()->id)
                            ->where('ml.created_by', delegationUtils::getIdUser())
                            ->where('ml.is_deleted', 0)
                            ->whereBetween('ml.date_log', [$startOfYear, $endOfYear])
                            ->select('ml.*', 'ms.mail_st_name', 'mt.mail_tp_name', 'u.full_name_ui')
                            ->get();
            }
            
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al cargar los registros', 'icon' => 'error']);    
        }

        return json_encode(['success' => true, 'lMails' => $lMails]);
    }
}

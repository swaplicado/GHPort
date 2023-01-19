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

class MailsLogscontroller extends Controller
{
    public function index(){
        $lMails = \DB::table('mail_logs as ml')
                    ->Join('sys_mails_sts as ms', 'ml.sys_mails_st_id',  '=', 'ms.id_mail_st')
                    ->Join('cat_mails_tps as mt', 'mt.id_mail_tp', '=', 'ml.type_mail_id')
                    ->Join('users as u', 'u.id', '=', 'ml.to_user_id')
                    // ->where('ml.created_by', \Auth::user()->id)
                    ->where('ml.created_by', delegationUtils::getIdUser())
                    ->where('ml.is_deleted', 0)
                    ->select('ml.*', 'ms.mail_st_name', 'mt.mail_tp_name', 'u.full_name_ui')
                    ->get();

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

    public function sendMail(Request $request){
        try {
            $mailLog = MailLog::find($request->id_mailLog);
    
            $user = \DB::table('users')
                            ->where('id', $mailLog->to_user_id)
                            ->where('is_active', 1)
                            ->where('is_delete', 0)
                            ->first();
    
            $application = \DB::table('applications')
                            ->where('id_application', $mailLog->application_id_n)
                            ->first();
            
            if($mailLog->type_mail_id == SysConst::MAIL_SOLICITUD_VACACIONES){
                $this->sendMailRequestVac($user->email, $application);
            }else if($mailLog->type_mail_id == SysConst::MAIL_ACEPT_RECH_SOLICITUD){
                $this->sendMailAuthVac($user->email, $application, $user->id);
            }
            
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al enviar el e-mail', 'icon' => 'error']);    
        }

        $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
        $mailLog->update();

        $lMails = \DB::table('mail_logs as ml')
                    ->Join('sys_mails_sts as ms', 'ml.sys_mails_st_id',  '=', 'ms.id_mail_st')
                    ->Join('cat_mails_tps as mt', 'mt.id_mail_tp', '=', 'ml.type_mail_id')
                    ->Join('users as u', 'u.id', '=', 'ml.to_user_id')
                    // ->where('ml.created_by', \Auth::user()->id)
                    ->where('ml.created_by', delegationUtils::getIdUser())
                    ->where('ml.is_deleted', 0)
                    ->select('ml.*', 'ms.mail_st_name', 'mt.mail_tp_name', 'u.full_name_ui')
                    ->get();

        return json_encode(['success' => true, 'message' => 'E-mail enviado con exito', 'icon' => 'success', 'lMails'=> $lMails]);
    }

    public function sendMailRequestVac($email, $application){
        Mail::to($email)->send(new requestVacationMail(
                $application->id_application,
                // \Auth::user()->id,
                delegationUtils::getIdUser(),
                [],
                $application->return_date
            )
        );
    }

    public function sendMailAuthVac($email, $application, $user_id){
        Mail::to($email)->send(new authorizeVacationMail(
                $application->id_application,
                $user_id,
                [],
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

        $lMails = \DB::table('mail_logs as ml')
                    ->Join('sys_mails_sts as ms', 'ml.sys_mails_st_id',  '=', 'ms.id_mail_st')
                    ->Join('cat_mails_tps as mt', 'mt.id_mail_tp', '=', 'ml.type_mail_id')
                    ->Join('users as u', 'u.id', '=', 'ml.to_user_id')
                    // ->where('ml.created_by', \Auth::user()->id)
                    ->where('ml.created_by', delegationUtils::getIdUser())
                    ->where('ml.is_deleted', 0)
                    ->select('ml.*', 'ms.mail_st_name', 'mt.mail_tp_name', 'u.full_name_ui')
                    ->get();

        return json_encode(['success' => true, 'message' => 'Registro eliminado con éxito', 'icon' => 'success', 'lMails' => $lMails]);
    }

    public function filterYear(Request $request){
        try {
            $date = $request->year.'-01-01';
            $startOfYear = Carbon::parse($date)->startOfYear()->toDateString();
            $endOfYear   = Carbon::parse($date)->endOfYear()->toDateString();

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
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Error al cargar los registros', 'icon' => 'error']);    
        }

        return json_encode(['success' => true, 'lMails' => $lMails]);
    }
}

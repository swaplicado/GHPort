<?php

namespace App\Utils;

use \App\Constants\SysConst;
use App\Mail\invitationEventMail;
use App\Mail\modificationEventMail;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use GuzzleHttp\Request;
use GuzzleHttp\Exception\RequestException;
use App\Models\Vacations\Application;
use Carbon\Carbon;
use Spatie\Async\Pool;
use App\Models\Vacations\MailLog;
use App\Mail\requestVacationMail;

class emailUtils {
    public static function sendMail($lUsers, $idEvent, $typeMail){
        $mypool = Pool::create();
        $mypool[] = async(function () use ($lUsers, $idEvent, $typeMail){

            try {
                foreach($lUsers as $usr){
                    $mailLog = new MailLog();
                    $mailLog->date_log = Carbon::now()->toDateString();
                    $mailLog->to_user_id = $usr->id;
                    $mailLog->application_id_n = $idEvent;
                    $mailLog->sys_mails_st_id = SysConst::MAIL_EN_PROCESO;
                    $mailLog->type_mail_id = $typeMail > 0 ? SysConst::MAIL_INVITACION_EVENTO : SysConst::MAIL_MODIFICACION_EVENTO;
                    $mailLog->is_deleted = 0;
                    $mailLog->created_by = \Auth::user()->id;
                    $mailLog->updated_by = \Auth::user()->id;
                    $mailLog->save();
                    if($typeMail > 0){
                        Mail::to($usr->institutional_mail)->send(new invitationEventMail(
                            $idEvent,
                            $usr->id,
                        )
                    );
                    }else{
                        Mail::to($usr->institutional_mail)->send(new modificationEventMail(
                            $idEvent,
                            $usr->id,
                        )
                    );
                    }
                    
                }

            } catch (\Throwable $th) {
                $mailLog->sys_mails_st_id = SysConst::MAIL_NO_ENVIADO;
                $mailLog->update();   
                \Log::error($th);
                return null; 
            }

            $mailLog->sys_mails_st_id = SysConst::MAIL_ENVIADO;
            $mailLog->update();

        })->then(function ($mailLog) {
            
        })->catch(function ($mailLog) {
            
        })->timeout(function ($mailLog) {
            
        });   
    }
}
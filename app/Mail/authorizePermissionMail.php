<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Utils\dateUtils;
use App\Utils\permissionsUtils;

class authorizePermissionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($permission_id)
    {
        $this->permission_id = $permission_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $permission = \DB::table('hours_leave as hr')
                            ->join('cat_permission_tp as tp', 'tp.id_permission_tp', '=', 'hr.type_permission_id')
                            ->where('hr.id_hours_leave', $this->permission_id)
                            ->select(
                                'hr.*',
                                'tp.permission_tp_name',
                            )
                            ->first();

        $employee = \DB::table('users')
                        ->where('id', $permission->user_id)
                        ->first();

        $permission->start_date = dateUtils::formatDate($permission->start_date, 'D/m/Y dddd');
        $result = permissionsUtils::convertMinutesToHours($permission->minutes);
        $permission->time = $result[0].':'.$result[1].' hrs';

        $email = env('MAIL_FROM_ADDRESS');
        return $this->from($email)
                        ->subject('[PGH] Solicitud permiso horas '.$employee->short_name)
                        ->view('mails.authorizedPermissionMail')
                        ->with('permission', $permission)
                        ->with('employee', $employee)
                        ->with('sup_comments_n', $permission->sup_comments_n);
    }
}
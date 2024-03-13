<?php

namespace App\Mail;

use App\Utils\dateUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class cancelPermissionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($permission_id, $idEmployee, $idSuperviser)
    {
        $this->permission_id = $permission_id;
        $this->idEmployee = $idEmployee;
        $this->idSuperviser = $idSuperviser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $oPermission = \DB::table('hours_leave as h')
                            ->leftJoin('cat_permission_tp as pt', 'pt.id_permission_tp', '=', 'h.type_permission_id')
                            ->where('h.id_hours_leave', $this->permission_id)
                            ->select(
                                'h.*',
                                'pt.permission_tp_name'
                            )
                            ->first();

        $oPermission->type = 'PERMISO';
        $oPermission->type_name = 'PERMISO '.$oPermission->permission_tp_name;

        $oPermission->start_date = dateUtils::formatDate($oPermission->start_date, 'D-m-Y');
        $oPermission->end_date = dateUtils::formatDate($oPermission->end_date, 'D-m-Y');
        $oPermission->updated_at = dateUtils::formatDate($oPermission->updated_at, 'D-m-Y');

        $oEmployee = \DB::table('users')
                        ->where('id', $this->idEmployee)
                        ->first();

        $oSuperviser = \DB::table('users')
                        ->where('id', $this->idSuperviser)
                        ->first();

        $subject = '[Portal GH] Solicitud ' . $oPermission->permission_tp_name . ' cancelada';

        $email = "Portalgh@aeth.mx";
        return $this->from($email)
                    ->subject($subject)
                    ->view('mails.cancelMail')
                    ->with('oApplication', $oPermission)
                    ->with('oEmployee', $oEmployee)
                    ->with('oSuperviser', $oSuperviser);
    }
}

<?php

namespace App\Mail;

use App\Constants\SysConst;
use App\Utils\dateUtils;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class discardIncidentMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($idApplication, $idEmployee, $idSuperviser, $isPermission)
    {
        $this->idApplication = $idApplication;
        $this->idEmployee = $idEmployee;
        $this->idSuperviser = $idSuperviser;
        $this->isPermission = $isPermission;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->isPermission) {
            $oApplication = \DB::table('hours_leave as a')
                                ->leftJoin('cat_permission_cl as i', 'i.id_cl_permission', '=', 'a.cl_permission_id')
                                ->where('a.id_hours_leave', $this->idApplication)
                                ->select(
                                    'a.*',
                                    'a.start_date',
                                    'a.start_date as end_date',
                                    'i.cl_permission_name as type_name'
                                )
                                ->first();

            if ($oApplication) {
                $oApplication->type = 'PERMISO';
            }
        } else {
            $oApplication = \DB::table('applications as a')
                                ->leftJoin('cat_incidence_tps as i', 'i.id_incidence_tp', '=', 'a.type_incident_id')
                                ->where('a.id_application', $this->idApplication)
                                ->select(
                                    'a.*',
                                    'i.incidence_tp_name as type_name'
                                )
                                ->first();

            if ($oApplication) {
                if ($oApplication->type_incident_id == SysConst::TYPE_VACACIONES) {
                    $oApplication->type = 'VACACIONES';
                } else if ($oApplication->type_incident_id == SysConst::TYPE_CUMPLEAÑOS) {
                    $oApplication->type = 'CUMPLEAÑOS';
                } else {
                    $oApplication->type = 'INCIDENCIA';
                }
            }
        }

        $oApplication->start_date = dateUtils::formatDate($oApplication->start_date, 'D-m-Y');
        $oApplication->end_date = dateUtils::formatDate($oApplication->end_date, 'D-m-Y');
        $oApplication->updated_at = dateUtils::formatDate($oApplication->updated_at, 'D-m-Y');

        $oEmployee = \DB::table('users')
                        ->where('id', $this->idEmployee)
                        ->first();

        $oSuperviser = \DB::table('users')
                        ->where('id', $this->idSuperviser)
                        ->first();

        $subject = '[Portal GH] Solicitud ' . $oApplication->type_name . ' descartada';

        $email = "Portalgh@aeth.mx";
        return $this->from($email)
                    ->subject($subject)
                    ->view('mails.cancelMail')
                    ->with('oApplication', $oApplication)
                    ->with('oEmployee', $oEmployee)
                    ->with('oSuperviser', $oSuperviser);
    }
}

<?php

namespace App\Mail;

use App\Constants\SysConst;
use App\Utils\dateUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class discardIncidentGHMail extends Mailable
{
    use Queueable, SerializesModels;

    public $idApplication;
    public $idEmployee;
    public $idSuperviser;

    public function __construct($idApplication, $idEmployee, $idSuperviser)
    {
        $this->idApplication = $idApplication;
        $this->idEmployee = $idEmployee;
        $this->idSuperviser = $idSuperviser;
    }

    public function build()
    {
        $oApplication = \DB::table('applications as a')
                            ->leftJoin('cat_incidence_tps as i', 'i.id_incidence_tp', '=', 'a.type_incident_id')
                            ->where('a.id_application', $this->idApplication)
                            ->select('a.*', 'i.incidence_tp_name as type_name')
                            ->first();

        if ($oApplication->type_incident_id == SysConst::TYPE_VACACIONES) {
            $oApplication->type = 'VACACIONES';
        } else if ($oApplication->type_incident_id == SysConst::TYPE_CUMPLEAÑOS) {
            $oApplication->type = 'CUMPLEAÑOS';
        } else {
            $oApplication->type = 'INCIDENCIA';
        }

        $oApplication->start_date = dateUtils::formatDate($oApplication->start_date, 'D-m-Y');
        $oApplication->end_date = dateUtils::formatDate($oApplication->end_date, 'D-m-Y');
        $oApplication->updated_at = dateUtils::formatDate($oApplication->updated_at, 'D-m-Y');

        $oEmployee = \DB::table('users')->where('id', $this->idEmployee)->first();
        $oSuperviser = \DB::table('users')->where('id', $this->idSuperviser)->first();

        // Asunto enfocado en atención/revisión de descarte por GH
        $subject = '[Portal GH] REVISIÓN: Solicitud ' . $oApplication->type_name . ' descartada de ' . $oEmployee->full_name;

        return $this->from("Portalgh@aeth.mx")
                    ->subject($subject)
                    ->view('mails.discardIncidentGHMail')
                    ->with('oApplication', $oApplication)
                    ->with('oEmployee', $oEmployee)
                    ->with('oSuperviser', $oSuperviser);
    }
}
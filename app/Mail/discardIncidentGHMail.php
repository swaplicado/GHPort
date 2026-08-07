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
        // Intenta buscar en la tabla 'applications'
        $oApplication = \DB::table('applications as a')
                            ->leftJoin('cat_incidence_tps as i', 'i.id_incidence_tp', '=', 'a.type_incident_id')
                            ->where('a.id_application', $this->idApplication)
                            ->select('a.*', 'i.incidence_tp_name as type_name')
                            ->first();

        // Si es null, busca en la tabla 'hours_leave' (permisos)
        if (!$oApplication) {
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
            if ($oApplication->type_incident_id == SysConst::TYPE_VACACIONES) {
                $oApplication->type = 'VACACIONES';
            } else if ($oApplication->type_incident_id == SysConst::TYPE_CUMPLEAÑOS) {
                $oApplication->type = 'CUMPLEAÑOS';
            } else {
                $oApplication->type = 'INCIDENCIA';
            }
        }

        $oApplication->start_date = dateUtils::formatDate($oApplication->start_date, 'D-m-Y');
        $oApplication->end_date = dateUtils::formatDate($oApplication->end_date, 'D-m-Y');
        $oApplication->updated_at = dateUtils::formatDate($oApplication->updated_at, 'D-m-Y');

        $oEmployee = \DB::table('users')->where('id', $this->idEmployee)->first();
        $oSuperviser = \DB::table('users')->where('id', $this->idSuperviser)->first();

        $subject = '[Portal GH] Solicitud ' . $oApplication->type_name . ' descartada';

        return $this->from("Portalgh@aeth.mx")
                    ->subject($subject)
                    ->view('mails.cancelMail')
                    ->with('oApplication', $oApplication)
                    ->with('oEmployee', $oEmployee)
                    ->with('oSuperviser', $oSuperviser);
    }
}
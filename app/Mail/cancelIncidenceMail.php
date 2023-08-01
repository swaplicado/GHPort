<?php

namespace App\Mail;

use App\Utils\dateUtils;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class cancelIncidenceMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($idApplication, $idEmployee, $idSuperviser)
    {
        $this->idApplication = $idApplication;
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
        $oApplication = \DB::table('applications as a')
                            ->leftJoin('cat_incidence_tps as i', 'i.id_incidence_tp', '=', 'a.type_incident_id')
                            ->where('a.id_application', $this->idApplication)
                            ->select(
                                'a.*',
                                'i.incidence_tp_name as type_name'
                            )
                            ->first();

        $oApplication->start_date = dateUtils::formatDate($oApplication->start_date, 'D-m-Y');
        $oApplication->end_date = dateUtils::formatDate($oApplication->end_date, 'D-m-Y');
        $oApplication->updated_at = dateUtils::formatDate($oApplication->updated_at, 'D-m-Y');

        $oEmployee = \DB::table('users')
                        ->where('id', $this->idEmployee)
                        ->first();

        $oSuperviser = \DB::table('users')
                        ->where('id', $this->idSuperviser)
                        ->first();

        $subject = '[PGH] Solicitud ' . $oApplication->type_name . ' cancelada';

        $email = "Portalgh@aeth.mx";
        return $this->from($email)
                    ->subject($subject)
                    ->view('mails.cancelMail')
                    ->with('oApplication', $oApplication)
                    ->with('oEmployee', $oEmployee)
                    ->with('oSuperviser', $oSuperviser);
    }
}

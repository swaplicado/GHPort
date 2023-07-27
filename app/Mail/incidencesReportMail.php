<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class incidencesReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($lEmployees, $week, $ini, $end, $lOrgCharts, $sDate, $sDateHead)
    {
        $this->lEmployees = $lEmployees;
        $this->week = $week;
        $this->ini = $ini;
        $this->end = $end;
        $this->lOrgCharts = $lOrgCharts;
        $this->sDate = $sDate;
        $this->sDateHead = $sDateHead;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // $email = env('MAIL_FROM_ADDRESS');
        $email = "Portalgh@aeth.mx";
        return $this->from($email)
                        ->subject('[PGH] Reporte incidencias del '.$this->sDateHead)
                        ->view('mails.incidencesReportMailFormat2')
                        ->with('lEmployees', $this->lEmployees)
                        ->with('week', $this->week)
                        ->with('date_ini', $this->ini)
                        ->with('date_end', $this->end)
                        ->with('lOrgCharts', $this->lOrgCharts)
                        ->with('sDate', $this->sDate);
    }
}

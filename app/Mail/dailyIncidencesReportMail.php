<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class dailyIncidencesReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( 
        $lastWeeklOrgCharts,
        $actualWeeklOrgCharts,
        $nextWeeklOrgCharts,
        $lastWeek,
        $actualWeek,
        $nextWeek,
        $lastLogsExecutions,
        $actual_date,
        $lApplicationToExclude,
        $lEmployeesLastWeek,
        $lEmployeesWeek,
        $lEmployeesNextWeek,
        $lastSDate,
        $actualSDate,
        $nextSDate,
        $sDate
     )
    {
        $this->lastWeeklOrgCharts = $lastWeeklOrgCharts;
        $this->actualWeeklOrgCharts = $actualWeeklOrgCharts;
        $this->nextWeeklOrgCharts = $nextWeeklOrgCharts;
        $this->lastWeek = $lastWeek;
        $this->actualWeek = $actualWeek;
        $this->nextWeek = $nextWeek;
        $this->lastLogsExecutions = $lastLogsExecutions;
        $this->actual_date = $actual_date;
        $this->lApplicationToExclude = $lApplicationToExclude;
        $this->lEmployeesLastWeek = $lEmployeesLastWeek;
        $this->lEmployeesWeek = $lEmployeesWeek;
        $this->lEmployeesNextWeek = $lEmployeesNextWeek;
        $this->lastSDate = $lastSDate;
        $this->actualSDate = $actualSDate;
        $this->nextSDate = $nextSDate;
        $this->sDate = $sDate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // $email = "Portalgh@aeth.mx";
        $email = "adrian.aviles.swaplicado@gmail.com";
        return $this->from($email)
                        ->subject('[Portal GH] Reporte incidencias del '.$this->sDate)
                        ->view('mails.dailyIncidencesReportMail')
                        ->with('lastWeeklOrgCharts', $this->lastWeeklOrgCharts)
                        ->with('actualWeeklOrgCharts', $this->actualWeeklOrgCharts)
                        ->with('nextWeeklOrgCharts', $this->nextWeeklOrgCharts)
                        ->with('lastWeek', $this->lastWeek)
                        ->with('actualWeek', $this->actualWeek)
                        ->with('nextWeek', $this->nextWeek)
                        ->with('lastLogsExecutions', $this->lastLogsExecutions)
                        ->with('actual_date', $this->actual_date)
                        ->with('lApplicationToExclude', $this->lApplicationToExclude)
                        ->with('lEmployeesLastWeek', $this->lEmployeesLastWeek)
                        ->with('lEmployeesWeek', $this->lEmployeesWeek)
                        ->with('lEmployeesNextWeek', $this->lEmployeesNextWeek)
                        ->with('lastSDate', $this->lastSDate)
                        ->with('actualSDate', $this->actualSDate)
                        ->with('nextSDate', $this->nextSDate);
    }
}

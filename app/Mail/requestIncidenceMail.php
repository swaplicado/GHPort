<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Utils\dateUtils;

class requestIncidenceMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($application_id)
    {
        $this->application_id = $application_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $application = \DB::table('applications as ap')
                            ->join('cat_incidence_tps as tp', 'tp.id_incidence_tp', '=', 'ap.type_incident_id')
                            ->where('ap.id_application', $this->application_id)
                            ->select(
                                'ap.*',
                                'tp.incidence_tp_name',
                            )
                            ->first();

        $employee = \DB::table('users')
                        ->where('id', $application->user_id)
                        ->first();

        $application->start_date = dateUtils::formatDate($application->start_date, 'D/m/Y dddd');
        $application->end_date = dateUtils::formatDate($application->end_date, 'D/m/Y dddd');
        $application->return_date = dateUtils::formatDate($application->return_date, 'D/m/Y dddd');
        $lDays = json_decode($application->ldays);
        for ($i=0; $i < count($lDays); $i++) { 
            $lDays[$i]->date = dateUtils::formatDate($lDays[$i]->date, 'D/m/Y dddd');
        }

        $email = "Portalgh@aeth.mx";
        return $this->from($email)
                        ->subject('[PGH] Solicitud incidencia '.$employee->short_name.' '.$employee->first_name.' '.$employee->last_name)
                        ->view('mails.requestIncidenceMail')
                        ->with('application', $application)
                        ->with('employee', $employee)
                        ->with('lDays', $lDays)
                        ->with('returnDate', $application->return_date)
                        ->with('emp_comments_n', $application->emp_comments_n);
    }
}
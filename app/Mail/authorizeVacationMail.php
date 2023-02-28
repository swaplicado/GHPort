<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Utils\dateUtils;

class authorizeVacationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($id_application, $employee_id, $lDays, $returnDate)
    {
        $this->id_application = $id_application;
        $this->employee_id = $employee_id;
        $this->lDays = $lDays;
        $this->returnDate = $returnDate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $application = \DB::table('applications')
                            ->where('id_application', $this->id_application)
                            ->first();

        // $application->start_date = dateUtils::formatDate($application->start_date, 'ddd D-M-Y');
        // $application->end_date = dateUtils::formatDate($application->end_date, 'ddd D-M-Y');
        // $this->returnDate = dateUtils::formatDate($this->returnDate, 'ddd D-M-Y');
        $application->start_date = dateUtils::formatDate($application->start_date, 'D/m/Y dddd');
        $application->end_date = dateUtils::formatDate($application->end_date, 'D/m/Y dddd');
        $application->return_date = dateUtils::formatDate($application->return_date, 'D/m/Y dddd');
        $lDays = json_decode($application->ldays);
        for ($i=0; $i < count($lDays); $i++) { 
            $lDays[$i]->date = dateUtils::formatDate($lDays[$i]->date, 'D/m/Y dddd');
        }


        $employee = \DB::table('users')
                        ->where('id', $this->employee_id)
                        ->first();

        $email = env('MAIL_FROM_ADDRESS');
        return $this->from($email)
                        ->subject('[PGH] Solicitud vacaciones '.$employee->short_name)
                        ->view('mails.authorizedVacationMail')
                        ->with('application', $application)
                        ->with('employee', $employee)
                        ->with('lDays', $lDays)
                        ->with('returnDate', $application->return_date);
    }
}

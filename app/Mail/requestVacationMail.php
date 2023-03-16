<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Utils\dateUtils;

class requestVacationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($idApplication, $idEmployee, $lDays, $returnDate)
    {
        $this->idApplication = $idApplication;
        $this->idEmployee = $idEmployee;
        $this->lDays = $lDays; //Obsoleto ya no se usa
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
                            ->where('id_application', $this->idApplication)
                            ->first();

        // $application->start_date = dateUtils::formatDate($application->start_date, 'ddd D-M-Y');
        // $application->end_date = dateUtils::formatDate($application->end_date, 'ddd D-M-Y');
        $application->start_date = dateUtils::formatDate($application->start_date, 'D/m/Y dddd');
        $application->end_date = dateUtils::formatDate($application->end_date, 'D/m/Y dddd');
        $application->return_date = dateUtils::formatDate($application->return_date, 'D/m/Y dddd');
        $lDays = json_decode($application->ldays);
        for ($i=0; $i < count($lDays); $i++) { 
            $lDays[$i]->date = dateUtils::formatDate($lDays[$i]->date, 'D/m/Y dddd');
        }
        
        $employee = \DB::table('users')
                        ->where('id', $this->idEmployee)
                        ->first();

        $email = env('MAIL_FROM_ADDRESS');
        return $this->from($email)
                        ->subject('[PGH] Solicitud vacaciones '.$employee->short_name)
                        ->view('mails.requestVacationMail')
                        ->with('application', $application)
                        ->with('employee', $employee)
                        ->with('lDays', $lDays)
                        ->with('returnDate', $application->return_date)
                        ->with('emp_comments_n', $application->emp_comments_n);
    }
}
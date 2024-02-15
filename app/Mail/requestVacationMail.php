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
    public function __construct($idApplication, $idEmployee, $lDays, $returnDate, $superviser = null)
    {
        $this->idApplication = $idApplication;
        $this->idEmployee = $idEmployee;
        $this->lDays = $lDays;
        $this->returnDate = $returnDate;
        $this->superviser = $superviser;
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

        if(!is_null($this->superviser)){
            $subject = (!$this->superviser->is_delegation ? '[PGH] Solicitud vacaciones '.$employee->short_name.' '.$employee->first_name.' '.$employee->last_name : '[PGH] Solicitud vacaciones '.$employee->short_name.' '.$employee->first_name.' '.$employee->last_name.' (por delegación)');
            $is_delegation = $this->superviser->is_delegation;
            $delegated = $this->superviser->delegated;
            $delegation_start_date = $this->superviser->delegation_start_date;
            $delegation_end_date = $this->superviser->delegation_end_date;
        }else{
            $subject = '[PGH] Solicitud vacaciones '.$employee->short_name.' '.$employee->first_name.' '.$employee->last_name;
            $is_delegation = false;
            $delegated = null;
            $delegation_start_date = null;
            $delegation_end_date = null;
        }

        $email = "Portalgh@aeth.mx";
        return $this->from($email)
                        ->subject($subject)
                        ->view('mails.requestVacationMail')
                        ->with('application', $application)
                        ->with('employee', $employee)
                        ->with('lDays', $lDays)
                        ->with('returnDate', $application->return_date)
                        ->with('emp_comments_n', $application->emp_comments_n)
                        ->with('is_delegation', $is_delegation)
                        ->with('delegated', $delegated)
                        ->with('delegation_start_date', $delegation_start_date)
                        ->with('delegation_end_date', $delegation_end_date);
    }
}
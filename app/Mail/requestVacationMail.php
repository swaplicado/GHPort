<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
                            ->where('id_application', $this->idApplication)
                            ->first();

        $employee = \DB::table('users')
                        ->where('id', $this->idEmployee)
                        ->first();

        $email = env('MAIL_FROM_ADDRESS');
        return $this->from($email)
                        ->subject('Solicitud de vacaciones')
                        ->view('mails.requestVacationMail')
                        ->with('application', $application)
                        ->with('employee', $employee)
                        ->with('lDays', $this->lDays)
                        ->with('returnDate', $this->returnDate);
    }
}
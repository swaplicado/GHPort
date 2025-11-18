<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Utils\dateUtils;

class ModificationEventMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($idEvent, $idEmployee)
    {
        $this->idEvent = $idEvent;
        $this->idEmployee = $idEmployee;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $event = \DB::table('cat_events')
                            ->where('id_event', $this->idEvent)
                            ->first();

        $event->start_date = dateUtils::formatDate($event->start_date, 'D/m/Y dddd');
        $event->end_date = dateUtils::formatDate($event->end_date, 'D/m/Y dddd');
        $event->return_date = dateUtils::formatDate($event->return_date, 'D/m/Y dddd');
        $lDays = json_decode($event->ldays);
        for ($i=0; $i < count($lDays); $i++) { 
            $lDays[$i]->date = dateUtils::formatDate($lDays[$i]->date, 'D/m/Y dddd');
        }
        
        $employee = \DB::table('users')
                        ->where('id', $this->idEmployee)
                        ->first();

        $subject = '[Portal GH] Modificación del evento al que estás invitado '.$employee->short_name.' '.$employee->first_name.' '.$employee->last_name;

        $email = "Portalgh@aeth.mx";
        return $this->from($email)
                        ->subject($subject)
                        ->view('mails.modificationEventMail')
                        ->with('event', $event)
                        ->with('employee', $employee)
                        ->with('lDays', $lDays)
                        ->with('returnDate', $event->return_date);
    }
}
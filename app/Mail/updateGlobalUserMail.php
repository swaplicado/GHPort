<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class updateGlobalUserMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($oUser, $type)
    {
        $this->oUser = $oUser;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = "Portalgh@aeth.mx";
        $subject = "[Portal GH] Actualizacion de usuario";
        return $this->from($email)
                    ->subject($subject)
                    ->view('mails.updateGlobalUserMail')
                    ->with('oUser', $this->oUser)
                    ->with('type', $this->type);
        // return $this->view('view.name');
    }
}

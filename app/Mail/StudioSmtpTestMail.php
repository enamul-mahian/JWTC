<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class StudioSmtpTestMail extends Mailable
{
    public function build(): static
    {
        return $this->subject('JWTC SMTP Profile Connection Test')
            ->text('emails.studio_smtp_test');
    }
}

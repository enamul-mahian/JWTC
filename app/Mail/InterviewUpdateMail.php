<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class InterviewUpdateMail extends Mailable
{
    public function __construct(
        public string $subjectLine,
        public string $messageBody
    ) {
    }

    public function build(): static
    {
        return $this->subject($this->subjectLine)
            ->text('emails.interview_update_text');
    }
}

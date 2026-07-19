<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Application $application) {}

    public function build(): self
    {
        return $this->subject('Confirmation de votre inscription EDSP')
            ->view('emails.application-submitted');
    }
}

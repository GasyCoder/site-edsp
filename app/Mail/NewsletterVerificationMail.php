<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly NewsletterSubscriber $subscriber,
        public readonly string $verificationUrl,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Confirmez votre inscription à la newsletter de l’EDSP')
            ->view('emails.newsletter-verification');
    }
}

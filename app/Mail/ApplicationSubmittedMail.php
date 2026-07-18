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
        $name = e($this->application->first_name.' '.$this->application->last_name);
        $number = e($this->application->application_number);

        return $this->subject('Confirmation de votre préinscription EDSP')
            ->html("<p>Bonjour {$name},</p><p>Votre dossier de préinscription a bien été reçu.</p><p>Numéro de dossier : <strong>{$number}</strong></p><p>Conservez ce numéro pour vos échanges avec l’EDSP.</p>");
    }
}

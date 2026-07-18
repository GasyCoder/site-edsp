<?php

namespace App\Mail;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Application $application,
        public readonly string $status,
    ) {}

    public function build(): self
    {
        $name = e($this->application->first_name.' '.$this->application->last_name);
        $number = e($this->application->application_number);
        $status = e(ApplicationStatus::from($this->status)->label());

        return $this->subject('Mise à jour de votre dossier EDSP')
            ->html("<p>Bonjour {$name},</p><p>Le statut du dossier <strong>{$number}</strong> est désormais : <strong>{$status}</strong>.</p><p>Vous pouvez contacter l’EDSP si vous avez besoin de précisions.</p>");
    }
}

<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly ContactMessage $contactMessage) {}

    public function build(): self
    {
        $name = e(trim($this->contactMessage->first_name.' '.$this->contactMessage->last_name));
        $email = e($this->contactMessage->email);
        $subject = e($this->contactMessage->subject);
        $message = nl2br(e($this->contactMessage->message));

        return $this->subject('Nouveau message EDSP : '.$this->contactMessage->subject)
            ->html("<p><strong>{$name}</strong> ({$email}) a envoyé un message.</p><p>Sujet : {$subject}</p><p>{$message}</p>");
    }
}

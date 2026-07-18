<?php

namespace App\Jobs;

use App\Mail\ContactMessageReceivedMail;
use App\Models\ContactMessage;
use App\Services\SettingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendContactNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $contactMessageId) {}

    public function handle(SettingService $settings): void
    {
        $message = ContactMessage::query()->find($this->contactMessageId);
        $publicSettings = $settings->public();
        $recipient = $publicSettings['contact_email'] ?? $publicSettings['email'] ?? config('mail.from.address');

        if ($message && $recipient) {
            Mail::to($recipient)->send(new ContactMessageReceivedMail($message));
        }
    }
}

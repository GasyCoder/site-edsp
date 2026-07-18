<?php

namespace App\Jobs;

use App\Mail\NewsletterVerificationMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendNewsletterVerification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $subscriberId) {}

    public function handle(): void
    {
        $subscriber = NewsletterSubscriber::query()->find($this->subscriberId);

        if ($subscriber === null || ($subscriber->verified_at !== null && $subscriber->unsubscribed_at === null)) {
            return;
        }

        $verificationUrl = URL::temporarySignedRoute(
            'newsletter.verify',
            now()->addHours(48),
            ['subscriber' => $subscriber],
        );

        Mail::to($subscriber->email)->send(new NewsletterVerificationMail($subscriber, $verificationUrl));
    }
}

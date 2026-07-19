<?php

namespace App\Jobs;

use App\Mail\NewsletterCampaignMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendNewsletterDelivery implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public readonly int $deliveryId) {}

    public function handle(): void
    {
        $delivery = NewsletterDelivery::query()->with(['campaign', 'subscriber'])->find($this->deliveryId);

        if ($delivery === null || in_array($delivery->status, ['sent', 'failed', 'skipped'], true)) {
            return;
        }

        $delivery->increment('attempts');
        $campaign = $delivery->campaign;
        $subscriber = $delivery->subscriber;

        if ($campaign === null || $subscriber === null || $campaign->status !== 'sending') {
            return;
        }

        if ($subscriber->verified_at === null || $subscriber->unsubscribed_at !== null) {
            $delivery->update(['status' => 'skipped', 'error' => 'Adresse inactive ou désinscrite.']);
            $campaign->increment('skipped_count');
            $this->completeCampaignIfFinished($campaign);

            return;
        }

        $unsubscribeUrl = URL::temporarySignedRoute(
            'newsletter.unsubscribe',
            now()->addYears(5),
            ['subscriber' => $subscriber],
        );

        Mail::to($subscriber->email)->send(new NewsletterCampaignMail(
            $campaign,
            $subscriber,
            $unsubscribeUrl,
        ));

        $delivery->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
        $campaign->increment('delivered_count');
        $this->completeCampaignIfFinished($campaign);
    }

    public function failed(?Throwable $exception): void
    {
        $delivery = NewsletterDelivery::query()->with('campaign')->find($this->deliveryId);

        if ($delivery === null || in_array($delivery->status, ['sent', 'failed'], true)) {
            return;
        }

        $message = mb_substr($exception?->getMessage() ?? 'Échec inconnu', 0, 2000);
        $delivery->update(['status' => 'failed', 'error' => $message]);

        if ($delivery->campaign !== null) {
            $delivery->campaign->increment('failed_count');
            $delivery->campaign->update(['last_error' => $message]);
            $this->completeCampaignIfFinished($delivery->campaign);
        }
    }

    private function completeCampaignIfFinished(NewsletterCampaign $campaign): void
    {
        $campaign->refresh();

        if ($campaign->status !== 'sending') {
            return;
        }

        $processed = $campaign->delivered_count + $campaign->failed_count + $campaign->skipped_count;

        if ($processed < $campaign->recipient_count) {
            return;
        }

        $campaign->update([
            'status' => $campaign->delivered_count > 0 ? 'sent' : 'failed',
            'sent_at' => now(),
        ]);
    }
}

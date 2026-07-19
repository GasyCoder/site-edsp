<?php

namespace App\Services;

use App\Jobs\SendNewsletterDelivery;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DispatchNewsletterCampaign
{
    public function handle(NewsletterCampaign $campaign): NewsletterCampaign
    {
        $campaign = DB::transaction(function () use ($campaign): NewsletterCampaign {
            /** @var NewsletterCampaign $locked */
            $locked = NewsletterCampaign::query()->lockForUpdate()->findOrFail($campaign->getKey());

            if (! $locked->canBePrepared()) {
                throw ValidationException::withMessages([
                    'status' => 'Cette campagne est déjà en cours d’envoi ou terminée.',
                ]);
            }

            $alreadyDelivered = $locked->deliveries()->where('status', 'sent')->count();
            $locked->deliveries()->where('status', '!=', 'sent')->delete();
            $remainingRecipients = NewsletterSubscriber::query()
                ->active()
                ->whereNotIn('id', $locked->deliveries()->where('status', 'sent')->select('newsletter_subscriber_id'))
                ->count();

            $locked->update([
                'status' => 'sending',
                'started_at' => now(),
                'sent_at' => null,
                'recipient_count' => $alreadyDelivered + $remainingRecipients,
                'delivered_count' => $alreadyDelivered,
                'failed_count' => 0,
                'skipped_count' => 0,
                'last_error' => null,
            ]);

            return $locked;
        });

        $deliveredSubscriberIds = $campaign->deliveries()
            ->where('status', 'sent')
            ->pluck('newsletter_subscriber_id');

        NewsletterSubscriber::query()
            ->active()
            ->when($deliveredSubscriberIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $deliveredSubscriberIds))
            ->select('id')
            ->chunkById(500, function ($subscribers) use ($campaign): void {
                foreach ($subscribers as $subscriber) {
                    $delivery = NewsletterDelivery::query()->firstOrCreate([
                        'newsletter_campaign_id' => $campaign->id,
                        'newsletter_subscriber_id' => $subscriber->id,
                    ], ['status' => 'queued']);

                    SendNewsletterDelivery::dispatch($delivery->id);
                }
            });

        if ($campaign->recipient_count === 0) {
            $campaign->update(['status' => 'sent', 'sent_at' => now()]);
        }

        return $campaign->fresh();
    }
}

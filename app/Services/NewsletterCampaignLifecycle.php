<?php

namespace App\Services;

use App\Models\NewsletterCampaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NewsletterCampaignLifecycle
{
    public function __construct(private readonly DispatchNewsletterCampaign $dispatcher) {}

    public function pause(NewsletterCampaign $campaign, string $reason, ?int $actorId): NewsletterCampaign
    {
        return DB::transaction(function () use ($campaign, $reason, $actorId): NewsletterCampaign {
            $locked = $this->lock($campaign);
            $this->ensureStatus($locked, ['scheduled', 'sending'], 'Seule une campagne programmée ou en cours peut être suspendue.');

            $locked->update([
                'status' => 'paused',
                'paused_at' => now(),
                'status_reason' => $reason,
                'status_changed_by' => $actorId,
            ]);

            return $locked->fresh();
        });
    }

    public function resume(NewsletterCampaign $campaign, ?int $actorId): NewsletterCampaign
    {
        $campaign->refresh();
        $this->ensureStatus($campaign, ['paused'], 'Cette campagne n’est pas suspendue.');

        if ($campaign->started_at === null && $campaign->scheduled_at?->isFuture()) {
            $campaign->update([
                'status' => 'scheduled',
                'paused_at' => null,
                'status_reason' => null,
                'status_changed_by' => $actorId,
            ]);

            return $campaign->fresh();
        }

        $campaign->update([
            'paused_at' => null,
            'status_reason' => null,
            'status_changed_by' => $actorId,
        ]);

        return $this->dispatcher->handle($campaign);
    }

    public function cancel(NewsletterCampaign $campaign, string $reason, ?int $actorId): NewsletterCampaign
    {
        return DB::transaction(function () use ($campaign, $reason, $actorId): NewsletterCampaign {
            $locked = $this->lock($campaign);
            $this->ensureStatus(
                $locked,
                ['draft', 'scheduled', 'sending', 'paused', 'failed'],
                'Cette campagne ne peut plus être annulée.',
            );

            $locked->deliveries()
                ->where('status', 'queued')
                ->update(['status' => 'skipped', 'error' => 'Campagne annulée avant l’envoi.']);

            $locked->update([
                'status' => 'cancelled',
                'scheduled_at' => null,
                'paused_at' => null,
                'cancelled_at' => now(),
                'status_reason' => $reason,
                'status_changed_by' => $actorId,
                'skipped_count' => $locked->deliveries()->where('status', 'skipped')->count(),
            ]);

            return $locked->fresh();
        });
    }

    public function createEventCancellationNotice(
        NewsletterCampaign $campaign,
        string $reason,
        ?int $actorId,
    ): NewsletterCampaign {
        return DB::transaction(function () use ($campaign, $reason, $actorId): NewsletterCampaign {
            $locked = $this->lock($campaign);

            if ($locked->type !== 'event' || $locked->status !== 'sent') {
                throw ValidationException::withMessages([
                    'status' => 'Un avis d’annulation peut être créé uniquement pour un événement déjà envoyé.',
                ]);
            }

            $safeReason = nl2br(e($reason));

            $notice = NewsletterCampaign::query()->create([
                'type' => 'event_cancellation',
                'title' => 'Annulation — '.$locked->title,
                'subject' => 'Annulation : '.$locked->subject,
                'preheader' => 'Cet événement est annulé. Consultez les informations importantes.',
                'content' => '<p><strong>L’événement « '.e($locked->title).' » est annulé.</strong></p><p>'.$safeReason.'</p>',
                'event_starts_at' => $locked->event_starts_at,
                'event_location' => $locked->event_location,
                'external_url' => $locked->external_url,
                'external_url_label' => $locked->external_url_label,
                'status' => 'draft',
                'created_by' => $actorId,
            ]);

            $locked->update([
                'status' => 'event_cancelled',
                'cancelled_at' => now(),
                'status_reason' => $reason,
                'status_changed_by' => $actorId,
            ]);

            return $notice;
        });
    }

    private function lock(NewsletterCampaign $campaign): NewsletterCampaign
    {
        return NewsletterCampaign::query()->lockForUpdate()->findOrFail($campaign->getKey());
    }

    /** @param list<string> $allowedStatuses */
    private function ensureStatus(NewsletterCampaign $campaign, array $allowedStatuses, string $message): void
    {
        if (in_array($campaign->status, $allowedStatuses, true)) {
            return;
        }

        throw ValidationException::withMessages(['status' => $message]);
    }
}

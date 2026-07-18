<?php

namespace App\Services;

use App\Models\AdmissionCampaign;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Page;
use App\Models\Program;

final class PublicationService
{
    public function __construct(private readonly ContentRevisionService $revisions) {}

    public function publishDue(): int
    {
        $published = 0;

        foreach ([Page::class, News::class, Program::class, Gallery::class] as $model) {
            $model::query()
                ->where('status', 'scheduled')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->eachById(function ($content) use (&$published): void {
                    $this->revisions->update($content, ['status' => 'published'], action: 'published');
                    $published++;
                });
        }

        AdmissionCampaign::query()
            ->where('status', 'scheduled')
            ->where('opens_at', '<=', now())
            ->eachById(function (AdmissionCampaign $campaign) use (&$published): void {
                $this->revisions->update($campaign, ['status' => 'published'], action: 'published');
                $published++;
            });

        return $published;
    }
}

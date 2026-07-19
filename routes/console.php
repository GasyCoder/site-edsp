<?php

use App\Jobs\OptimizeMediaImage;
use App\Models\Media;
use App\Models\NewsletterCampaign;
use App\Services\DispatchNewsletterCampaign;
use App\Services\PublicationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('media:optimize', function (): void {
    $queued = 0;

    Media::query()
        ->where('mime_type', 'like', 'image/%')
        ->eachById(function (Media $media) use (&$queued): void {
            OptimizeMediaImage::dispatch($media->id);
            $queued++;
        });

    $this->info($queued.' image(s) ajoutée(s) à la file d’optimisation.');
})->purpose('Générer ou régénérer les variantes WebP des médias image');

Schedule::call(fn () => app(PublicationService::class)->publishDue())
    ->name('publish-scheduled-content')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::call(function (): void {
    NewsletterCampaign::query()
        ->due()
        ->select('id')
        ->chunkById(50, function ($campaigns): void {
            foreach ($campaigns as $campaign) {
                app(DispatchNewsletterCampaign::class)->handle($campaign);
            }
        });
})->name('dispatch-scheduled-newsletters')->everyMinute()->withoutOverlapping();

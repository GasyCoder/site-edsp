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

Artisan::command('media:optimize {--force : Régénérer toutes les variantes} {--queue : Utiliser la file d’attente}', function (): int {
    $query = Media::query()->where('mime_type', 'like', 'image/%');

    if (! $this->option('force')) {
        $query->where(function ($query): void {
            $query->whereNull('optimized_path')->orWhereNull('thumbnail_path');
        });
    }

    $total = (clone $query)->count();
    $processed = 0;
    $failed = 0;

    if ($total === 0) {
        $this->info('Toutes les images possèdent déjà leurs variantes WebP.');

        return 0;
    }

    $bar = $this->output->createProgressBar($total);
    $bar->start();

    $query->eachById(function (Media $media) use (&$processed, &$failed, $bar): void {
        try {
            if ($this->option('queue')) {
                OptimizeMediaImage::dispatch($media->id);
            } else {
                OptimizeMediaImage::dispatchSync($media->id);
            }

            $processed++;
        } catch (Throwable $exception) {
            report($exception);
            $failed++;
        } finally {
            $bar->advance();
        }
    });

    $bar->finish();
    $this->newLine(2);
    $mode = $this->option('queue') ? 'mise(s) en file' : 'optimisée(s)';
    $this->info("{$processed} image(s) {$mode}.");

    if ($failed > 0) {
        $this->error("{$failed} image(s) n’ont pas pu être traitées.");

        return 1;
    }

    return 0;
})->purpose('Générer les variantes WebP manquantes sans dépendre d’un worker');

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

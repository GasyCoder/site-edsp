<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ContactMessage;
use App\Models\Document;
use App\Models\Media;
use App\Models\News;
use App\Models\Program;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class EdspStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = [];
        $user = auth()->user();

        if ($user?->can('view news')) {
            $stats[] = Stat::make('Actualités publiées', News::published()->count())
                ->description(News::query()->where('status', 'draft')->count().' brouillon(s)')
                ->color('primary');
        }

        if ($user?->can('view programs')) {
            $stats[] = Stat::make('Formations actives', Program::published()->count())
                ->color('success');
        }

        if ($user?->can('view applications')) {
            $stats[] = Stat::make('Inscriptions reçues', Application::query()->count())
                ->description(Application::query()->whereIn('status', ['submitted', 'under_review', 'incomplete'])->count().' à traiter')
                ->color('warning');
        }

        if ($user?->can('view contacts')) {
            $stats[] = Stat::make('Messages non lus', ContactMessage::query()->whereNull('read_at')->count())
                ->description(ContactMessage::query()->whereNull('handled_at')->count().' non traité(s)')
                ->color('danger');
        }

        if ($user?->can('view media')) {
            $storageBytes = (int) Media::query()->sum('size')
                + (int) Document::query()->sum('size')
                + (int) ApplicationDocument::query()->sum('size');
            $fileCount = Media::query()->count()
                + Document::query()->count()
                + ApplicationDocument::query()->count();

            $stats[] = Stat::make('Stockage utilisé', Number::fileSize($storageBytes))
                ->description($fileCount.' fichier(s), publics et privés')
                ->color('gray');
        }

        return $stats;
    }

    public static function canView(): bool
    {
        return auth()->user()?->canAny(['view news', 'view programs', 'view applications', 'view contacts', 'view media']) ?? false;
    }
}

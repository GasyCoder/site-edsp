<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Enums\ApplicationStatus;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ManageApplications extends ManageRecords
{
    protected static string $resource = ApplicationResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    #[Url(as: 'view')]
    public string $viewMode = 'list';

    public function setViewMode(string $viewMode): void
    {
        if (! in_array($viewMode, ['grid', 'list'], true)) {
            return;
        }

        $this->viewMode = $viewMode;
    }

    public function getHeading(): string
    {
        return 'Dossiers d’inscription';
    }

    public function getSubheading(): string
    {
        return 'Consultez, filtrez et traitez les candidatures reçues depuis le formulaire public.';
    }

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous')
                ->icon(Heroicon::OutlinedInboxStack)
                ->badge(fn (): int => Application::query()->count())
                ->badgeColor('gray'),
            'submitted' => Tab::make('Nouveaux')
                ->icon(Heroicon::OutlinedInboxArrowDown)
                ->badge(fn (): int => Application::query()->where('status', ApplicationStatus::Submitted)->count())
                ->badgeColor('info')
                ->query(fn (Builder $query): Builder => $query->where('status', ApplicationStatus::Submitted)),
            'processing' => Tab::make('À traiter')
                ->icon(Heroicon::OutlinedQueueList)
                ->badge(fn (): int => Application::query()->whereIn('status', [
                    ApplicationStatus::UnderReview,
                    ApplicationStatus::Incomplete,
                    ApplicationStatus::Eligible,
                    ApplicationStatus::Waitlisted,
                ])->count())
                ->badgeColor('warning')
                ->query(fn (Builder $query): Builder => $query->whereIn('status', [
                    ApplicationStatus::UnderReview,
                    ApplicationStatus::Incomplete,
                    ApplicationStatus::Eligible,
                    ApplicationStatus::Waitlisted,
                ])),
            'accepted' => Tab::make('Acceptés')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->badge(fn (): int => Application::query()->where('status', ApplicationStatus::Accepted)->count())
                ->badgeColor('success')
                ->query(fn (Builder $query): Builder => $query->where('status', ApplicationStatus::Accepted)),
            'rejected' => Tab::make('Refusés')
                ->icon(Heroicon::OutlinedXCircle)
                ->badge(fn (): int => Application::query()->where('status', ApplicationStatus::Rejected)->count())
                ->badgeColor('danger')
                ->query(fn (Builder $query): Builder => $query->where('status', ApplicationStatus::Rejected)),
            'archived' => Tab::make('Archivés')
                ->icon(Heroicon::OutlinedArchiveBox)
                ->badge(fn (): int => Application::query()->where('status', ApplicationStatus::Archived)->count())
                ->badgeColor('gray')
                ->query(fn (Builder $query): Builder => $query->where('status', ApplicationStatus::Archived)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

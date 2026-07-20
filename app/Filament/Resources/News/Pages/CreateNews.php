<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\News\NewsResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public static bool $formActionsAreSticky = true;

    public function getHeading(): string
    {
        return 'Créer une actualité';
    }

    public function getSubheading(): string
    {
        return 'Rédigez l’article, configurez sa publication puis complétez son référencement.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour aux actualités')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(NewsResource::getUrl('index')),
        ];
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author_id'] ??= auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return NewsResource::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Actualité créée';
    }
}

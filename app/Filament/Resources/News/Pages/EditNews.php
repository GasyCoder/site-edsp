<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\News\NewsResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class EditNews extends EditRecord
{
    use TracksContentRevisions;

    protected static string $resource = NewsResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public static bool $formActionsAreSticky = true;

    public function getHeading(): string
    {
        return 'Modifier : '.$this->getRecord()->title;
    }

    public function getSubheading(): string
    {
        return 'Mettez à jour le contenu, la publication, les médias et le référencement depuis cette page.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour aux actualités')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(NewsResource::getUrl('index')),
            Action::make('publicPage')
                ->label('Voir sur le site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): string => route('news.show', $this->getRecord()->slug))
                ->openUrlInNewTab()
                ->visible(function (): bool {
                    $status = $this->getRecord()->status;

                    return ($status instanceof BackedEnum ? $status->value : (string) $status) === 'published';
                }),
            DeleteAction::make()
                ->label('Supprimer'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Actualité mise à jour';
    }
}

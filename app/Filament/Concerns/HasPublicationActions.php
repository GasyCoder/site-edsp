<?php

namespace App\Filament\Concerns;

use App\Actions\ArchiveContent;
use App\Actions\PublishContent;
use App\Models\News;
use App\Models\Page;
use App\Models\Program;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

trait HasPublicationActions
{
    /** @return array<string, string> */
    protected static function publicationStatusOptions(): array
    {
        $options = [
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'scheduled' => 'Programmé',
            'published' => 'Publié',
            'archived' => 'Archivé',
        ];

        return static::canManagePublicationStatus()
            ? $options
            : array_intersect_key($options, array_flip(['draft', 'pending']));
    }

    protected static function canManagePublicationStatus(): bool
    {
        return auth()->user()?->can(static::publicationPermission()) ?? false;
    }

    private static function publicationPermission(): string
    {
        return match (static::getModel()) {
            Page::class => 'publish pages',
            News::class => 'publish news',
            Program::class => 'publish programs',
            default => throw new \LogicException('Aucune permission de publication définie pour cette ressource.'),
        };
    }

    /**
     * @return array<Action>
     */
    protected static function publicationActions(): array
    {
        return [
            Action::make('publish')
                ->label('Publier')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->iconButton()
                ->tooltip('Publier')
                ->color('success')
                ->authorize('publish')
                ->visible(fn (Model $record): bool => static::contentStatus($record) !== 'published')
                ->requiresConfirmation()
                ->action(function (Model $record): void {
                    app(PublishContent::class)->handle($record, auth()->id());

                    Notification::make()->title('Contenu publié')->success()->send();
                }),
            Action::make('archive')
                ->label('Archiver')
                ->icon(Heroicon::OutlinedArchiveBox)
                ->iconButton()
                ->tooltip('Archiver')
                ->color('warning')
                ->authorize('publish')
                ->visible(fn (Model $record): bool => static::contentStatus($record) !== 'archived')
                ->requiresConfirmation()
                ->action(function (Model $record): void {
                    app(ArchiveContent::class)->handle($record, auth()->id());

                    Notification::make()->title('Contenu archivé')->success()->send();
                }),
        ];
    }

    private static function contentStatus(Model $record): string
    {
        $status = $record->getAttribute('status');

        return $status instanceof BackedEnum ? (string) $status->value : (string) $status;
    }
}

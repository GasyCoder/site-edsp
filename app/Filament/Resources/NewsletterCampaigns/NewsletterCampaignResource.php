<?php

namespace App\Filament\Resources\NewsletterCampaigns;

use App\Filament\Resources\NewsletterCampaigns\Pages\CreateNewsletterCampaign;
use App\Filament\Resources\NewsletterCampaigns\Pages\EditNewsletterCampaign;
use App\Filament\Resources\NewsletterCampaigns\Pages\ListNewsletterCampaigns;
use App\Filament\Resources\NewsletterCampaigns\Pages\ViewNewsletterCampaign;
use App\Models\News;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Rules\SafeUrl;
use App\Services\DispatchNewsletterCampaign;
use App\Services\NewsletterCampaignLifecycle;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
use UnitEnum;

class NewsletterCampaignResource extends Resource
{
    protected static ?string $model = NewsletterCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Campagnes newsletter';

    protected static ?string $modelLabel = 'campagne newsletter';

    protected static ?string $pluralModelLabel = 'campagnes newsletter';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make('Message')
                    ->description('Préparez un contenu clair et concis pour les abonnés de l’EDSP.')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->schema([
                        Select::make('type')
                            ->label('Type de communication')
                            ->options([
                                'news' => 'Actualité',
                                'event' => 'Événement',
                                'event_cancellation' => 'Avis d’annulation',
                                'message' => 'Message public',
                            ])
                            ->default('message')
                            ->required()
                            ->live(),
                        Select::make('news_id')
                            ->label('Actualité source')
                            ->relationship('news', 'title')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => $get->string('type') === 'news')
                            ->live()
                            ->afterStateUpdated(function (mixed $state, Set $set): void {
                                $news = News::query()->find($state);

                                if ($news === null) {
                                    return;
                                }

                                $set('title', $news->title);
                                $set('subject', $news->title.' — EDSP');
                                $set('preheader', Str::limit($news->excerpt, 150));
                                $set('content', $news->content);
                                $set('external_url', route('news.show', $news->slug));
                                $set('external_url_label', 'Lire l’actualité');
                            }),
                        TextInput::make('title')
                            ->label('Titre interne et titre du message')
                            ->required()
                            ->maxLength(180),
                        TextInput::make('subject')
                            ->label('Objet de l’e-mail')
                            ->required()
                            ->maxLength(180),
                        TextInput::make('preheader')
                            ->label('Texte d’aperçu')
                            ->helperText('Court résumé visible à côté de l’objet dans la boîte de réception.')
                            ->maxLength(200),
                        RichEditor::make('content')
                            ->label('Contenu')
                            ->required()
                            ->extraInputAttributes(['style' => 'min-height:24rem'])
                            ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? ''))
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(['default' => 12, 'xl' => 8]),
                Grid::make(1)
                    ->schema([
                        Section::make('Diffusion')
                            ->icon(Heroicon::OutlinedPaperAirplane)
                            ->schema([
                                TextEntry::make('status_summary')
                                    ->label('Destinataires actuels')
                                    ->state(fn (): string => NewsletterSubscriber::query()->active()->count().' abonné(s) vérifié(s)'),
                                TextEntry::make('campaign_status_summary')
                                    ->label('Statut')
                                    ->state(fn (?NewsletterCampaign $record): string => self::statusLabel($record?->status ?? 'draft'))
                                    ->badge()
                                    ->color(fn (?NewsletterCampaign $record): string => self::statusColor($record?->status ?? 'draft')),
                                Textarea::make('delivery_notice')
                                    ->label('Fonctionnement')
                                    ->default('Enregistrez d’abord le brouillon. Vous pourrez ensuite l’envoyer immédiatement ou programmer sa diffusion depuis les actions de la page.')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->rows(4),
                            ]),
                        Section::make('Événement')
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->visible(fn (Get $get): bool => in_array($get->string('type'), ['event', 'event_cancellation'], true))
                            ->schema([
                                DateTimePicker::make('event_starts_at')
                                    ->label('Date et heure')
                                    ->seconds(false)
                                    ->required(fn (Get $get): bool => $get->string('type') === 'event'),
                                TextInput::make('event_location')
                                    ->label('Lieu ou modalité')
                                    ->placeholder('Amphithéâtre, visioconférence…')
                                    ->maxLength(180),
                            ]),
                        Section::make('Lien et pièce jointe')
                            ->icon(Heroicon::OutlinedPaperClip)
                            ->schema([
                                TextInput::make('external_url')
                                    ->label('Lien externe')
                                    ->placeholder('https://…')
                                    ->rules([new SafeUrl(allowRelative: false)])
                                    ->maxLength(2048),
                                TextInput::make('external_url_label')
                                    ->label('Texte du bouton')
                                    ->placeholder('En savoir plus')
                                    ->maxLength(100),
                                FileUpload::make('attachment_path')
                                    ->label('Pièce jointe')
                                    ->disk('private')
                                    ->directory('newsletter/attachments')
                                    ->visibility('private')
                                    ->storeFileNamesIn('attachment_name')
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                    ])
                                    ->maxSize(10240)
                                    ->helperText('PDF, DOC, DOCX, JPEG, PNG ou WebP — 10 Mo maximum.'),
                                Hidden::make('attachment_disk')->default('private'),
                            ]),
                    ])
                    ->columnSpan(['default' => 12, 'xl' => 4]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make('Résumé de la diffusion')
                    ->schema([
                        TextEntry::make('type')->label('Type')->formatStateUsing(fn (string $state): string => self::typeLabel($state))->badge(),
                        TextEntry::make('status')->label('Statut')->formatStateUsing(fn (string $state): string => self::statusLabel($state))->badge()->color(fn (string $state): string => self::statusColor($state)),
                        TextEntry::make('subject')->label('Objet')->weight(FontWeight::SemiBold),
                        TextEntry::make('scheduled_at')->label('Programmée le')->dateTime('d/m/Y à H:i')->placeholder('Non programmée'),
                        TextEntry::make('recipient_count')->label('Destinataires')->numeric(),
                        TextEntry::make('delivered_count')->label('Envoyés')->numeric()->color('success'),
                        TextEntry::make('failed_count')->label('Échecs')->numeric()->color('danger'),
                        TextEntry::make('skipped_count')->label('Ignorés')->numeric()->color('gray'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Section::make('Aperçu du message')
                    ->schema([
                        TextEntry::make('title')->label('Titre')->weight(FontWeight::Bold),
                        TextEntry::make('preheader')->label('Texte d’aperçu')->placeholder('—'),
                        TextEntry::make('event_starts_at')->label('Date de l’événement')->dateTime('d/m/Y à H:i')->placeholder('—'),
                        TextEntry::make('event_location')->label('Lieu')->placeholder('—'),
                        TextEntry::make('content')->label('Contenu')->html()->prose()->columnSpanFull(),
                        TextEntry::make('external_url')->label('Lien')->url(fn (?string $state): ?string => $state)->openUrlInNewTab()->placeholder('—'),
                        TextEntry::make('attachment_name')
                            ->label('Pièce jointe')
                            ->icon(Heroicon::OutlinedPaperClip)
                            ->url(fn (NewsletterCampaign $record): ?string => filled($record->attachment_path)
                                ? route('newsletter-campaigns.attachment.download', $record)
                                : null)
                            ->placeholder('Aucune'),
                    ])
                    ->columns(2)
                    ->columnSpan(['default' => 12, 'xl' => 8]),
                Section::make('Informations techniques')
                    ->schema([
                        TextEntry::make('creator.name')->label('Créée par')->placeholder('Système'),
                        TextEntry::make('created_at')->label('Créée le')->dateTime('d/m/Y à H:i'),
                        TextEntry::make('started_at')->label('Envoi commencé le')->dateTime('d/m/Y à H:i')->placeholder('—'),
                        TextEntry::make('sent_at')->label('Terminé le')->dateTime('d/m/Y à H:i')->placeholder('—'),
                        TextEntry::make('paused_at')->label('Suspendue le')->dateTime('d/m/Y à H:i')->placeholder('—'),
                        TextEntry::make('cancelled_at')->label('Annulation enregistrée le')->dateTime('d/m/Y à H:i')->placeholder('—'),
                        TextEntry::make('statusChangedBy.name')->label('Statut modifié par')->placeholder('—'),
                        TextEntry::make('status_reason')->label('Motif de suspension ou d’annulation')->placeholder('Aucun')->columnSpanFull(),
                        TextEntry::make('last_error')->label('Dernière erreur')->placeholder('Aucune')->color('danger'),
                    ])
                    ->columnSpan(['default' => 12, 'xl' => 4]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Campagne')
                    ->description(fn (NewsletterCampaign $record): string => $record->subject)
                    ->searchable(['title', 'subject'])
                    ->sortable()
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::typeLabel($state)),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusLabel($state))
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->label('Programmation')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Envoi manuel')
                    ->sortable(),
                TextColumn::make('progress')
                    ->label('Résultats')
                    ->state(fn (NewsletterCampaign $record): string => $record->delivered_count.' / '.$record->recipient_count)
                    ->description(fn (NewsletterCampaign $record): string => $record->failed_count > 0
                        ? $record->failed_count.' échec(s)'
                        : 'Aucun échec'),
                TextColumn::make('created_at')->label('Créée le')->dateTime('d/m/Y H:i')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->options([
                    'news' => 'Actualité',
                    'event' => 'Événement',
                    'event_cancellation' => 'Avis d’annulation',
                    'message' => 'Message public',
                ]),
                SelectFilter::make('status')->options([
                    'draft' => 'Brouillon',
                    'scheduled' => 'Programmée',
                    'sending' => 'En cours',
                    'sent' => 'Envoyée',
                    'failed' => 'Échec',
                    'paused' => 'Suspendue',
                    'cancelled' => 'Envoi annulé',
                    'event_cancelled' => 'Événement annulé',
                ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->visible(fn (NewsletterCampaign $record): bool => $record->canBePrepared()),
                self::sendAction()
                    ->iconButton()
                    ->tooltip('Envoyer maintenant'),
                self::scheduleAction()
                    ->iconButton()
                    ->tooltip('Programmer l’envoi'),
                self::pauseAction()
                    ->iconButton()
                    ->tooltip('Suspendre la campagne'),
                self::resumeAction()
                    ->iconButton()
                    ->tooltip('Reprendre la campagne'),
                self::reopenAction()
                    ->iconButton()
                    ->tooltip('Réouvrir en brouillon'),
                self::cancelAction()
                    ->iconButton()
                    ->tooltip('Annuler la campagne'),
                self::announceEventCancellationAction()
                    ->iconButton()
                    ->tooltip('Préparer un avis d’annulation'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('pause_selected')
                        ->label('Suspendre les campagnes')
                        ->icon(Heroicon::OutlinedPauseCircle)
                        ->color('warning')
                        ->authorize(fn (): bool => auth()->user()?->can('send newsletter campaigns') ?? false)
                        ->requiresConfirmation()
                        ->modalHeading('Suspendre les campagnes sélectionnées ?')
                        ->modalDescription('Seules les campagnes programmées ou en cours seront suspendues. Les autres resteront inchangées.')
                        ->schema([
                            Textarea::make('reason')
                                ->label('Motif commun de la suspension')
                                ->required()
                                ->maxLength(1000)
                                ->rows(4),
                        ])
                        ->modalSubmitActionLabel('Suspendre les campagnes')
                        ->action(function (Collection $records, array $data, NewsletterCampaignLifecycle $lifecycle): void {
                            $campaigns = $records->filter(
                                fn (NewsletterCampaign $record): bool => in_array($record->status, ['scheduled', 'sending'], true),
                            );

                            $campaigns->each(fn (NewsletterCampaign $record) => $lifecycle->pause(
                                $record,
                                $data['reason'],
                                auth()->id(),
                            ));

                            Notification::make()
                                ->success()
                                ->title($campaigns->count().' campagne(s) suspendue(s)')
                                ->body('Les campagnes incompatibles ont été laissées inchangées.')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('resume_selected')
                        ->label('Reprendre les campagnes')
                        ->icon(Heroicon::OutlinedPlayCircle)
                        ->color('success')
                        ->authorize(fn (): bool => auth()->user()?->can('send newsletter campaigns') ?? false)
                        ->requiresConfirmation()
                        ->modalHeading('Reprendre les campagnes sélectionnées ?')
                        ->modalDescription('Seules les campagnes suspendues seront reprises. Une programmation encore future sera conservée.')
                        ->modalSubmitActionLabel('Reprendre les campagnes')
                        ->action(function (Collection $records, NewsletterCampaignLifecycle $lifecycle): void {
                            $campaigns = $records->where('status', 'paused');

                            $campaigns->each(fn (NewsletterCampaign $record) => $lifecycle->resume($record, auth()->id()));

                            Notification::make()
                                ->success()
                                ->title($campaigns->count().' campagne(s) reprise(s)')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('reopen_selected')
                        ->label('Réouvrir en brouillon')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('info')
                        ->authorize(fn (): bool => auth()->user()?->can('send newsletter campaigns') ?? false)
                        ->requiresConfirmation()
                        ->modalHeading('Réouvrir les envois annulés ?')
                        ->modalDescription('Seuls les envois annulés redeviendront des brouillons modifiables.')
                        ->modalSubmitActionLabel('Réouvrir en brouillon')
                        ->action(function (Collection $records): void {
                            $campaigns = $records->where('status', 'cancelled');

                            $campaigns->each(fn (NewsletterCampaign $record) => $record->update([
                                'status' => 'draft',
                                'cancelled_at' => null,
                                'status_reason' => null,
                                'status_changed_by' => auth()->id(),
                            ]));

                            Notification::make()
                                ->success()
                                ->title($campaigns->count().' campagne(s) rouverte(s)')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('cancel_selected')
                        ->label('Annuler les campagnes')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->color('danger')
                        ->authorize(fn (): bool => auth()->user()?->can('send newsletter campaigns') ?? false)
                        ->requiresConfirmation()
                        ->modalHeading('Annuler les campagnes sélectionnées ?')
                        ->modalDescription('Seules les campagnes pouvant encore être arrêtées seront annulées. Les e-mails déjà reçus ne peuvent pas être rappelés.')
                        ->schema([
                            Textarea::make('reason')
                                ->label('Motif commun de l’annulation')
                                ->required()
                                ->maxLength(1000)
                                ->rows(4),
                        ])
                        ->modalSubmitActionLabel('Annuler les campagnes')
                        ->action(function (Collection $records, array $data, NewsletterCampaignLifecycle $lifecycle): void {
                            $campaigns = $records->filter(
                                fn (NewsletterCampaign $record): bool => in_array($record->status, ['draft', 'scheduled', 'sending', 'paused', 'failed'], true),
                            );

                            $campaigns->each(fn (NewsletterCampaign $record) => $lifecycle->cancel(
                                $record,
                                $data['reason'],
                                auth()->id(),
                            ));

                            Notification::make()
                                ->success()
                                ->title($campaigns->count().' campagne(s) annulée(s)')
                                ->body('Les campagnes déjà terminées ont été laissées inchangées.')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->label('Supprimer les campagnes')
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function sendAction(): Action
    {
        return Action::make('send_now')
            ->label('Envoyer maintenant')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('success')
            ->authorize(fn (NewsletterCampaign $record): bool => auth()->user()?->can('send', $record) ?? false)
            ->visible(fn (NewsletterCampaign $record): bool => in_array($record->status, ['draft', 'scheduled', 'failed'], true))
            ->requiresConfirmation()
            ->modalHeading('Envoyer cette campagne maintenant ?')
            ->modalDescription(fn (): string => NewsletterSubscriber::query()->active()->count().' abonné(s) actif(s) recevront un message individualisé.')
            ->action(function (NewsletterCampaign $record, DispatchNewsletterCampaign $dispatcher): void {
                $campaign = $dispatcher->handle($record);
                Notification::make()
                    ->success()
                    ->title('Campagne ajoutée à la file d’envoi')
                    ->body($campaign->recipient_count.' destinataire(s) préparé(s).')
                    ->send();
            });
    }

    public static function scheduleAction(): Action
    {
        return Action::make('schedule')
            ->label('Programmer')
            ->icon(Heroicon::OutlinedClock)
            ->color('info')
            ->authorize(fn (NewsletterCampaign $record): bool => auth()->user()?->can('send', $record) ?? false)
            ->visible(fn (NewsletterCampaign $record): bool => in_array($record->status, ['draft', 'failed'], true))
            ->schema([
                DateTimePicker::make('scheduled_at')
                    ->label('Date et heure d’envoi')
                    ->minDate(fn () => now()->addMinute()->startOfMinute())
                    ->rules(['after:now'])
                    ->seconds(false)
                    ->required(),
            ])
            ->action(function (NewsletterCampaign $record, array $data): void {
                $record->update([
                    'status' => 'scheduled',
                    'scheduled_at' => $data['scheduled_at'],
                    'paused_at' => null,
                    'cancelled_at' => null,
                    'status_reason' => null,
                    'status_changed_by' => auth()->id(),
                ]);
                Notification::make()->success()->title('Envoi programmé')->send();
            });
    }

    public static function pauseAction(): Action
    {
        return Action::make('pause')
            ->label('Suspendre')
            ->icon(Heroicon::OutlinedPauseCircle)
            ->color('warning')
            ->authorize(fn (NewsletterCampaign $record): bool => auth()->user()?->can('send', $record) ?? false)
            ->visible(fn (NewsletterCampaign $record): bool => in_array($record->status, ['scheduled', 'sending'], true))
            ->requiresConfirmation()
            ->modalHeading('Suspendre cette campagne ?')
            ->modalDescription(fn (NewsletterCampaign $record): string => $record->status === 'sending'
                ? 'Les e-mails déjà envoyés ne peuvent pas être rappelés. Les envois encore en attente seront interrompus et pourront être repris plus tard.'
                : 'La programmation sera mise en pause. Aucun e-mail ne sera envoyé tant que la campagne n’aura pas été reprise.')
            ->schema([
                Textarea::make('reason')
                    ->label('Motif de la suspension')
                    ->required()
                    ->maxLength(1000)
                    ->rows(4),
            ])
            ->modalSubmitActionLabel('Suspendre la campagne')
            ->action(function (NewsletterCampaign $record, array $data, NewsletterCampaignLifecycle $lifecycle): void {
                $lifecycle->pause($record, $data['reason'], auth()->id());
                Notification::make()->warning()->title('Campagne suspendue')->body('Aucun nouvel envoi ne partira avant sa reprise.')->send();
            });
    }

    public static function resumeAction(): Action
    {
        return Action::make('resume')
            ->label('Reprendre')
            ->icon(Heroicon::OutlinedPlayCircle)
            ->color('success')
            ->authorize(fn (NewsletterCampaign $record): bool => auth()->user()?->can('send', $record) ?? false)
            ->visible(fn (NewsletterCampaign $record): bool => $record->status === 'paused')
            ->requiresConfirmation()
            ->modalHeading('Reprendre cette campagne ?')
            ->modalDescription('Si la date programmée est encore à venir, la programmation sera restaurée. Sinon, les destinataires restant à traiter seront remis dans la file d’envoi immédiatement.')
            ->modalSubmitActionLabel('Reprendre la diffusion')
            ->action(function (NewsletterCampaign $record, NewsletterCampaignLifecycle $lifecycle): void {
                $campaign = $lifecycle->resume($record, auth()->id());
                Notification::make()
                    ->success()
                    ->title($campaign->status === 'scheduled' ? 'Programmation réactivée' : 'Diffusion reprise')
                    ->send();
            });
    }

    public static function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label('Annuler')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('danger')
            ->authorize(fn (NewsletterCampaign $record): bool => auth()->user()?->can('send', $record) ?? false)
            ->visible(fn (NewsletterCampaign $record): bool => in_array($record->status, ['draft', 'scheduled', 'sending', 'paused', 'failed'], true))
            ->requiresConfirmation()
            ->modalHeading('Annuler définitivement cette campagne ?')
            ->modalDescription('Les envois encore en attente seront arrêtés. Les e-mails déjà remis aux destinataires ne peuvent pas être rappelés.')
            ->schema([
                Textarea::make('reason')
                    ->label('Motif de l’annulation')
                    ->required()
                    ->maxLength(1000)
                    ->rows(4),
            ])
            ->modalSubmitActionLabel('Confirmer l’annulation')
            ->action(function (NewsletterCampaign $record, array $data, NewsletterCampaignLifecycle $lifecycle): void {
                $lifecycle->cancel($record, $data['reason'], auth()->id());
                Notification::make()->success()->title('Campagne annulée')->body('Tous les envois encore en attente ont été arrêtés.')->send();
            });
    }

    public static function reopenAction(): Action
    {
        return Action::make('reopen')
            ->label('Réouvrir en brouillon')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('info')
            ->authorize(fn (NewsletterCampaign $record): bool => auth()->user()?->can('send', $record) ?? false)
            ->visible(fn (NewsletterCampaign $record): bool => $record->status === 'cancelled')
            ->requiresConfirmation()
            ->modalHeading('Réouvrir cet envoi ?')
            ->modalDescription('La campagne redeviendra un brouillon modifiable. Aucun e-mail ne sera envoyé tant que vous ne confirmerez pas sa diffusion.')
            ->modalSubmitActionLabel('Réouvrir en brouillon')
            ->action(function (NewsletterCampaign $record): void {
                $record->update([
                    'status' => 'draft',
                    'cancelled_at' => null,
                    'status_reason' => null,
                    'status_changed_by' => auth()->id(),
                ]);

                Notification::make()
                    ->success()
                    ->title('Campagne rouverte en brouillon')
                    ->body('Vous pouvez maintenant la modifier puis l’envoyer.')
                    ->send();
            });
    }

    public static function announceEventCancellationAction(): Action
    {
        return Action::make('announce_event_cancellation')
            ->label('Annoncer l’annulation')
            ->icon(Heroicon::OutlinedExclamationTriangle)
            ->color('danger')
            ->authorize(fn (NewsletterCampaign $record): bool => auth()->user()?->can('send', $record) ?? false)
            ->visible(fn (NewsletterCampaign $record): bool => $record->type === 'event' && $record->status === 'sent')
            ->modalHeading('Préparer un avis d’annulation ?')
            ->modalDescription('La campagne d’origine restera dans l’historique. Un nouveau brouillon sera créé afin d’informer clairement tous les abonnés.')
            ->schema([
                Textarea::make('reason')
                    ->label('Motif et informations à communiquer')
                    ->required()
                    ->maxLength(2000)
                    ->rows(6),
            ])
            ->modalSubmitActionLabel('Créer l’avis d’annulation')
            ->action(function (NewsletterCampaign $record, array $data, NewsletterCampaignLifecycle $lifecycle) {
                $notice = $lifecycle->createEventCancellationNotice($record, $data['reason'], auth()->id());
                Notification::make()->success()->title('Avis d’annulation préparé')->body('Vérifiez son contenu avant de l’envoyer.')->send();

                return redirect(self::getUrl('edit', ['record' => $notice]));
            });
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'draft' => 'Brouillon',
            'scheduled' => 'Programmée',
            'sending' => 'En cours d’envoi',
            'sent' => 'Envoyée',
            'failed' => 'Échec',
            'paused' => 'Suspendue',
            'cancelled' => 'Envoi annulé',
            'event_cancelled' => 'Événement annulé',
            default => $status,
        };
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'scheduled' => 'info',
            'sending' => 'warning',
            'sent' => 'success',
            'failed' => 'danger',
            'paused' => 'warning',
            'cancelled', 'event_cancelled' => 'danger',
            default => 'gray',
        };
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'news' => 'Actualité',
            'event' => 'Événement',
            'event_cancellation' => 'Avis d’annulation',
            'message' => 'Message public',
            default => $type,
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterCampaigns::route('/'),
            'create' => CreateNewsletterCampaign::route('/create'),
            'view' => ViewNewsletterCampaign::route('/{record}'),
            'edit' => EditNewsletterCampaign::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\NewsletterSubscribers;

use App\Filament\Exports\NewsletterSubscriberExporter;
use App\Filament\Resources\NewsletterSubscribers\Pages\ManageNewsletterSubscribers;
use App\Jobs\SendNewsletterVerification;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterSubscriberImporter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Abonnés newsletter';

    protected static ?string $modelLabel = 'abonné';

    protected static ?string $pluralModelLabel = 'abonnés newsletter';

    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Abonné')
                ->description('Une adresse ajoutée manuellement est activée immédiatement. Elle pourra se désinscrire depuis chaque message reçu.')
                ->icon(Heroicon::OutlinedEnvelope)
                ->schema([
                    TextInput::make('email')
                        ->label('Adresse e-mail')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(254),
                    TextInput::make('name')
                        ->label('Nom ou raison sociale')
                        ->maxLength(180),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Rechercher une adresse ou un nom…')
            ->columns([
                TextColumn::make('email')
                    ->label('Adresse e-mail')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->copyable()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('subscription_status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Actif',
                        'pending' => 'À confirmer',
                        'unsubscribed' => 'Désinscrit',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('source')
                    ->label('Origine')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'website' => 'Site public',
                        'import' => 'Import',
                        'admin' => 'Ajout manuel',
                        default => $state ?: 'Inconnue',
                    })
                    ->toggleable(),
                TextColumn::make('verified_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('En attente')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('subscription_status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actifs',
                        'pending' => 'À confirmer',
                        'unsubscribed' => 'Désinscrits',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'active' => $query->whereNotNull('verified_at')->whereNull('unsubscribed_at'),
                            'pending' => $query->whereNull('verified_at')->whereNull('unsubscribed_at'),
                            'unsubscribed' => $query->whereNotNull('unsubscribed_at'),
                            default => $query,
                        };
                    }),
                SelectFilter::make('source')
                    ->label('Origine')
                    ->options(['website' => 'Site public', 'import' => 'Import', 'admin' => 'Ajout manuel']),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('confirm_email')
                    ->label('Confirmer l’adresse')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('success')
                    ->iconButton()
                    ->tooltip('Confirmer manuellement cette adresse')
                    ->authorize(fn (NewsletterSubscriber $record): bool => auth()->user()?->can('confirm', $record) ?? false)
                    ->visible(fn (NewsletterSubscriber $record): bool => $record->subscription_status === 'pending'
                        && (auth()->user()?->can('confirm', $record) ?? false))
                    ->requiresConfirmation()
                    ->modalHeading('Confirmer cette adresse e-mail ?')
                    ->modalDescription(fn (NewsletterSubscriber $record): string => "L’adresse {$record->email} deviendra immédiatement active et éligible aux campagnes. Cette action remplace la confirmation par le lien envoyé à l’utilisateur.")
                    ->modalSubmitActionLabel('Confirmer l’adresse')
                    ->action(function (NewsletterSubscriber $record): void {
                        $record->update([
                            'verified_at' => now(),
                            'unsubscribed_at' => null,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Adresse e-mail confirmée')
                            ->body("{$record->email} peut désormais recevoir les campagnes newsletter.")
                            ->send();
                    }),
                Action::make('reactivate')
                    ->label('Réactiver')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('warning')
                    ->iconButton()
                    ->tooltip('Réactiver cette adresse désinscrite')
                    ->authorize(fn (NewsletterSubscriber $record): bool => auth()->user()?->can('reactivate', $record) ?? false)
                    ->visible(fn (NewsletterSubscriber $record): bool => $record->subscription_status === 'unsubscribed'
                        && (auth()->user()?->can('reactivate', $record) ?? false))
                    ->requiresConfirmation()
                    ->modalHeading('Réactiver cet abonnement ?')
                    ->modalDescription(fn (NewsletterSubscriber $record): string => "L’adresse {$record->email} s’était désinscrite. Elle redeviendra active et éligible aux campagnes newsletter.")
                    ->modalSubmitActionLabel('Réactiver l’abonnement')
                    ->action(function (NewsletterSubscriber $record): void {
                        $record->update([
                            'verified_at' => $record->verified_at ?? now(),
                            'unsubscribed_at' => null,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Abonnement réactivé')
                            ->body("{$record->email} est de nouveau éligible aux campagnes newsletter.")
                            ->send();
                    }),
                Action::make('resend_verification')
                    ->label('Renvoyer la confirmation')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->iconButton()
                    ->tooltip('Renvoyer la confirmation')
                    ->visible(fn (NewsletterSubscriber $record): bool => $record->subscription_status === 'pending')
                    ->action(function (NewsletterSubscriber $record): void {
                        $record->update(['verification_sent_at' => now()]);
                        SendNewsletterVerification::dispatch($record->id);
                        Notification::make()->success()->title('E-mail de confirmation ajouté à la file d’envoi')->send();
                    }),
                Action::make('unsubscribe')
                    ->label('Désinscrire')
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->iconButton()
                    ->tooltip('Désinscrire cette adresse')
                    ->visible(fn (NewsletterSubscriber $record): bool => $record->subscription_status === 'active')
                    ->requiresConfirmation()
                    ->action(fn (NewsletterSubscriber $record) => $record->update(['unsubscribed_at' => now()])),
                DeleteAction::make(),
            ])
            ->headerActions([
                Action::make('import')
                    ->label('Importer')
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->authorize(fn (): bool => auth()->user()?->can('import newsletter subscribers') ?? false)
                    ->modalHeading('Importer des abonnés')
                    ->modalDescription('Importez un CSV avec les colonnes email et nom, ou collez directement une adresse par ligne.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Fichier CSV')
                            ->disk('local')
                            ->directory('newsletter-imports')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                            ->maxSize(5120),
                        Textarea::make('emails')
                            ->label('Ou coller des adresses')
                            ->placeholder("email@example.com;Nom complet\nautre@example.com;Autre nom")
                            ->rows(7),
                        Toggle::make('activate')
                            ->label('Activer immédiatement les adresses importées')
                            ->default(true),
                    ])
                    ->action(function (array $data, NewsletterSubscriberImporter $importer): void {
                        if (blank($data['file'] ?? null) && blank($data['emails'] ?? null)) {
                            Notification::make()->danger()->title('Ajoutez un fichier CSV ou des adresses e-mail.')->send();

                            return;
                        }

                        $total = ['created' => 0, 'updated' => 0, 'invalid' => 0];
                        $activate = (bool) ($data['activate'] ?? true);

                        if (filled($data['file'] ?? null)) {
                            try {
                                $result = $importer->fromStoredFile('local', $data['file'], $activate);
                                foreach ($total as $key => $value) {
                                    $total[$key] += $result[$key];
                                }
                            } finally {
                                Storage::disk('local')->delete($data['file']);
                            }
                        }

                        if (filled($data['emails'] ?? null)) {
                            $result = $importer->fromText($data['emails'], $activate);
                            foreach ($total as $key => $value) {
                                $total[$key] += $result[$key];
                            }
                        }

                        Notification::make()
                            ->success()
                            ->title('Import terminé')
                            ->body("{$total['created']} ajoutée(s), {$total['updated']} mise(s) à jour, {$total['invalid']} invalide(s).")
                            ->send();
                    }),
                ExportAction::make()
                    ->label('Exporter')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->authorize(fn (): bool => auth()->user()?->can('export newsletter subscribers') ?? false)
                    ->exporter(NewsletterSubscriberExporter::class)
                    ->fileName(fn (): string => 'abonnes-newsletter-edsp-'.now()->format('Y-m-d-His')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('confirm_selected')
                        ->label('Confirmer les adresses')
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->color('success')
                        ->authorize(fn (): bool => auth()->user()?->hasRole('superadmin') ?? false)
                        ->visible(fn (): bool => auth()->user()?->hasRole('superadmin') ?? false)
                        ->requiresConfirmation()
                        ->modalHeading('Confirmer les adresses sélectionnées ?')
                        ->modalDescription('Seules les adresses encore en attente seront confirmées et deviendront immédiatement éligibles aux campagnes newsletter.')
                        ->modalSubmitActionLabel('Confirmer les adresses')
                        ->action(function (Collection $records): void {
                            $pendingSubscribers = $records->filter(
                                fn (NewsletterSubscriber $record): bool => $record->subscription_status === 'pending',
                            );

                            $pendingSubscribers->each(fn (NewsletterSubscriber $record) => $record->update([
                                'verified_at' => now(),
                                'unsubscribed_at' => null,
                            ]));

                            Notification::make()
                                ->success()
                                ->title($pendingSubscribers->count().' adresse(s) confirmée(s)')
                                ->body('Les autres adresses sélectionnées ont été laissées inchangées.')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('resend_verification_selected')
                        ->label('Renvoyer les confirmations')
                        ->icon(Heroicon::OutlinedPaperAirplane)
                        ->authorize(fn (): bool => auth()->user()?->can('edit newsletter subscribers') ?? false)
                        ->requiresConfirmation()
                        ->modalHeading('Renvoyer les e-mails de confirmation ?')
                        ->modalDescription('Un nouvel e-mail sera envoyé uniquement aux adresses sélectionnées qui sont encore en attente de confirmation.')
                        ->modalSubmitActionLabel('Renvoyer les confirmations')
                        ->action(function (Collection $records): void {
                            $pendingSubscribers = $records->filter(
                                fn (NewsletterSubscriber $record): bool => $record->subscription_status === 'pending',
                            );

                            $pendingSubscribers->each(function (NewsletterSubscriber $record): void {
                                $record->update(['verification_sent_at' => now()]);
                                SendNewsletterVerification::dispatch($record->id);
                            });

                            Notification::make()
                                ->success()
                                ->title($pendingSubscribers->count().' confirmation(s) ajoutée(s) à la file d’envoi')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->label('Supprimer les adresses')
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterSubscribers::route('/')];
    }
}

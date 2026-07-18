<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ManageActivityLogs;
use App\Models\ActivityLog;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use UnitEnum;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Journal d’activité';

    protected static ?string $modelLabel = 'événement';

    protected static ?string $pluralModelLabel = 'journal d’activité';

    protected static ?string $recordTitleAttribute = 'action';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Événement')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('action')
                                ->label('Action'),
                            Select::make('user_id')
                                ->label('Utilisateur')
                                ->relationship('user', 'name'),
                            TextInput::make('subject_type')
                                ->label('Type de ressource'),
                            TextInput::make('subject_id')
                                ->label('Identifiant de ressource'),
                            TextInput::make('ip_address')
                                ->label('Adresse IP'),
                            TextInput::make('created_at')
                                ->label('Date et heure'),
                        ]),
                    ]),
                Section::make('Métadonnées')
                    ->schema([
                        Textarea::make('metadata_json')
                            ->hiddenLabel()
                            ->rows(14)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->placeholder('Système')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject_type')
                    ->label('Ressource')
                    ->formatStateUsing(fn (?string $state, ActivityLog $record): string => $state
                        ? class_basename($state).' #'.($record->subject_id ?? '—')
                        : '—')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->label('Adresse IP')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('metadata')
                    ->label('Détails')
                    ->formatStateUsing(fn (mixed $state): string => Str::limit(
                        json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '',
                        70,
                    ))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('action')
                    ->label('Action')
                    ->options(fn (): array => ActivityLog::query()
                        ->distinct()
                        ->orderBy('action')
                        ->pluck('action', 'action')
                        ->all()),
                SelectFilter::make('subject_type')
                    ->label('Type de ressource')
                    ->options(fn (): array => ActivityLog::query()
                        ->whereNotNull('subject_type')
                        ->distinct()
                        ->orderBy('subject_type')
                        ->pluck('subject_type', 'subject_type')
                        ->mapWithKeys(fn (string $type): array => [$type => class_basename($type)])
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalWidth('5xl')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['metadata_json'] = json_encode(
                            $data['metadata'] ?? [],
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                        ) ?: '{}';

                        return $data;
                    }),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageActivityLogs::route('/'),
        ];
    }
}

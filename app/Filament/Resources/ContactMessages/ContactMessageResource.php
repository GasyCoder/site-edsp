<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use UnitEnum;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Messages de contact';

    protected static ?string $modelLabel = 'message';

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Expéditeur')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('first_name')
                                ->label('Prénom')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('last_name')
                                ->label('Nom')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('email')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('phone')
                                ->label('Téléphone')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('organization')
                                ->label('Organisation')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ]),
                Section::make('Message')
                    ->schema([
                        TextInput::make('subject')
                            ->label('Sujet')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('message')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(9),
                    ]),
                Section::make('Traitement')
                    ->schema([
                        Grid::make(2)->schema([
                            DateTimePicker::make('read_at')
                                ->label('Lu le')
                                ->seconds(false),
                            DateTimePicker::make('handled_at')
                                ->label('Traité le')
                                ->seconds(false),
                            Select::make('handled_by')
                                ->label('Traité par')
                                ->relationship('handledBy', 'name')
                                ->searchable()
                                ->preload(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                TextColumn::make('subject')
                    ->label('Sujet')
                    ->searchable()
                    ->limit(55),
                TextColumn::make('last_name')
                    ->label('Expéditeur')
                    ->formatStateUsing(fn (ContactMessage $record): string => trim("{$record->first_name} {$record->last_name}"))
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('read_at')
                    ->label('Lu')
                    ->boolean(),
                IconColumn::make('handled_at')
                    ->label('Traité')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Reçu le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('read_at')
                    ->label('Lecture')
                    ->nullable(),
                TernaryFilter::make('handled_at')
                    ->label('Traitement')
                    ->nullable(),
                TernaryFilter::make('archived_at')
                    ->label('Archivage')
                    ->nullable(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('markRead')
                    ->label('Marquer comme lu')
                    ->icon(Heroicon::OutlinedEye)
                    ->iconButton()
                    ->tooltip('Marquer comme lu')
                    ->authorize('update')
                    ->visible(fn (ContactMessage $record): bool => $record->read_at === null)
                    ->action(function (ContactMessage $record): bool {
                        Gate::authorize('update', $record);

                        return $record->update(['read_at' => now()]);
                    }),
                Action::make('markHandled')
                    ->label('Marquer comme traité')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->iconButton()
                    ->tooltip('Marquer comme traité')
                    ->authorize('update')
                    ->visible(fn (ContactMessage $record): bool => $record->handled_at === null)
                    ->requiresConfirmation()
                    ->action(function (ContactMessage $record): bool {
                        Gate::authorize('update', $record);

                        return $record->update([
                            'read_at' => $record->read_at ?? now(),
                            'handled_at' => now(),
                            'handled_by' => auth()->id(),
                        ]);
                    }),
                Action::make('archive')
                    ->label('Archiver')
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->iconButton()
                    ->tooltip('Archiver')
                    ->color('warning')
                    ->authorize('update')
                    ->visible(fn (ContactMessage $record): bool => $record->archived_at === null)
                    ->requiresConfirmation()
                    ->action(function (ContactMessage $record): bool {
                        Gate::authorize('update', $record);

                        return $record->update(['archived_at' => now()]);
                    }),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageContactMessages::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

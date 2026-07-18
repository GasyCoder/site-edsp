<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\Redirects\Pages\ManageRedirects;
use App\Models\Redirect;
use App\Rules\SafeUrl;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Redirections';

    protected static ?string $modelLabel = 'redirection';

    protected static ?string $pluralModelLabel = 'redirections';

    protected static ?string $recordTitleAttribute = 'source_path';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Règle de redirection')
                    ->description('Utilisez un chemin source interne et une destination interne ou absolue.')
                    ->schema([
                        TextInput::make('source_path')
                            ->label('Chemin source')
                            ->placeholder('/ancienne-page')
                            ->helperText('Doit commencer par / et ne contenir aucun espace.')
                            ->required()
                            ->rules(['regex:#^/(?!/)[^\s]*$#'])
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('destination_url')
                            ->label('Destination')
                            ->placeholder('/nouvelle-page ou https://exemple.mg/page')
                            ->helperText('Chemin commençant par / ou URL complète en HTTP(S).')
                            ->required()
                            ->rules(['different:source_path', new SafeUrl])
                            ->maxLength(255),
                        Select::make('status_code')
                            ->label('Code HTTP')
                            ->options(self::statusCodeOptions())
                            ->default(301)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Redirection active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('source_path')
            ->defaultSort('source_path')
            ->columns([
                TextColumn::make('source_path')
                    ->label('Source')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('destination_url')
                    ->label('Destination')
                    ->searchable()
                    ->copyable()
                    ->limit(65)
                    ->tooltip(fn (Redirect $record): string => $record->destination_url),
                TextColumn::make('status_code')
                    ->label('Code')
                    ->badge()
                    ->formatStateUsing(fn (int|string|null $state): string => self::statusCodeOptions()[(int) $state] ?? (string) $state)
                    ->color(fn (int|string|null $state): string => in_array((int) $state, [301, 308], true) ? 'success' : 'warning')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Mis à jour')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status_code')
                    ->label('Code HTTP')
                    ->options(self::statusCodeOptions()),
                TernaryFilter::make('is_active')
                    ->label('État'),
            ])
            ->recordActions([
                Action::make('openDestination')
                    ->label('Ouvrir la destination')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->iconButton()
                    ->tooltip('Ouvrir la destination')
                    ->url(fn (Redirect $record): string => str_starts_with($record->destination_url, '/')
                        ? url($record->destination_url)
                        : $record->destination_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Redirect $record): bool => Str::startsWith($record->destination_url, ['/', 'http://', 'https://'])),
                EditAction::make()
                    ->modalWidth('4xl'),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    /** @return array<int, string> */
    private static function statusCodeOptions(): array
    {
        return [
            301 => '301 — Permanente',
            302 => '302 — Temporaire',
            307 => '307 — Temporaire stricte',
            308 => '308 — Permanente stricte',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRedirects::route('/'),
        ];
    }
}

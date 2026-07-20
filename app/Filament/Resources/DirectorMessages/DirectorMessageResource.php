<?php

namespace App\Filament\Resources\DirectorMessages;

use App\Filament\Resources\DirectorMessages\Pages\EditDirectorMessage;
use App\Filament\Resources\DirectorMessages\Pages\ListDirectorMessages;
use App\Filament\Resources\PageSections\PageSectionResource;
use App\Models\PageSection;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DirectorMessageResource extends Resource
{
    protected static ?string $model = PageSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'Contenus';

    protected static ?string $navigationLabel = 'Mot du directeur';

    protected static ?string $modelLabel = 'mot du directeur';

    protected static ?string $pluralModelLabel = 'mot du directeur';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PageSectionResource::form($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image.image_url')
                    ->label('Portrait')
                    ->circular(),
                TextColumn::make('title')
                    ->label('Directeur')
                    ->description(fn (PageSection $record): ?string => $record->settings['director_position'] ?? null)
                    ->searchable(),
                TextColumn::make('subtitle')
                    ->label('Libellé')
                    ->badge(),
                TextColumn::make('updated_at')
                    ->label('Dernière modification')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Modifier le mot du directeur')
                    ->url(fn (PageSection $record): string => static::getUrl('edit', ['record' => $record])),
            ])
            ->recordUrl(fn (PageSection $record): string => static::getUrl('edit', ['record' => $record]));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('section_type', 'director-message');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDirectorMessages::route('/'),
            'edit' => EditDirectorMessage::route('/{record}/edit'),
        ];
    }
}

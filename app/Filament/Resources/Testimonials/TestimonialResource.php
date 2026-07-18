<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Testimonial;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Contenus';

    protected static ?string $navigationLabel = 'Témoignages';

    protected static ?string $modelLabel = 'témoignage';

    protected static ?string $pluralModelLabel = 'témoignages';

    protected static ?string $recordTitleAttribute = 'author_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Témoignage')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('author_name')
                                ->label('Nom de l’auteur')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('author_role')
                                ->label('Fonction ou promotion')
                                ->maxLength(255),
                            Select::make('photo_id')
                                ->label('Photo')
                                ->relationship(
                                    name: 'photo',
                                    titleAttribute: 'original_name',
                                    modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                )
                                ->searchable()
                                ->preload(),
                            Toggle::make('is_visible')
                                ->label('Afficher sur le site')
                                ->default(true),
                        ]),
                        Textarea::make('content')
                            ->label('Citation')
                            ->required()
                            ->rows(7)
                            ->maxLength(3000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('author_name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('photo.path')
                    ->label('Photo')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('author_name')
                    ->label('Auteur')
                    ->description(fn (Testimonial $record): ?string => $record->author_role)
                    ->searchable(['author_name', 'author_role'])
                    ->sortable(),
                TextColumn::make('content')
                    ->label('Témoignage')
                    ->formatStateUsing(fn (?string $state): string => Str::limit($state, 95))
                    ->wrap()
                    ->searchable(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Mis à jour')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label('Visibilité'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth('5xl'),
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

    public static function getPages(): array
    {
        return [
            'index' => ManageTestimonials::route('/'),
        ];
    }
}

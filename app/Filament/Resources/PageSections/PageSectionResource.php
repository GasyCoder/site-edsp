<?php

namespace App\Filament\Resources\PageSections;

use App\Filament\Resources\PageSections\Pages\ManagePageSections;
use App\Models\PageSection;
use App\Rules\SafeUrl;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use UnitEnum;

class PageSectionResource extends Resource
{
    protected static ?string $model = PageSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Contenus';

    protected static ?string $navigationLabel = 'Sections de pages';

    protected static ?string $modelLabel = 'section';

    protected static ?string $recordTitleAttribute = 'section_key';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Section')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('page_id')
                                ->label('Page')
                                ->relationship('page', 'title')
                                ->searchable()
                                ->preload()
                                ->required(),
                            TextInput::make('section_key')
                                ->label('Clé technique')
                                ->required()
                                ->rules(fn (?PageSection $record, callable $get): array => [
                                    Rule::unique('page_sections', 'section_key')
                                        ->where('page_id', $get('page_id'))
                                        ->ignore($record?->getKey()),
                                ])
                                ->maxLength(100),
                            Select::make('section_type')
                                ->label('Type')
                                ->options([
                                    'hero' => 'Hero',
                                    'content' => 'Contenu',
                                    'features' => 'Cartes',
                                    'programs' => 'Formations',
                                    'stats' => 'Chiffres clés',
                                    'admissions' => 'Admissions',
                                    'news' => 'Actualités',
                                    'student_life' => 'Vie étudiante',
                                    'library' => 'Bibliothèque',
                                    'team' => 'Équipe',
                                    'testimonials' => 'Témoignages',
                                    'cta' => 'Appel à l’action',
                                ])
                                ->required(),
                            TextInput::make('position')
                                ->label('Ordre')
                                ->numeric()
                                ->minValue(0)
                                ->required(),
                            Toggle::make('is_visible')
                                ->label('Visible')
                                ->default(true),
                        ]),
                        TextInput::make('title')
                            ->label('Titre')
                            ->maxLength(180),
                        TextInput::make('subtitle')
                            ->label('Sur-titre / sous-titre')
                            ->maxLength(255),
                        Textarea::make('content')
                            ->label('Contenu')
                            ->rows(6)
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            TextInput::make('button_text')
                                ->label('Texte du bouton')
                                ->maxLength(80),
                            TextInput::make('button_url')
                                ->label('Lien du bouton')
                                ->rules([new SafeUrl])
                                ->maxLength(2048),
                            Select::make('image_id')
                                ->label('Média')
                                ->relationship(
                                    name: 'image',
                                    titleAttribute: 'original_name',
                                    modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                )
                                ->searchable()
                                ->preload(),
                        ]),
                    ]),
                Section::make('Paramètres contrôlés')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('settings.background')
                                ->label('Arrière-plan')
                                ->options([
                                    'white' => 'Blanc',
                                    'light' => 'Clair',
                                    'blue' => 'Bleu',
                                ]),
                            Select::make('settings.alignment')
                                ->label('Alignement')
                                ->options([
                                    'left' => 'Gauche',
                                    'center' => 'Centre',
                                ]),
                            Select::make('settings.container')
                                ->label('Largeur')
                                ->options([
                                    'narrow' => 'Étroit',
                                    'default' => 'Standard',
                                    'wide' => 'Large',
                                ]),
                        ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('section_key')
            ->columns([
                TextColumn::make('section_key')
                    ->label('Clé')
                    ->searchable(),
                TextColumn::make('page.title')
                    ->label('Page')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->limit(45)
                    ->searchable(),
                TextColumn::make('section_type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('position')
                    ->label('Ordre')
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('page_id')
                    ->label('Page')
                    ->relationship('page', 'title'),
                SelectFilter::make('section_type')
                    ->label('Type')
                    ->options([
                        'hero' => 'Hero',
                        'content' => 'Contenu',
                        'features' => 'Cartes',
                        'programs' => 'Formations',
                        'stats' => 'Chiffres clés',
                        'admissions' => 'Admissions',
                        'news' => 'Actualités',
                        'student_life' => 'Vie étudiante',
                        'library' => 'Bibliothèque',
                        'team' => 'Équipe',
                        'testimonials' => 'Témoignages',
                        'cta' => 'Appel à l’action',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(function (array $data, PageSection $record): array {
                        $data['settings'] = [
                            ...($record->settings ?? []),
                            ...($data['settings'] ?? []),
                        ];

                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePageSections::route('/'),
        ];
    }
}

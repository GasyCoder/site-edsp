<?php

namespace App\Filament\Resources\Faqs;

use App\Filament\Resources\Faqs\Pages\ManageFaqs;
use App\Models\Faq;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
use UnitEnum;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Contenus';

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $modelLabel = 'question fréquente';

    protected static ?string $pluralModelLabel = 'questions fréquentes';

    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Question et réponse')
                    ->description('Rédigez une réponse courte, précise et facile à parcourir sur le site.')
                    ->icon(Heroicon::OutlinedQuestionMarkCircle)
                    ->schema([
                        TextInput::make('question')
                            ->label('Question')
                            ->placeholder('Ex. Comment déposer un dossier d’inscription ?')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        RichEditor::make('answer')
                            ->label('Réponse')
                            ->helperText('Utilisez des paragraphes, listes ou liens lorsque cela facilite la lecture.')
                            ->required()
                            ->extraInputAttributes(['style' => 'min-height:14rem'])
                            ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? ''))
                            ->columnSpanFull(),
                        Grid::make(3)->schema([
                            TextInput::make('category')
                                ->label('Catégorie')
                                ->placeholder('Ex. Inscriptions')
                                ->maxLength(120)
                                ->datalist(fn (): array => Faq::query()
                                    ->whereNotNull('category')
                                    ->where('category', '!=', '')
                                    ->distinct()
                                    ->orderBy('category')
                                    ->pluck('category')
                                    ->all()),
                            TextInput::make('position')
                                ->label('Ordre d’affichage')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                            Toggle::make('is_visible')
                                ->label('Afficher sur le site')
                                ->default(true),
                        ]),
                    ]),
                Section::make('Version anglaise')
                    ->description('Contenu affiché lorsque le visiteur choisit English. Les champs vides utilisent le français.')
                    ->icon(Heroicon::OutlinedLanguage)
                    ->schema([
                        TextInput::make('translations.en.question')
                            ->label('Question en anglais')
                            ->maxLength(255),
                        RichEditor::make('translations.en.answer')
                            ->label('Réponse en anglais')
                            ->extraInputAttributes(['style' => 'min-height:12rem'])
                            ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? ''))
                            ->columnSpanFull(),
                        TextInput::make('translations.en.category')
                            ->label('Catégorie en anglais')
                            ->maxLength(120),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                TextColumn::make('question')
                    ->label('Question')
                    ->description(fn (Faq $record): ?string => $record->category)
                    ->wrap()
                    ->searchable(['question', 'answer', 'category'])
                    ->sortable(),
                TextColumn::make('answer')
                    ->label('Réponse')
                    ->formatStateUsing(fn (?string $state): string => Str::limit(strip_tags($state ?? ''), 110))
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('position')
                    ->label('Ordre')
                    ->sortable()
                    ->width('90px'),
                ToggleColumn::make('is_visible')
                    ->label('Visible')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Mis à jour')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(fn (): array => Faq::query()
                        ->whereNotNull('category')
                        ->where('category', '!=', '')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category', 'category')
                        ->all()),
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
            'index' => ManageFaqs::route('/'),
        ];
    }
}

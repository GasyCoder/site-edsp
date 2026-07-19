<?php

namespace App\Filament\Resources\AdmissionCampaigns;

use App\Filament\Resources\AdmissionCampaigns\Pages\ManageAdmissionCampaigns;
use App\Models\AdmissionCampaign;
use App\Rules\SafeUrl;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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
use UnitEnum;

class AdmissionCampaignResource extends Resource
{
    protected static ?string $model = AdmissionCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Admissions';

    protected static ?string $navigationLabel = 'Campagnes';

    protected static ?string $modelLabel = 'campagne';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Campagne d’admission')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(180),
                        Grid::make(2)->schema([
                            TextInput::make('academic_year')
                                ->label('Année universitaire')
                                ->placeholder('2026-2027')
                                ->required(),
                            Select::make('status')
                                ->label('Statut')
                                ->options([
                                    'draft' => 'Brouillon',
                                    'pending' => 'En attente',
                                    'scheduled' => 'Programmée',
                                    'published' => 'Publiée',
                                    'archived' => 'Archivée',
                                ])
                                ->required(),
                            DateTimePicker::make('opens_at')
                                ->label('Ouverture')
                                ->seconds(false)
                                ->required(),
                            DateTimePicker::make('closes_at')
                                ->label('Fermeture')
                                ->seconds(false)
                                ->required()
                                ->after('opens_at'),
                            Toggle::make('is_visible')
                                ->label('Visible sur le site')
                                ->default(true),
                        ]),
                        Select::make('programs')
                            ->label('Formations ouvertes')
                            ->relationship('programs', 'title')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required(),
                        Textarea::make('instructions')
                            ->label('Instructions')
                            ->helperText('Présentez brièvement les consignes importantes avant de commencer le formulaire.')
                            ->rows(5),
                        TextInput::make('tutorial_video_url')
                            ->label('Lien du tutoriel vidéo')
                            ->placeholder('https://www.youtube.com/watch?v=…')
                            ->helperText('Facultatif. Ajoutez une vidéo YouTube, Vimeo ou toute autre adresse HTTPS expliquant comment remplir le dossier.')
                            ->prefixIcon(Heroicon::OutlinedPlayCircle)
                            ->rules([new SafeUrl(allowRelative: false)])
                            ->maxLength(2048),
                        TagsInput::make('required_documents')
                            ->label('Pièces demandées')
                            ->placeholder('Ajouter une pièce'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Campagne')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('academic_year')
                    ->label('Année')
                    ->badge(),
                TextColumn::make('opens_at')
                    ->label('Ouverture')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('closes_at')
                    ->label('Fermeture')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'pending' => 'En attente',
                        'scheduled' => 'Programmée',
                        'published' => 'Publiée',
                        'archived' => 'Archivée',
                        default => (string) $state,
                    }),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
                IconColumn::make('has_tutorial')
                    ->label('Tutoriel')
                    ->state(fn (AdmissionCampaign $record): bool => filled($record->tutorial_video_url))
                    ->boolean()
                    ->tooltip(fn (AdmissionCampaign $record): string => filled($record->tutorial_video_url)
                        ? 'Un tutoriel vidéo est disponible'
                        : 'Aucun tutoriel vidéo'),
                TextColumn::make('applications_count')
                    ->label('Dossiers')
                    ->counts('applications')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Brouillon',
                        'pending' => 'En attente',
                        'scheduled' => 'Programmée',
                        'published' => 'Publiée',
                        'archived' => 'Archivée',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
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
            'index' => ManageAdmissionCampaigns::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\Settings\SettingResource;
use App\Jobs\OptimizeMediaImage;
use App\Models\Media;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

/**
 * @property-read Schema $form
 */
class ManageSettings extends Page
{
    protected static string $resource = SettingResource::class;

    protected static ?string $title = 'Paramètres du site';

    protected ?string $subheading = 'Gérez l’identité de l’EDSP, ses coordonnées et son référencement depuis un seul écran.';

    protected Width|string|null $maxContentWidth = Width::Full;

    public static bool $formActionsAreSticky = true;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Configuration du site')
                    ->persistTabInQueryString('section')
                    ->tabs([
                        Tab::make('Identité')
                            ->icon(Heroicon::OutlinedBuildingLibrary)
                            ->schema([
                                Section::make('Informations principales')
                                    ->description('Ces informations identifient l’établissement sur le site public.')
                                    ->icon(Heroicon::OutlinedIdentification)
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('site_name')
                                                ->label('Nom court du site')
                                                ->placeholder('EDSP')
                                                ->required()
                                                ->maxLength(100),
                                            TextInput::make('institution_name')
                                                ->label('Nom officiel de l’établissement')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('parent_institution')
                                                ->label('Institution de rattachement')
                                                ->placeholder('Facultatif')
                                                ->maxLength(255),
                                            TextInput::make('academic_year')
                                                ->label('Année académique active')
                                                ->placeholder('2026-2027')
                                                ->maxLength(30),
                                        ]),
                                        Textarea::make('site_description')
                                            ->label('Description générale')
                                            ->helperText('Un texte court et institutionnel, également utile pour le référencement.')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->columnSpanFull(),
                                        Textarea::make('footer_text')
                                            ->label('Présentation dans le pied de page')
                                            ->rows(2)
                                            ->maxLength(500)
                                            ->columnSpanFull(),
                                    ]),
                                self::imageSection(
                                    prefix: 'logo',
                                    title: 'Logo principal',
                                    description: 'Utilisé dans l’en-tête et le pied de page. Préférez un PNG ou WebP transparent et horizontal.',
                                    recommendation: 'Format conseillé : PNG/WebP, largeur minimale 500 px, 5 Mo maximum.',
                                    icon: Heroicon::OutlinedPhoto,
                                ),
                                self::imageSection(
                                    prefix: 'logo_dark',
                                    title: 'Logo pour le mode sombre',
                                    description: 'Variante transparente utilisée sur les surfaces sombres. Si nécessaire, conservez une petite zone claire autour des détails noirs.',
                                    recommendation: 'Format conseillé : PNG/WebP transparent, largeur minimale 500 px, 5 Mo maximum.',
                                    icon: Heroicon::OutlinedMoon,
                                ),
                                self::imageSection(
                                    prefix: 'favicon',
                                    title: 'Favicon du navigateur',
                                    description: 'Petite image carrée affichée dans l’onglet du navigateur.',
                                    recommendation: 'Format conseillé : PNG carré de 32 × 32 ou 64 × 64 px, 1 Mo maximum.',
                                    icon: Heroicon::OutlinedGlobeAlt,
                                    maxSize: 1024,
                                ),
                            ]),
                        Tab::make('Référence officielle')
                            ->icon(Heroicon::OutlinedDocumentCheck)
                            ->schema([
                                Section::make('Habilitation de l’établissement')
                                    ->description('Modifiez ici l’arrêté ministériel affiché dans le bandeau institutionnel du site public.')
                                    ->icon(Heroicon::OutlinedBuildingLibrary)
                                    ->schema([
                                        TextInput::make('ministerial_reference_label')
                                            ->label('Libellé affiché au public')
                                            ->placeholder('Référence ministérielle')
                                            ->helperText('Exemple : Référence ministérielle')
                                            ->required()
                                            ->maxLength(180),
                                        Textarea::make('ministerial_reference')
                                            ->label('Référence de l’arrêté ministériel')
                                            ->placeholder('Arrêté n°8008/2014-MESupRES du 29 janvier 2014')
                                            ->helperText('Cette référence concerne l’établissement. Elle reste indépendante du mot du directeur.')
                                            ->rows(3)
                                            ->required()
                                            ->maxLength(500),
                                    ]),
                                Section::make('Version anglaise')
                                    ->description('Texte affiché lorsque la langue du site est English.')
                                    ->icon(Heroicon::OutlinedLanguage)
                                    ->schema([
                                        TextInput::make('ministerial_reference_label_en')
                                            ->label('Official reference label')
                                            ->maxLength(180),
                                        Textarea::make('ministerial_reference_en')
                                            ->label('Ministerial order reference')
                                            ->rows(3)
                                            ->maxLength(500),
                                    ]),
                            ]),
                        Tab::make('Coordonnées')
                            ->icon(Heroicon::OutlinedMapPin)
                            ->schema([
                                Section::make('Coordonnées publiques')
                                    ->description('Informations affichées dans l’en-tête, le pied de page et la page Contact.')
                                    ->icon(Heroicon::OutlinedPhone)
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('contact_email')
                                                ->label('Adresse e-mail principale')
                                                ->email()
                                                ->prefixIcon(Heroicon::OutlinedEnvelope)
                                                ->maxLength(255),
                                            TextInput::make('contact_phone')
                                                ->label('Téléphone principal')
                                                ->tel()
                                                ->prefixIcon(Heroicon::OutlinedPhone)
                                                ->maxLength(50),
                                            TextInput::make('phone_secondary')
                                                ->label('Téléphone secondaire')
                                                ->tel()
                                                ->prefixIcon(Heroicon::OutlinedPhone)
                                                ->maxLength(50),
                                            TextInput::make('contact_location')
                                                ->label('Ville et pays')
                                                ->prefixIcon(Heroicon::OutlinedMap)
                                                ->maxLength(255),
                                        ]),
                                        Textarea::make('contact_address')
                                            ->label('Adresse complète de l’établissement')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Version anglaise')
                            ->icon(Heroicon::OutlinedLanguage)
                            ->schema([
                                Section::make('Identité et présentation en anglais')
                                    ->description('Ces textes sont affichés lorsque le visiteur choisit English.')
                                    ->schema([
                                        TextInput::make('institution_name_en')
                                            ->label('Official institution name')
                                            ->maxLength(255),
                                        Textarea::make('site_description_en')
                                            ->label('General description')
                                            ->rows(3)
                                            ->maxLength(500),
                                        Textarea::make('footer_text_en')
                                            ->label('Footer introduction')
                                            ->rows(2)
                                            ->maxLength(500),
                                    ]),
                                Section::make('English SEO defaults')
                                    ->schema([
                                        TextInput::make('seo_title_en')->label('Default SEO title')->maxLength(70),
                                        Textarea::make('seo_description_en')->label('Default meta description')->rows(3)->maxLength(180),
                                        Textarea::make('seo_keywords_en')->label('Keywords')->rows(2)->maxLength(500),
                                    ]),
                            ]),
                        Tab::make('Réseaux sociaux')
                            ->icon(Heroicon::OutlinedShare)
                            ->schema([
                                Section::make('Liens officiels')
                                    ->description('Laissez un champ vide pour masquer le réseau correspondant sur le site.')
                                    ->icon(Heroicon::OutlinedLink)
                                    ->schema([
                                        TextInput::make('facebook_url')
                                            ->label('Page Facebook')
                                            ->placeholder('https://facebook.com/...')
                                            ->url()
                                            ->prefixIcon(Heroicon::OutlinedLink)
                                            ->maxLength(2048),
                                        TextInput::make('linkedin_url')
                                            ->label('Page LinkedIn')
                                            ->placeholder('https://linkedin.com/company/...')
                                            ->url()
                                            ->prefixIcon(Heroicon::OutlinedLink)
                                            ->maxLength(2048),
                                        TextInput::make('youtube_url')
                                            ->label('Chaîne YouTube')
                                            ->placeholder('https://youtube.com/@...')
                                            ->url()
                                            ->prefixIcon(Heroicon::OutlinedLink)
                                            ->maxLength(2048),
                                    ]),
                            ]),
                        Tab::make('SEO et partage')
                            ->icon(Heroicon::OutlinedMagnifyingGlass)
                            ->schema([
                                Section::make('Référencement par défaut')
                                    ->description('Valeurs utilisées lorsqu’une page ne possède pas ses propres métadonnées.')
                                    ->icon(Heroicon::OutlinedChartBar)
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('Titre SEO par défaut')
                                            ->helperText('Idéalement entre 45 et 60 caractères.')
                                            ->maxLength(70),
                                        Textarea::make('seo_description')
                                            ->label('Méta-description par défaut')
                                            ->helperText('Idéalement entre 120 et 160 caractères.')
                                            ->rows(3)
                                            ->maxLength(180),
                                        Textarea::make('seo_keywords')
                                            ->label('Mots-clés')
                                            ->helperText('Séparez les mots-clés par des virgules.')
                                            ->rows(2)
                                            ->maxLength(500),
                                    ]),
                                self::imageSection(
                                    prefix: 'og',
                                    title: 'Image de partage par défaut',
                                    description: 'Utilisée par Facebook, LinkedIn et les messageries lors du partage d’un lien.',
                                    recommendation: 'Format conseillé : 1200 × 630 px, JPG/PNG/WebP, 5 Mo maximum.',
                                    icon: Heroicon::OutlinedRectangleGroup,
                                ),
                                Section::make('Indexation')
                                    ->description('Configuration avancée destinée aux moteurs de recherche.')
                                    ->icon(Heroicon::OutlinedCommandLine)
                                    ->collapsed()
                                    ->schema([
                                        Textarea::make('robots_content')
                                            ->label('Contenu du fichier robots.txt')
                                            ->rows(7)
                                            ->maxLength(5000),
                                    ]),
                            ]),
                        Tab::make('Options avancées')
                            ->icon(Heroicon::OutlinedWrenchScrewdriver)
                            ->schema([
                                Section::make('Services et informations légales')
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->schema([
                                        TextInput::make('library_url')
                                            ->label('Lien vers la bibliothèque')
                                            ->placeholder('https://... ou /bibliotheque')
                                            ->maxLength(2048),
                                        Textarea::make('legal_information')
                                            ->label('Informations légales')
                                            ->rows(5)
                                            ->maxLength(5000),
                                    ]),
                                Section::make('Disponibilité du site')
                                    ->description('À utiliser uniquement pendant une opération technique planifiée.')
                                    ->icon(Heroicon::OutlinedExclamationTriangle)
                                    ->schema([
                                        Toggle::make('maintenance_mode')
                                            ->label('Activer le mode maintenance')
                                            ->helperText('Les visiteurs verront une page d’indisponibilité ; l’administration restera accessible.')
                                            ->onColor('danger'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('settings-form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Enregistrer les paramètres')
                            ->icon(Heroicon::OutlinedCheckCircle)
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ])
                        ->alignment('end')
                        ->sticky(),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('official_reference')
                ->label('Modifier la référence officielle')
                ->icon(Heroicon::OutlinedDocumentCheck)
                ->color('primary')
                ->url(SettingResource::getUrl('index', ['section' => 'reference-officielle'])),
            Action::make('media')
                ->label('Ouvrir la médiathèque')
                ->icon(Heroicon::OutlinedPhoto)
                ->color('gray')
                ->url(MediaResource::getUrl()),
            Action::make('website')
                ->label('Voir le site public')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(url('/'))
                ->openUrlInNewTab(),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data): void {
            $logo = $this->resolveImageValue('logo', $data);
            $darkLogo = $this->resolveImageValue('logo_dark', $data);
            $favicon = $this->resolveImageValue('favicon', $data);
            $openGraphImage = $this->resolveImageValue('og', $data);

            $values = [
                'site_name' => $data['site_name'] ?? null,
                'institution_name' => $data['institution_name'] ?? null,
                'parent_institution' => $data['parent_institution'] ?? null,
                'site_description' => $data['site_description'] ?? null,
                'footer_text' => $data['footer_text'] ?? null,
                'institution_name_en' => $data['institution_name_en'] ?? null,
                'site_description_en' => $data['site_description_en'] ?? null,
                'footer_text_en' => $data['footer_text_en'] ?? null,
                'ministerial_reference_label' => $data['ministerial_reference_label'] ?? null,
                'ministerial_reference' => $data['ministerial_reference'] ?? null,
                'ministerial_reference_label_en' => $data['ministerial_reference_label_en'] ?? null,
                'ministerial_reference_en' => $data['ministerial_reference_en'] ?? null,
                'academic_year' => $data['academic_year'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'email' => $data['contact_email'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'phone' => $data['contact_phone'] ?? null,
                'phone_secondary' => $data['phone_secondary'] ?? null,
                'contact_address' => $data['contact_address'] ?? null,
                'address' => $data['contact_address'] ?? null,
                'contact_location' => $data['contact_location'] ?? null,
                'facebook_url' => $data['facebook_url'] ?? null,
                'facebook' => $data['facebook_url'] ?? null,
                'linkedin_url' => $data['linkedin_url'] ?? null,
                'linkedin' => $data['linkedin_url'] ?? null,
                'youtube_url' => $data['youtube_url'] ?? null,
                'youtube' => $data['youtube_url'] ?? null,
                'seo_default_title' => $data['seo_title'] ?? null,
                'default_meta_title' => $data['seo_title'] ?? null,
                'seo_default_description' => $data['seo_description'] ?? null,
                'default_meta_description' => $data['seo_description'] ?? null,
                'default_meta_keywords' => $data['seo_keywords'] ?? null,
                'default_meta_title_en' => $data['seo_title_en'] ?? null,
                'default_meta_description_en' => $data['seo_description_en'] ?? null,
                'default_meta_keywords_en' => $data['seo_keywords_en'] ?? null,
                'robots_content' => $data['robots_content'] ?? null,
                'library_url' => $data['library_url'] ?? null,
                'legal_information' => $data['legal_information'] ?? null,
                'maintenance_mode' => ($data['maintenance_mode'] ?? false) ? 'true' : 'false',
                'logo_url' => $logo,
                'logo_dark_url' => $darkLogo,
                'favicon_url' => $favicon,
                'seo_default_og_image' => $openGraphImage,
                'default_og_image' => $openGraphImage,
            ];

            foreach ($values as $key => $value) {
                $this->saveSetting($key, $value);
            }
        });

        $this->fillForm();

        Notification::make()
            ->success()
            ->title('Paramètres enregistrés')
            ->body('Les modifications sont maintenant utilisées sur le site public.')
            ->send();
    }

    protected function fillForm(): void
    {
        $settings = Setting::query()->pluck('value', 'key');

        $this->form->fill([
            'site_name' => $settings->get('site_name'),
            'institution_name' => $settings->get('institution_name'),
            'parent_institution' => $settings->get('parent_institution'),
            'site_description' => $settings->get('site_description'),
            'footer_text' => $settings->get('footer_text'),
            'institution_name_en' => $settings->get('institution_name_en'),
            'site_description_en' => $settings->get('site_description_en'),
            'footer_text_en' => $settings->get('footer_text_en'),
            'ministerial_reference_label' => $settings->get('ministerial_reference_label'),
            'ministerial_reference' => $settings->get('ministerial_reference'),
            'ministerial_reference_label_en' => $settings->get('ministerial_reference_label_en'),
            'ministerial_reference_en' => $settings->get('ministerial_reference_en'),
            'academic_year' => $settings->get('academic_year'),
            'contact_email' => $settings->get('contact_email') ?: $settings->get('email'),
            'contact_phone' => $settings->get('contact_phone') ?: $settings->get('phone'),
            'phone_secondary' => $settings->get('phone_secondary'),
            'contact_address' => $settings->get('contact_address') ?: $settings->get('address'),
            'contact_location' => $settings->get('contact_location'),
            'facebook_url' => $settings->get('facebook_url') ?: $settings->get('facebook'),
            'linkedin_url' => $settings->get('linkedin_url') ?: $settings->get('linkedin'),
            'youtube_url' => $settings->get('youtube_url') ?: $settings->get('youtube'),
            'seo_title' => $settings->get('seo_default_title') ?: $settings->get('default_meta_title'),
            'seo_description' => $settings->get('seo_default_description') ?: $settings->get('default_meta_description'),
            'seo_keywords' => $settings->get('default_meta_keywords'),
            'seo_title_en' => $settings->get('default_meta_title_en'),
            'seo_description_en' => $settings->get('default_meta_description_en'),
            'seo_keywords_en' => $settings->get('default_meta_keywords_en'),
            'robots_content' => $settings->get('robots_content'),
            'library_url' => $settings->get('library_url'),
            'legal_information' => $settings->get('legal_information'),
            'maintenance_mode' => filter_var($settings->get('maintenance_mode'), FILTER_VALIDATE_BOOL),
            ...$this->imageState('logo', $settings->get('logo_url')),
            ...$this->imageState('logo_dark', $settings->get('logo_dark_url')),
            ...$this->imageState('favicon', $settings->get('favicon_url')),
            ...$this->imageState('og', $settings->get('seo_default_og_image') ?: $settings->get('default_og_image')),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveImageValue(string $prefix, array $data): ?string
    {
        if (($data["{$prefix}_source"] ?? 'computer') === 'gallery') {
            $media = Media::query()
                ->where('mime_type', 'like', 'image/%')
                ->find($data["{$prefix}_media_id"] ?? null);

            if (! $media) {
                throw ValidationException::withMessages([
                    "data.{$prefix}_media_id" => 'Sélectionnez une image valide dans la médiathèque.',
                ]);
            }

            Gate::authorize('view', $media);

            return $media->url;
        }

        $path = $data["{$prefix}_upload"] ?? null;

        if (blank($path)) {
            return $data["{$prefix}_current"] ?? null;
        }

        if (! is_string($path) || ! Storage::disk('public')->exists($path)) {
            throw ValidationException::withMessages([
                "data.{$prefix}_upload" => 'Le fichier téléversé est introuvable.',
            ]);
        }

        $media = Media::query()->where('disk', 'public')->where('path', $path)->first();

        if (! $media) {
            Gate::authorize('create', Media::class);

            $metadata = MediaResource::withStoredFileMetadata([
                'disk' => 'public',
                'path' => $path,
                'original_name' => $data["{$prefix}_original_name"] ?? basename($path),
                'alt_text' => $this->imageAltText($prefix, $data),
            ]);

            $media = Media::query()->create($metadata);
            OptimizeMediaImage::dispatch($media->id)->afterCommit();
        }

        return $media->url;
    }

    protected function saveSetting(string $key, mixed $value): void
    {
        $record = Setting::query()->firstOrNew(['key' => $key]);

        Gate::authorize($record->exists ? 'update' : 'create', $record->exists ? $record : Setting::class);

        $definition = self::settingDefinitions()[$key] ?? ['string', 'general', true];
        $record->fill([
            'value' => is_string($value) || is_null($value) ? $value : (string) $value,
            'type' => $definition[0],
            'group' => $definition[1],
            'is_public' => $definition[2],
        ])->save();
    }

    /** @return array<string, mixed> */
    protected function imageState(string $prefix, ?string $value): array
    {
        $path = $this->publicDiskPath($value);
        $media = $path ? Media::query()->where('disk', 'public')->where('path', $path)->first() : null;

        return [
            "{$prefix}_source" => $media ? 'gallery' : 'computer',
            "{$prefix}_upload" => $media ? null : $path,
            "{$prefix}_media_id" => $media?->id,
            "{$prefix}_current" => $value,
            "{$prefix}_original_name" => null,
        ];
    }

    protected function publicDiskPath(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $path = parse_url($value, PHP_URL_PATH);
        if (! is_string($path) || ! str_starts_with($path, '/storage/')) {
            return null;
        }

        $diskPath = ltrim(substr($path, strlen('/storage/')), '/');

        return Storage::disk('public')->exists($diskPath) ? $diskPath : null;
    }

    /** @param array<string, mixed> $data */
    protected function imageAltText(string $prefix, array $data): string
    {
        $siteName = trim((string) ($data['site_name'] ?? 'EDSP')) ?: 'EDSP';

        return match ($prefix) {
            'logo' => "Logo de {$siteName}",
            'logo_dark' => "Logo de {$siteName} pour fond sombre",
            'favicon' => "Icône de {$siteName}",
            default => "Image de partage de {$siteName}",
        };
    }

    protected static function imageSection(
        string $prefix,
        string $title,
        string $description,
        string $recommendation,
        Heroicon $icon,
        int $maxSize = 5120,
    ): Section {
        return Section::make($title)
            ->description($description)
            ->icon($icon)
            ->schema([
                Grid::make(3)->schema([
                    Placeholder::make("{$prefix}_preview")
                        ->label('Aperçu actuel')
                        ->content(fn (Get $get): HtmlString => self::imagePreview($prefix, $get))
                        ->columnSpan(1),
                    Grid::make(1)
                        ->schema([
                            Radio::make("{$prefix}_source")
                                ->label('Choisir la source')
                                ->options([
                                    'computer' => 'Depuis mon ordinateur',
                                    'gallery' => 'Depuis la médiathèque',
                                ])
                                ->descriptions([
                                    'computer' => 'Téléverser une nouvelle image.',
                                    'gallery' => 'Réutiliser une image déjà enregistrée.',
                                ])
                                ->required()
                                ->live(),
                            FileUpload::make("{$prefix}_upload")
                                ->label('Déposer une image')
                                ->helperText($recommendation)
                                ->disk('public')
                                ->directory('branding')
                                ->visibility('public')
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->maxSize($maxSize)
                                ->image()
                                ->imagePreviewHeight('180')
                                ->panelLayout('integrated')
                                ->openable()
                                ->downloadable()
                                ->storeFileNamesIn("{$prefix}_original_name")
                                ->visible(fn (Get $get): bool => $get->string("{$prefix}_source") === 'computer'),
                            Select::make("{$prefix}_media_id")
                                ->label('Image de la médiathèque')
                                ->placeholder('Rechercher une image…')
                                ->options(fn (): array => self::mediaOptions())
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->live()
                                ->required(fn (Get $get): bool => $get->string("{$prefix}_source") === 'gallery')
                                ->visible(fn (Get $get): bool => $get->string("{$prefix}_source") === 'gallery'),
                            Hidden::make("{$prefix}_current"),
                        ])
                        ->columnSpan(2),
                ]),
            ]);
    }

    protected static function imagePreview(string $prefix, Get $get): HtmlString
    {
        $url = $get->string("{$prefix}_current");

        if ($get->string("{$prefix}_source") === 'gallery' && $get("{$prefix}_media_id")) {
            $url = (string) (Media::query()->find($get("{$prefix}_media_id"))?->thumbnail_url ?? $url);
        }

        if (blank($url)) {
            return new HtmlString('<div style="display:grid;min-height:150px;place-items:center;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b;background:#f8fafc;text-align:center;padding:16px">Aucune image configurée</div>');
        }

        $safeUrl = e($url);

        return new HtmlString("<div style=\"display:grid;min-height:150px;place-items:center;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;padding:16px\"><img src=\"{$safeUrl}\" alt=\"Aperçu\" style=\"max-height:130px;max-width:100%;object-fit:contain\"></div>");
    }

    /** @return array<int, string> */
    protected static function mediaOptions(): array
    {
        return Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->latest()
            ->limit(250)
            ->get()
            ->mapWithKeys(fn (Media $media): array => [
                $media->id => trim($media->original_name.' · '.($media->width && $media->height ? "{$media->width} × {$media->height} px" : 'dimensions inconnues')),
            ])
            ->all();
    }

    /** @return array<string, array{string, string, bool}> */
    protected static function settingDefinitions(): array
    {
        return [
            'site_name' => ['string', 'general', true],
            'institution_name' => ['string', 'general', true],
            'parent_institution' => ['string', 'general', true],
            'site_description' => ['text', 'general', true],
            'footer_text' => ['text', 'general', true],
            'ministerial_reference_label' => ['string', 'legal', true],
            'ministerial_reference' => ['string', 'legal', true],
            'ministerial_reference_label_en' => ['string', 'legal', true],
            'ministerial_reference_en' => ['string', 'legal', true],
            'logo_url' => ['string', 'general', true],
            'logo_dark_url' => ['string', 'general', true],
            'favicon_url' => ['string', 'general', true],
            'academic_year' => ['string', 'academic', true],
            'contact_email' => ['string', 'contact', true],
            'email' => ['string', 'contact', true],
            'contact_phone' => ['string', 'contact', true],
            'phone' => ['string', 'contact', true],
            'phone_secondary' => ['string', 'contact', true],
            'contact_address' => ['string', 'contact', true],
            'address' => ['string', 'contact', true],
            'contact_location' => ['string', 'contact', true],
            'facebook_url' => ['string', 'social', true],
            'facebook' => ['string', 'social', true],
            'linkedin_url' => ['string', 'social', true],
            'linkedin' => ['string', 'social', true],
            'youtube_url' => ['string', 'social', true],
            'youtube' => ['string', 'social', true],
            'seo_default_title' => ['string', 'seo', true],
            'default_meta_title' => ['string', 'seo', true],
            'seo_default_description' => ['text', 'seo', true],
            'default_meta_description' => ['text', 'seo', true],
            'default_meta_keywords' => ['text', 'seo', true],
            'seo_default_og_image' => ['string', 'seo', true],
            'default_og_image' => ['string', 'seo', true],
            'robots_content' => ['text', 'seo', true],
            'library_url' => ['string', 'general', true],
            'legal_information' => ['text', 'legal', true],
            'maintenance_mode' => ['boolean', 'system', false],
        ];
    }
}

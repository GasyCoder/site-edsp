export type SectionFieldType =
    | 'boolean'
    | 'color-choice'
    | 'media'
    | 'number'
    | 'range'
    | 'richtext'
    | 'select'
    | 'text'
    | 'textarea'
    | 'url';

export type SectionFieldGroup = 'appearance' | 'content' | 'details' | 'title-style';

export interface SectionFieldOption {
    label: string;
    swatchClass?: string;
    value: string;
}

export interface SectionField {
    defaultValue?: boolean | number | string;
    group: SectionFieldGroup;
    help?: string;
    key: string;
    label: string;
    max?: number;
    min?: number;
    options?: SectionFieldOption[];
    required?: boolean;
    rows?: number;
    step?: number;
    type: SectionFieldType;
    unit?: string;
}

const textFields: SectionField[] = [
    { group: 'content', key: 'subtitle', label: 'Sur-titre', type: 'text' },
    { group: 'content', key: 'title', label: 'Titre', type: 'text' },
    { group: 'content', key: 'content', label: 'Description', rows: 6, type: 'textarea' },
];

const presentationTextFields: SectionField[] = [
    { group: 'content', key: 'subtitle', label: 'Libellé de section (ex. « Mot du directeur »)', type: 'text' },
    { group: 'content', key: 'title', label: 'Nom complet du directeur', type: 'text' },
    { group: 'content', key: 'content', label: 'Message du directeur', type: 'richtext' },
];

const callToActionFields: SectionField[] = [
    { group: 'content', key: 'button_text', label: 'Texte du bouton principal', type: 'text' },
    {
        group: 'content',
        help: "Utilisez un lien interne commençant par / ou une adresse https://.",
        key: 'button_url',
        label: 'Lien du bouton principal',
        type: 'url',
    },
];

const secondaryButtonFields: SectionField[] = [
    { group: 'details', key: 'settings.secondary_button_text', label: 'Texte du bouton secondaire', type: 'text' },
    {
        group: 'details',
        help: "Utilisez un lien interne commençant par / ou une adresse https://.",
        key: 'settings.secondary_button_url',
        label: 'Lien du bouton secondaire',
        type: 'url',
    },
];

const mediaField: SectionField = {
    group: 'content',
    help: 'Choisissez une image accessible déjà présente dans la médiathèque.',
    key: 'image_id',
    label: 'Image principale',
    type: 'media',
};

const layoutFields: SectionField[] = [
    {
        group: 'appearance',
        help: "Les sections sont affichées dans l'ordre croissant.",
        key: 'position',
        label: 'Ordre de la section',
        type: 'number',
    },
    {
        group: 'appearance',
        key: 'settings.background',
        label: 'Couleur de fond',
        options: [
            { label: 'Blanc', value: 'white' },
            { label: 'Gris très clair', value: 'light' },
            { label: 'Bleu institutionnel', value: 'blue' },
        ],
        type: 'select',
    },
    {
        group: 'appearance',
        key: 'settings.alignment',
        label: 'Alignement du contenu',
        options: [
            { label: 'À gauche', value: 'left' },
            { label: 'Centré', value: 'center' },
        ],
        type: 'select',
    },
    {
        group: 'appearance',
        key: 'settings.container',
        label: 'Largeur du contenu',
        options: [
            { label: 'Standard', value: 'default' },
            { label: 'Étroite', value: 'narrow' },
            { label: 'Large', value: 'wide' },
        ],
        type: 'select',
    },
    { group: 'appearance', key: 'is_visible', label: 'Afficher cette section', type: 'boolean' },
];

function detailText(key: string, label: string, rows?: number): SectionField {
    return { group: 'details', key: `settings.${key}`, label, rows, type: rows ? 'textarea' : 'text' };
}

function heroContentText(key: string, label: string, help?: string): SectionField {
    return { group: 'content', key: `settings.${key}`, label, help, type: 'text' };
}

function heroHighlightColor(key: string, label: string, defaultValue: HighlightColor): SectionField {
    return {
        defaultValue,
        group: 'title-style',
        key: `settings.${key}`,
        label,
        help: 'Cliquez directement sur une couleur. La coche indique le choix actuellement appliqué.',
        options: [
            { label: 'Vert EDSP', swatchClass: 'bg-edsp-green', value: 'green' },
            { label: 'Bleu institutionnel', swatchClass: 'bg-institutional', value: 'institutional' },
            { label: 'Or', swatchClass: 'bg-gold', value: 'gold' },
        ],
        type: 'color-choice',
    };
}

type HighlightColor = 'gold' | 'green' | 'institutional';

const heroFields: SectionField[] = [
    {
        group: 'title-style',
        help: 'La taille mobile reste automatiquement limitée pour conserver un titre lisible.',
        key: 'settings.title_font_size',
        label: 'Taille du titre',
        max: 64,
        min: 32,
        step: 1,
        type: 'range',
        unit: 'px',
    },
    {
        ...heroContentText('title_highlight_1', 'Première expression à surligner', 'Saisissez exactement un passage du titre, ou laissez vide pour retirer ce surlignage.'),
        group: 'title-style',
    },
    heroHighlightColor('title_highlight_1_color', 'Couleur du premier surlignage', 'green'),
    {
        ...heroContentText('title_highlight_2', 'Deuxième expression à surligner', 'Saisissez exactement un passage du titre, ou laissez vide pour retirer ce surlignage.'),
        group: 'title-style',
    },
    heroHighlightColor('title_highlight_2_color', 'Couleur du deuxième surlignage', 'institutional'),
    heroContentText('kicker_text', 'Texte fixe avant les mentions (ex. « Deux mentions : »)'),
    heroContentText('rotating_item_1', 'Première mention animée (ex. « Droit »)'),
    heroContentText('rotating_item_2', 'Deuxième mention animée (ex. « Sciences Politiques »)'),
    heroContentText('location_text', 'Localisation affichée'),
    heroContentText('degree_text', 'Diplômes affichés'),
    detailText('visual_eyebrow', 'Sur-titre du visuel'),
    detailText('visual_title', 'Texte principal du visuel', 3),
    detailText('visual_program_1', 'Parcours du visuel 1'),
    detailText('visual_program_2', 'Parcours du visuel 2'),
    detailText('visual_footer', 'Légende sur la photo'),
    detailText('alt_text', 'Texte alternatif de l’image'),
    ...secondaryButtonFields,
];

const directorMessageFields: SectionField[] = [
    {
        defaultValue: 100,
        group: 'details',
        help: 'Agrandissez le portrait sans modifier le fichier original.',
        key: 'settings.image_zoom',
        label: 'Zoom du portrait',
        max: 200,
        min: 50,
        step: 1,
        type: 'range',
        unit: '%',
    },
    {
        defaultValue: 50,
        group: 'details',
        help: 'Déplacez le cadrage vers la gauche ou la droite.',
        key: 'settings.image_position_x',
        label: 'Position horizontale',
        max: 100,
        min: 0,
        step: 1,
        type: 'range',
        unit: '%',
    },
    {
        defaultValue: 50,
        group: 'details',
        help: 'Déplacez le cadrage vers le haut ou le bas.',
        key: 'settings.image_position_y',
        label: 'Position verticale',
        max: 100,
        min: 0,
        step: 1,
        type: 'range',
        unit: '%',
    },
    detailText('director_position', 'Fonction du directeur'),
    detailText('director_signature', 'Formule de clôture du message'),
    detailText('alt_text', 'Texte alternatif du portrait'),
];

const presentationFields: SectionField[] = [
    detailText('feature_1_title', 'Carte 1 — titre'),
    detailText('feature_1_description', 'Carte 1 — description', 3),
    detailText('feature_2_title', 'Carte 2 — titre'),
    detailText('feature_2_description', 'Carte 2 — description', 3),
    detailText('feature_3_title', 'Carte 3 — titre'),
    detailText('feature_3_description', 'Carte 3 — description', 3),
];

const statsFields: SectionField[] = [
    detailText('stat_1_label', 'Libellé du nombre de parcours'),
    detailText('stat_2_label', 'Libellé des niveaux'),
    detailText('stat_3_label', 'Troisième chiffre clé'),
    detailText('stat_4_label', 'Quatrième chiffre clé'),
];

const admissionsFields: SectionField[] = [
    ...[1, 2, 3, 4].flatMap((number) => [
        detailText(`step_${number}_title`, `Étape ${number} — titre`),
        detailText(`step_${number}_description`, `Étape ${number} — description`, 3),
    ]),
    detailText('info_text', 'Note d’information', 3),
    detailText('campaign_fallback_text', 'Texte lorsqu’aucune campagne n’est ouverte', 2),
    detailText('campaign_link_text', 'Texte du lien vers les avis'),
    { group: 'details', key: 'settings.campaign_link_url', label: 'Lien vers les avis', type: 'url' },
    ...secondaryButtonFields,
];

const studentLifeFields: SectionField[] = [
    {
        group: 'content',
        help: 'Visuel affiché dans le cadre supérieur droit.',
        key: 'settings.secondary_media_id',
        label: 'Photo conférence',
        type: 'media',
    },
    {
        group: 'content',
        help: 'Visuel affiché dans le cadre inférieur droit.',
        key: 'settings.tertiary_media_id',
        label: 'Photo événement étudiant',
        type: 'media',
    },
    ...[1, 2, 3, 4, 5, 6].map((number) => detailText(`item_${number}`, `Activité ${number}`)),
    detailText('secondary_media_label', 'Légende du petit visuel 1'),
    detailText('tertiary_media_label', 'Légende du petit visuel 2'),
];

export function fieldsForSection(sectionType: string): SectionField[] {
    const normalized = sectionType.toLocaleLowerCase('fr');
    const isStats = normalized.includes('stats');
    const isDirectorMessage = normalized.includes('director-message');
    const isPresentation = normalized.includes('presentation');
    const supportsMedia = ['banner', 'director-message', 'gallery', 'hero', 'image', 'student-life', 'student_life', 'team']
        .some((type) => normalized.includes(type));
    const supportsCallToAction = [
        'admission',
        'banner',
        'call-to-action',
        'cta',
        'hero',
        'library',
        'news',
        'partner',
        'presentation',
        'program',
        'student-life',
        'student_life',
        'team',
    ].some((type) => normalized.includes(type));

    const typeFields = isDirectorMessage
        ? directorMessageFields
        : normalized.includes('hero')
        ? heroFields
        : isPresentation
          ? presentationFields
          : isStats
            ? statsFields
            : normalized.includes('admission')
              ? admissionsFields
              : normalized.includes('student-life') || normalized.includes('student_life')
                ? studentLifeFields
                : normalized.includes('program')
                  ? [detailText('footer_label', 'Libellé avant les diplômes')]
                  : normalized.includes('partner')
                    ? [detailText('partner_link_text', 'Texte du lien de chaque partenaire')]
                  : normalized.includes('call-to-action') || normalized.includes('cta')
                    ? secondaryButtonFields
                    : [];

    return [
        ...(isStats || isPresentation ? [] : isDirectorMessage ? presentationTextFields : textFields),
        ...(supportsMedia ? [mediaField] : []),
        ...(supportsCallToAction ? callToActionFields : []),
        ...typeFields,
        ...layoutFields,
    ];
}

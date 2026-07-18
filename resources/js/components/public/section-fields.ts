export type SectionFieldType =
    | 'boolean'
    | 'media'
    | 'number'
    | 'select'
    | 'text'
    | 'textarea'
    | 'url';

export type SectionFieldGroup = 'appearance' | 'content' | 'details';

export interface SectionFieldOption {
    label: string;
    value: string;
}

export interface SectionField {
    group: SectionFieldGroup;
    help?: string;
    key: string;
    label: string;
    options?: SectionFieldOption[];
    required?: boolean;
    rows?: number;
    type: SectionFieldType;
}

const textFields: SectionField[] = [
    { group: 'content', key: 'subtitle', label: 'Sur-titre', type: 'text' },
    { group: 'content', key: 'title', label: 'Titre', type: 'text' },
    { group: 'content', key: 'content', label: 'Description', rows: 6, type: 'textarea' },
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

const heroFields: SectionField[] = [
    detailText('kicker_text', 'Libellé avant les parcours'),
    detailText('rotating_item_1', 'Parcours animé 1'),
    detailText('rotating_item_2', 'Parcours animé 2'),
    detailText('location_text', 'Localisation affichée'),
    detailText('degree_text', 'Diplômes affichés'),
    detailText('visual_eyebrow', 'Sur-titre du visuel'),
    detailText('visual_title', 'Texte principal du visuel', 3),
    detailText('visual_program_1', 'Parcours du visuel 1'),
    detailText('visual_program_2', 'Parcours du visuel 2'),
    detailText('visual_footer', 'Légende sur la photo'),
    detailText('alt_text', 'Texte alternatif de l’image'),
    ...secondaryButtonFields,
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
    ...[1, 2, 3, 4, 5, 6].map((number) => detailText(`item_${number}`, `Activité ${number}`)),
    detailText('secondary_media_label', 'Légende du petit visuel 1'),
    detailText('tertiary_media_label', 'Légende du petit visuel 2'),
];

export function fieldsForSection(sectionType: string): SectionField[] {
    const normalized = sectionType.toLocaleLowerCase('fr');
    const isStats = normalized.includes('stats');
    const supportsMedia = ['banner', 'gallery', 'hero', 'image', 'presentation', 'student-life', 'student_life', 'team']
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

    const typeFields = normalized.includes('hero')
        ? heroFields
        : normalized.includes('presentation')
          ? presentationFields
          : isStats
            ? statsFields
            : normalized.includes('admission')
              ? admissionsFields
              : normalized.includes('student-life') || normalized.includes('student_life')
                ? studentLifeFields
                : normalized.includes('program')
                  ? [detailText('footer_label', 'Libellé avant les niveaux')]
                  : normalized.includes('partner')
                    ? [detailText('partner_link_text', 'Texte du lien de chaque partenaire')]
                  : normalized.includes('call-to-action') || normalized.includes('cta')
                    ? secondaryButtonFields
                    : [];

    return [
        ...(isStats ? [] : textFields),
        ...(supportsMedia ? [mediaField] : []),
        ...(supportsCallToAction ? callToActionFields : []),
        ...typeFields,
        ...layoutFields,
    ];
}

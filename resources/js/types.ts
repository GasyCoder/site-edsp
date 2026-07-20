export type Nullable<T> = T | null;

export type SectionBackground = 'white' | 'light' | 'blue';
export type SectionAlignment = 'left' | 'center';
export type SectionContainer = 'default' | 'narrow' | 'wide';

export interface MediaAsset {
    id: number;
    alt_text?: Nullable<string>;
    caption?: Nullable<string>;
    image_url?: Nullable<string>;
    photo_url?: Nullable<string>;
    thumbnail_url?: Nullable<string>;
    url?: Nullable<string>;
    path?: Nullable<string>;
}

export interface PublicDocument {
    id: number;
    title: string;
    description?: Nullable<string>;
    category?: Nullable<string>;
    original_name?: Nullable<string>;
    mime_type?: Nullable<string>;
    size?: Nullable<number>;
    download_url?: Nullable<string>;
    preview_url?: Nullable<string>;
    published_at?: Nullable<string>;
}

export interface GalleryImage {
    id: number;
    title?: Nullable<string>;
    alt_text?: Nullable<string>;
    caption?: Nullable<string>;
    is_visible?: boolean;
    media?: Nullable<MediaAsset>;
}

export interface PublicGallery {
    id: number;
    title: string;
    slug?: string;
    description?: Nullable<string>;
    cover_image?: Nullable<MediaAsset>;
    cover_image_url?: Nullable<string>;
    is_visible?: boolean;
    images?: GalleryImage[];
}

export interface SectionSettings {
    alignment?: SectionAlignment;
    alt_text?: string;
    background?: SectionBackground;
    container?: SectionContainer;
    image_url?: string;
    [key: string]: string | number | boolean | null | undefined;
}

export interface Section {
    id: number;
    page_id?: number;
    section_key: string;
    section_type: string;
    title: Nullable<string>;
    subtitle: Nullable<string>;
    content: Nullable<string>;
    image_id?: Nullable<number>;
    image?: Nullable<MediaAsset>;
    image_url?: Nullable<string>;
    secondary_image_url?: Nullable<string>;
    tertiary_image_url?: Nullable<string>;
    button_text: Nullable<string>;
    button_url: Nullable<string>;
    settings: SectionSettings | null;
    position?: number;
    is_visible: boolean;
}

export interface Page {
    id: number;
    title: string;
    slug: string;
    template?: string;
    meta_title: Nullable<string>;
    meta_description: Nullable<string>;
    meta_keywords?: Nullable<string>;
    canonical_url?: Nullable<string>;
    robots_index?: boolean;
    robots_follow?: boolean;
    og_title?: Nullable<string>;
    og_description?: Nullable<string>;
    og_image_url?: Nullable<string>;
    sections: Section[];
}

export interface AcademicLevel {
    id: number;
    code: string;
    nom: string;
    ordre?: number;
}

export interface AcademicPathwayLevel {
    id: number;
    is_active: boolean;
    is_common_core?: boolean;
    level?: Nullable<AcademicLevel>;
}

export interface AcademicPathway {
    id: number;
    code: string;
    nom: string;
    description?: Nullable<string>;
    level_links?: AcademicPathwayLevel[];
}

export interface AcademicMention {
    id: number;
    code: string;
    nom: string;
    description?: Nullable<string>;
    parcours?: AcademicPathway[];
    programs?: Program[];
}

export interface Program {
    id: number;
    title: string;
    slug: string;
    level: string;
    domain?: Nullable<string>;
    mention?: Nullable<string>;
    track?: Nullable<string>;
    description: string;
    objectives?: Nullable<string>;
    admission_requirements?: Nullable<string>;
    skills?: Nullable<string>;
    careers?: Nullable<string>;
    duration?: Nullable<string>;
    curriculum?: Nullable<string>;
    manager?: Nullable<string>;
    image_id?: Nullable<number>;
    image?: Nullable<MediaAsset>;
    image_url?: Nullable<string>;
    meta_title?: Nullable<string>;
    meta_description?: Nullable<string>;
    documents?: PublicDocument[];
    mention_record?: Nullable<AcademicMention>;
    parcours_levels?: Array<{
        id: number;
        label?: string;
        parcours?: Nullable<{
            id: number;
            code: string;
            nom: string;
        }>;
        level?: Nullable<{
            id: number;
            code: string;
            nom: string;
        }>;
    }>;
}

export interface NewsCategory {
    id?: number;
    name: string;
    slug?: string;
}

export interface Article {
    id: number;
    title: string;
    slug: string;
    excerpt: string;
    content?: string;
    published_at: Nullable<string>;
    category?: Nullable<NewsCategory>;
    category_name?: Nullable<string>;
    featured_image?: Nullable<MediaAsset>;
    featured_image_url?: Nullable<string>;
    image_url?: Nullable<string>;
    meta_title?: Nullable<string>;
    meta_description?: Nullable<string>;
    is_featured?: boolean;
    author_name?: Nullable<string>;
    documents?: PublicDocument[];
    galleries?: PublicGallery[];
}

export interface TeamMember {
    id: number;
    first_name: string;
    last_name: string;
    position: string;
    biography?: Nullable<string>;
    email?: Nullable<string>;
    phone?: Nullable<string>;
    photo?: Nullable<MediaAsset>;
    photo_url?: Nullable<string>;
}

export interface Testimonial {
    id: number;
    author_name: string;
    author_role?: Nullable<string>;
    content: string;
    photo?: Nullable<MediaAsset>;
    photo_url?: Nullable<string>;
}

export interface Partner {
    id: number;
    name: string;
    description?: Nullable<string>;
    url?: Nullable<string>;
    logo?: Nullable<MediaAsset>;
    logo_url?: Nullable<string>;
    logo_dark_url?: Nullable<string>;
}

export interface AdmissionCampaign {
    id: number;
    title: string;
    academic_year?: string;
    opens_at?: Nullable<string>;
    closes_at?: Nullable<string>;
    instructions?: Nullable<string>;
    tutorial_video_url?: Nullable<string>;
    required_documents?: RequiredDocument[] | null;
    programs?: Program[];
}

export interface RequiredDocument {
    key: string;
    label: string;
    required: boolean;
}

export interface SiteSettings {
    address?: Nullable<string>;
    default_meta_description?: Nullable<string>;
    default_meta_keywords?: Nullable<string>;
    default_meta_title?: Nullable<string>;
    default_og_image?: Nullable<string>;
    email?: Nullable<string>;
    facebook?: Nullable<string>;
    favicon_url?: Nullable<string>;
    footer_text?: Nullable<string>;
    institution_name?: Nullable<string>;
    library_url?: Nullable<string>;
    linkedin?: Nullable<string>;
    logo_url?: Nullable<string>;
    ministerial_reference?: Nullable<string>;
    ministerial_reference_label?: Nullable<string>;
    phone?: Nullable<string>;
    phone_secondary?: Nullable<string>;
    site_description?: Nullable<string>;
    site_name?: Nullable<string>;
    youtube?: Nullable<string>;
    [key: string]: string | number | boolean | null | undefined;
}

export interface PaginationLink {
    active: boolean;
    label: string;
    url: Nullable<string>;
}

export interface Paginated<T> {
    current_page?: number;
    data: T[];
    from?: Nullable<number>;
    last_page?: number;
    links?: PaginationLink[];
    next_page_url?: Nullable<string>;
    per_page?: number;
    prev_page_url?: Nullable<string>;
    to?: Nullable<number>;
    total?: number;
}

export interface SharedPageProps {
    auth?: {
        user: Nullable<{ id: number; name: string; email: string }>;
        canAccessAdmin?: boolean;
        canEdit?: boolean;
        canEditSettings?: boolean;
    };
    flash?: {
        newsletter?: Nullable<{
            status: 'error' | 'pending' | 'verified';
            message: string;
        }>;
        success?: Nullable<string>;
    };
    settings?: SiteSettings;
    navigationBrochures?: Array<{
        id: number;
        title: string;
        url: string;
    }>;
    locale?: 'fr' | 'en';
    locales?: Array<{
        code: 'fr' | 'en';
        label: string;
        shortLabel: string;
    }>;
}

export interface SeoData {
    canonical?: Nullable<string>;
    description?: Nullable<string>;
    keywords?: Nullable<string>;
    og_description?: Nullable<string>;
    og_image?: Nullable<string>;
    og_title?: Nullable<string>;
    robots?: Nullable<string>;
    schema?: Record<string, unknown>;
    title?: Nullable<string>;
}

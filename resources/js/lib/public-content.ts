import type { MediaAsset, Section, SiteSettings } from '../types';

export function mediaUrl(
    source:
        | null
        | undefined
        | MediaAsset
        | (Partial<Section> & {
              featured_image_url?: string | null;
              photo_url?: string | null;
          }),
): string | null {
    if (!source) {
        return null;
    }

    const direct = 'featured_image_url' in source
        ? source.featured_image_url
        : 'photo_url' in source
          ? source.photo_url
          : 'image_url' in source
            ? source.image_url
            : null;

    if (typeof direct === 'string' && direct.length > 0) {
        return direct;
    }

    if ('image' in source && source.image) {
        return mediaUrl(source.image);
    }

    if ('url' in source && typeof source.url === 'string' && source.url.length > 0) {
        return source.url;
    }

    return null;
}

export function mediaThumbnailUrl(source: MediaAsset | null | undefined): string | null {
    if (source?.thumbnail_url) {
        return source.thumbnail_url;
    }

    return mediaUrl(source);
}

export function formatPublicDate(value?: string | null): string | null {
    if (!value) {
        return null;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
}

export function setting(
    settings: SiteSettings | undefined,
    key: keyof SiteSettings,
    fallback: string,
): string {
    const value = settings?.[key];

    return typeof value === 'string' && value.trim().length > 0 ? value : fallback;
}

export function safePublicUrl(value: string | null | undefined): string | null {
    if (!value) {
        return null;
    }

    if (/^\/(?!\/)/.test(value)) {
        return value;
    }

    try {
        const url = new URL(value);

        return ['http:', 'https:'].includes(url.protocol) ? value : null;
    } catch {
        return null;
    }
}

export function sectionSettings(section?: Section | null) {
    return section?.settings ?? {};
}

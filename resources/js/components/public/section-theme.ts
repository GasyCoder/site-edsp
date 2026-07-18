import type { Section, SectionAlignment, SectionBackground, SectionContainer } from '../../types';

export function sectionBackground(section: Section | null | undefined, fallback: SectionBackground = 'white'): SectionBackground {
    return section?.settings?.background || fallback;
}

export function sectionBackgroundClass(section: Section | null | undefined, fallback: SectionBackground = 'white'): string {
    return {
        blue: 'bg-navy',
        light: 'bg-soft',
        white: 'bg-white',
    }[sectionBackground(section, fallback)];
}

export function sectionContainer(section: Section | null | undefined, fallback: SectionContainer = 'wide'): SectionContainer {
    return section?.settings?.container || fallback;
}

export function sectionContainerClass(section: Section | null | undefined, fallback: SectionContainer = 'wide'): string {
    return {
        default: 'max-w-6xl',
        narrow: 'max-w-4xl',
        wide: 'max-w-7xl',
    }[sectionContainer(section, fallback)];
}

export function sectionAlignment(section: Section | null | undefined, fallback: SectionAlignment = 'left'): SectionAlignment {
    return section?.settings?.alignment || fallback;
}

export function isDarkSection(section: Section | null | undefined, fallback: SectionBackground = 'white'): boolean {
    return sectionBackground(section, fallback) === 'blue';
}

export function sectionSetting(section: Section | null | undefined, key: string, fallback: string): string {
    const value = section?.settings?.[key];

    return typeof value === 'string' && value.trim() ? value : fallback;
}

import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { SharedPageProps } from '../types';

export type PublicLocale = 'fr' | 'en';

export function useI18n() {
    const page = usePage();
    const locale = computed<PublicLocale>(() => {
        const value = (page.props as SharedPageProps).locale;

        return value === 'en' ? 'en' : 'fr';
    });

    const languageTag = computed(() => locale.value === 'en' ? 'en-GB' : 'fr-FR');
    const tr = (french: string, english: string): string => locale.value === 'en' ? english : french;

    const setLocale = (nextLocale: PublicLocale): void => {
        if (nextLocale === locale.value) return;

        router.post(`/langue/${nextLocale}`, {}, {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                document.documentElement.lang = nextLocale;
            },
        });
    };

    return { languageTag, locale, setLocale, tr };
}

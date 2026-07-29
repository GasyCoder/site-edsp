<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { SiteSettings } from '../../types';
import { useI18n } from '../../lib/i18n';

const props = withDefaults(
    defineProps<{
        canonicalUrl?: string | null;
        description?: string | null;
        imageUrl?: string | null;
        keywords?: string | null;
        noFollow?: boolean;
        noIndex?: boolean;
        openGraphDescription?: string | null;
        openGraphTitle?: string | null;
        structuredData?: Record<string, unknown> | null;
        title?: string | null;
        type?: 'website' | 'article';
    }>(),
    {
        canonicalUrl: null,
        description: null,
        imageUrl: null,
        keywords: null,
        noFollow: false,
        noIndex: false,
        openGraphDescription: null,
        openGraphTitle: null,
        structuredData: null,
        title: null,
        type: 'website',
    },
);

const page = usePage();
const { tr } = useI18n();
const sharedSettings = computed(() => (page.props.settings ?? {}) as SiteSettings);
const normalizeTitle = (title: string): string => title.replace(/\s*[—–]\s*/g, ' | ').trim();
const resolvedTitle = computed(
    () => normalizeTitle(
        props.title ||
        sharedSettings.value.default_meta_title ||
        sharedSettings.value.site_name ||
        tr('EDSP | Université de Mahajanga', 'EDSP | University of Mahajanga'),
    ),
);
const resolvedDescription = computed(
    () =>
        props.description ||
        sharedSettings.value.default_meta_description ||
        sharedSettings.value.site_description ||
        tr("École de Droit et Science Politique de l'Université de Mahajanga.", 'University of Mahajanga School of Law and Political Science.'),
);
const resolvedKeywords = computed(() => props.keywords || sharedSettings.value.default_meta_keywords || null);
const resolvedImage = computed(
    () => props.imageUrl || sharedSettings.value.default_og_image || null,
);
const resolvedOpenGraphTitle = computed(() => normalizeTitle(props.openGraphTitle || resolvedTitle.value));
const resolvedOpenGraphDescription = computed(() => props.openGraphDescription || resolvedDescription.value);
const serializedStructuredData = computed(() => props.structuredData ? JSON.stringify(props.structuredData) : null);
const robots = computed(
    () => `${props.noIndex ? 'noindex' : 'index'}, ${props.noFollow ? 'nofollow' : 'follow'}`,
);
const canonical = computed(() => {
    if (props.canonicalUrl) {
        return props.canonicalUrl;
    }

    if (typeof window === 'undefined') {
        return page.url;
    }

    return new URL(page.url, window.location.origin).toString();
});
</script>

<template>
    <Head :title="resolvedTitle">
        <meta head-key="description" name="description" :content="resolvedDescription" />
        <meta
            head-key="robots"
            name="robots"
            :content="robots"
        />
        <meta v-if="resolvedKeywords" head-key="keywords" name="keywords" :content="resolvedKeywords" />
        <link head-key="canonical" rel="canonical" :href="canonical" />
        <meta head-key="og:title" property="og:title" :content="resolvedOpenGraphTitle" />
        <meta head-key="og:description" property="og:description" :content="resolvedOpenGraphDescription" />
        <meta head-key="og:type" property="og:type" :content="type" />
        <meta head-key="og:url" property="og:url" :content="canonical" />
        <meta v-if="resolvedImage" head-key="og:image" property="og:image" :content="resolvedImage" />
        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter:title" name="twitter:title" :content="resolvedOpenGraphTitle" />
        <meta
            head-key="twitter:description"
            name="twitter:description"
            :content="resolvedOpenGraphDescription"
        />
        <meta
            v-if="resolvedImage"
            head-key="twitter:image"
            name="twitter:image"
            :content="resolvedImage"
        />
        <component
            v-if="serializedStructuredData"
            :is="'script'"
            head-key="structured-data"
            type="application/ld+json"
            v-text="serializedStructuredData"
        />
    </Head>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    ChevronRight,
    Download,
    Eye,
    FileCheck2,
    FileText,
    LibraryBig,
    Search,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';
import PublicLayout from '../../layouts/PublicLayout.vue';
import SeoHead from '../../components/public/SeoHead.vue';
import type { PublicDocument, SeoData } from '../../types';
import { safePublicUrl } from '../../lib/public-content';
import { useI18n } from '../../lib/i18n';

type Paginator<T> = {
    current_page: number;
    data: T[];
    from: number | null;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
    to: number | null;
    total: number;
};

const props = withDefaults(defineProps<{
    categories?: string[];
    documents: Paginator<PublicDocument>;
    filters?: { q?: string; category?: string };
    seo?: SeoData;
}>(), {
    categories: () => [],
    filters: () => ({}),
    seo: () => ({}),
});

const { languageTag, tr } = useI18n();
const search = ref(props.filters.q || '');
const category = ref(props.filters.category || '');

const applyFilters = (): void => {
    router.get('/documents', {
        q: search.value || undefined,
        category: category.value || undefined,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const selectCategory = (value: string): void => {
    category.value = value;
    applyFilters();
};

const clearFilters = (): void => {
    search.value = '';
    category.value = '';
    applyFilters();
};

const fileExtension = (document: PublicDocument): string => {
    const filename = document.original_name || '';
    const extension = filename.includes('.') ? filename.split('.').pop() : null;

    return (extension || (document.mime_type === 'application/pdf' ? 'PDF' : 'Fichier')).toUpperCase();
};

const formatSize = (bytes?: number | null): string | null => {
    if (!bytes || bytes < 1) return null;
    if (bytes < 1024 * 1024) return `${Math.ceil(bytes / 1024)} Ko`;

    return `${(bytes / (1024 * 1024)).toLocaleString(languageTag.value, { maximumFractionDigits: 1 })} Mo`;
};

const formatDate = (date?: string | null): string | null => {
    if (!date) return null;
    const parsed = new Date(date);
    if (Number.isNaN(parsed.getTime())) return null;

    return new Intl.DateTimeFormat(languageTag.value, {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(parsed);
};

const downloadUrl = (document: PublicDocument): string | null => safePublicUrl(document.download_url);
const previewUrl = (document: PublicDocument): string | null => safePublicUrl(document.preview_url);
</script>

<template>
    <SeoHead
        :title="seo.title || tr('Documents publics | EDSP', 'Public documents | EDSP')"
        :description="seo.description || tr('Consultez les documents officiels publiés par l’EDSP.', 'Browse official documents published by EDSP.')"
        :canonical-url="seo.canonical"
        :structured-data="seo.schema"
    />

    <PublicLayout>
        <header class="relative isolate overflow-hidden bg-soft">
            <div class="absolute inset-y-0 right-0 -z-10 hidden w-[32%] bg-navy lg:block" aria-hidden="true" />
            <div class="absolute -left-24 -top-32 -z-10 size-80 rounded-full bg-edsp-green/10 blur-3xl" aria-hidden="true" />

            <div class="mx-auto max-w-7xl px-6 py-14 sm:py-16 lg:py-20">
                <nav :aria-label="tr('Fil d’Ariane', 'Breadcrumb')" class="mb-8">
                    <ol class="flex items-center gap-2 text-sm text-slate-500">
                        <li><Link href="/" class="transition hover:text-edsp-green">{{ tr('Accueil', 'Home') }}</Link></li>
                        <li aria-hidden="true"><ChevronRight :size="15" /></li>
                        <li class="font-semibold text-navy" aria-current="page">{{ tr('Documents publics', 'Public documents') }}</li>
                    </ol>
                </nav>

                <div class="max-w-3xl">
                    <p class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-edsp-green">
                        {{ tr('Centre de ressources', 'Resource centre') }}
                    </p>
                    <h1 class="text-3xl font-extrabold leading-tight text-navy sm:text-4xl lg:text-5xl">
                        {{ tr('Documents et publications', 'Documents and publications') }}
                    </h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                        {{ tr('Retrouvez dans un même espace les formulaires, règlements, brochures et ressources officiellement publiés par l’EDSP.', 'Find forms, regulations, brochures and resources officially published by EDSP in one place.') }}
                    </p>
                </div>
            </div>
        </header>

        <section class="bg-white px-4 py-14 sm:px-6 sm:py-18" aria-labelledby="public-documents-title">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-edsp-green">{{ tr('Publications officielles', 'Official publications') }}</p>
                        <h2 id="public-documents-title" class="mt-2 text-2xl font-bold text-navy sm:text-3xl">
                            {{ tr('Ressources disponibles', 'Available resources') }}
                        </h2>
                        <p class="mt-2 text-sm text-slate-500">
                            {{ documents.total }} {{ tr(documents.total > 1 ? 'documents publiés' : 'document publié', documents.total > 1 ? 'published documents' : 'published document') }}
                        </p>
                    </div>

                    <form class="flex w-full max-w-2xl flex-col gap-3 sm:flex-row" role="search" @submit.prevent="applyFilters">
                        <label class="relative min-w-0 flex-1">
                            <span class="sr-only">{{ tr('Rechercher un document', 'Search documents') }}</span>
                            <Search :size="18" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                            <input
                                v-model="search"
                                type="search"
                                class="form-control h-11 pl-10"
                                :placeholder="tr('Titre, catégorie ou mot-clé…', 'Title, category or keyword…')"
                            >
                        </label>
                        <button type="submit" class="button-primary h-11 justify-center px-5">
                            <Search :size="17" aria-hidden="true" />
                            {{ tr('Rechercher', 'Search') }}
                        </button>
                    </form>
                </div>

                <div v-if="categories.length" class="mt-7 flex flex-wrap gap-2" :aria-label="tr('Filtrer par catégorie', 'Filter by category')">
                    <button
                        type="button"
                        class="rounded-full border px-4 py-2 text-sm font-semibold transition"
                        :class="category === '' ? 'border-edsp-green bg-edsp-green text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-edsp-green hover:text-edsp-green'"
                        @click="selectCategory('')"
                    >
                        {{ tr('Tous', 'All') }}
                    </button>
                    <button
                        v-for="item in categories"
                        :key="item"
                        type="button"
                        class="rounded-full border px-4 py-2 text-sm font-semibold transition"
                        :class="category === item ? 'border-edsp-green bg-edsp-green text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-edsp-green hover:text-edsp-green'"
                        @click="selectCategory(item)"
                    >
                        {{ item }}
                    </button>
                </div>

                <div v-if="documents.data.length" class="mt-9 grid gap-4 lg:grid-cols-2">
                    <article
                        v-for="document in documents.data"
                        :key="document.id"
                        class="group flex min-w-0 flex-col rounded-xl border border-slate-200 bg-white p-5 transition hover:border-edsp-green/40 hover:shadow-[0_12px_30px_rgba(11,31,85,0.08)] sm:p-6"
                    >
                        <div class="flex min-w-0 items-start gap-4">
                            <span class="grid size-12 flex-none place-items-center rounded-xl bg-navy/5 text-navy ring-1 ring-navy/5">
                                <FileText :size="23" aria-hidden="true" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                                    <span v-if="document.category" class="text-edsp-green">{{ document.category }}</span>
                                    <span class="rounded bg-slate-100 px-2 py-1 text-slate-500">{{ fileExtension(document) }}</span>
                                </div>
                                <h3 class="mt-2 text-lg font-bold leading-snug text-navy">{{ document.title }}</h3>
                                <p v-if="document.description" class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600">{{ document.description }}</p>
                            </div>
                        </div>

                        <div class="mt-auto flex flex-col gap-4 pt-5 sm:flex-row sm:items-end sm:justify-between">
                            <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                                <span v-if="formatSize(document.size)">{{ formatSize(document.size) }}</span>
                                <span v-if="formatDate(document.published_at)">{{ tr('Publié le', 'Published') }} {{ formatDate(document.published_at) }}</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a v-if="previewUrl(document)" :href="previewUrl(document)!" target="_blank" rel="noopener" class="button-secondary justify-center px-4 py-2.5">
                                    <Eye :size="17" aria-hidden="true" /> {{ tr('Consulter', 'View') }}
                                </a>
                                <a v-if="downloadUrl(document)" :href="downloadUrl(document)!" class="button-primary justify-center px-4 py-2.5">
                                    <Download :size="17" aria-hidden="true" /> {{ tr('Télécharger', 'Download') }}
                                </a>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else class="mt-9 rounded-2xl border border-dashed border-slate-300 bg-soft px-6 py-14 text-center">
                    <span class="mx-auto grid size-14 place-items-center rounded-full bg-white text-edsp-green shadow-sm">
                        <LibraryBig :size="26" aria-hidden="true" />
                    </span>
                    <h3 class="mt-5 text-lg font-bold text-navy">{{ tr('Aucun document trouvé', 'No documents found') }}</h3>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-600">
                        {{ tr('Modifiez votre recherche ou réinitialisez les filtres pour afficher les ressources disponibles.', 'Change your search or reset the filters to see available resources.') }}
                    </p>
                    <button v-if="search || category" type="button" class="button-secondary mt-5 justify-center" @click="clearFilters">
                        <X :size="16" aria-hidden="true" /> {{ tr('Réinitialiser les filtres', 'Reset filters') }}
                    </button>
                </div>

                <nav v-if="documents.last_page > 1" class="mt-10 flex items-center justify-between border-t border-slate-200 pt-6" :aria-label="tr('Pagination des documents', 'Document pagination')">
                    <Link v-if="documents.prev_page_url" :href="documents.prev_page_url" class="button-secondary">{{ tr('Précédent', 'Previous') }}</Link>
                    <span v-else />
                    <p class="text-sm text-slate-500">{{ tr('Page', 'Page') }} {{ documents.current_page }} / {{ documents.last_page }}</p>
                    <Link v-if="documents.next_page_url" :href="documents.next_page_url" class="button-secondary">{{ tr('Suivant', 'Next') }}</Link>
                    <span v-else />
                </nav>

                <div class="mt-10 flex items-start gap-3 rounded-xl border border-edsp-green/15 bg-edsp-green/5 px-5 py-4 text-sm leading-6 text-slate-700">
                    <FileCheck2 :size="19" class="mt-0.5 flex-none text-edsp-green" aria-hidden="true" />
                    <p>{{ tr('Seuls les documents validés, rendus publics et dont la date de publication est atteinte apparaissent ici.', 'Only approved public documents whose publication date has been reached appear here.') }}</p>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

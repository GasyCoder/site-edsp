<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    CalendarDays,
    ChevronRight,
    Newspaper,
} from 'lucide-vue-next';
import PublicLayout from '../../layouts/PublicLayout.vue';
import SeoHead from '../../components/public/SeoHead.vue';
import type { SeoData } from '../../types';
import type { Article } from '../../types';
import { useI18n } from '../../lib/i18n';

type NewsListItem = Article & {
    is_featured?: boolean;
};

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

defineProps<{ news: Paginator<NewsListItem>; seo?: SeoData }>();
const { languageTag, tr } = useI18n();

const formatDate = (date: string | null): string => {
    if (!date) {
        return tr('Date non renseignée', 'Date not available');
    }

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return date;
    }

    return new Intl.DateTimeFormat(languageTag.value, {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(parsed);
};
</script>

<template>
    <SeoHead
        :title="seo?.title || tr('Actualités | EDSP', 'News | EDSP')"
        :description="seo?.description || tr('Consultez les actualités et communiqués publiés par l’École de Droit et Science Politique de l’Université de Mahajanga.', 'Read news and announcements from the University of Mahajanga School of Law and Political Science.')"
        :canonical-url="seo?.canonical"
        :structured-data="seo?.schema"
    />

    <PublicLayout>
        <header class="public-page-hero bg-soft">
            <div class="mx-auto max-w-7xl">
                <nav :aria-label="tr('Fil d’Ariane', 'Breadcrumb')" class="mb-6">
                    <ol class="flex items-center gap-2 text-sm text-gray-500">
                        <li><Link href="/" class="transition hover:text-edsp-green">{{ tr('Accueil', 'Home') }}</Link></li>
                        <li aria-hidden="true"><ChevronRight :size="15" /></li>
                        <li class="font-semibold text-navy" aria-current="page">{{ tr('Actualités', 'News') }}</li>
                    </ol>
                </nav>

                <div class="max-w-3xl">
                    <p class="section-eyebrow mb-2 text-edsp-green">
                        {{ tr('À la une', 'Latest news') }}
                    </p>
                    <h1 class="page-title text-navy">
                        {{ tr('Actualités et communiqués', 'News and announcements') }}
                    </h1>
                    <p class="section-description mt-4 text-gray-600">
                        {{ tr('Retrouvez les informations officiellement publiées par l’EDSP et suivez la vie de l’établissement.', 'Read official EDSP updates and keep up with life at the School.') }}
                    </p>
                </div>
            </div>
        </header>

        <div class="bg-white">
            <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16" aria-labelledby="news-heading">
                <div class="mb-9 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-edsp-green">
                            {{ tr('Publications', 'Publications') }}
                        </p>
                        <h2 id="news-heading" class="mt-2 text-2xl font-bold text-navy sm:text-3xl">
                            {{ tr('Dernières actualités', 'Latest news') }}
                        </h2>
                    </div>
                    <p v-if="news.total" class="text-sm text-gray-500">
                        {{ news.total }} {{ tr(news.total > 1 ? 'publications' : 'publication', news.total > 1 ? 'articles' : 'article') }}
                    </p>
                </div>

                <div v-if="news.data.length" class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="item in news.data"
                        :key="item.id"
                        class="group surface-card relative flex min-w-0 flex-col overflow-hidden transition-colors hover:border-institutional/30"
                    >
                        <div class="relative flex h-40 items-center justify-center overflow-hidden bg-navy sm:h-44" aria-hidden="true">
                            <Newspaper :size="34" class="text-gold" :stroke-width="1.5" />
                        </div>

                        <div class="flex flex-1 flex-col p-5 sm:p-6">
                            <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-gray-500">
                                <time :datetime="item.published_at ?? undefined" class="inline-flex items-center gap-2">
                                    <CalendarDays :size="15" class="text-edsp-green" aria-hidden="true" />
                                    {{ formatDate(item.published_at) }}
                                </time>
                                <span v-if="item.is_featured" class="rounded bg-gold/20 px-2.5 py-1 text-[#8A6410]">
                                    {{ tr('À la une', 'Featured') }}
                                </span>
                            </div>

                            <h3 class="mt-4 text-xl font-bold leading-snug text-navy">
                                <Link
                                    :href="`/actualites/${item.slug}`"
                                    class="after:absolute after:inset-0 focus-visible:rounded"
                                >
                                    {{ item.title }}
                                </Link>
                            </h3>
                            <p class="mt-3 line-clamp-4 flex-1 leading-7 text-gray-600">
                                {{ item.excerpt }}
                            </p>

                            <span class="mt-6 inline-flex items-center gap-2 font-heading text-sm font-semibold text-edsp-green">
                                {{ tr('Lire l’actualité', 'Read article') }}
                                <ArrowRight
                                    :size="17"
                                    class="transition-transform group-hover:translate-x-1"
                                    aria-hidden="true"
                                />
                            </span>
                        </div>
                    </article>
                </div>

                <div v-else class="rounded-xl border border-dashed border-gray-300 bg-soft px-6 py-16 text-center">
                    <Newspaper :size="36" class="mx-auto text-institutional" aria-hidden="true" />
                    <h3 class="mt-5 text-xl font-bold text-navy">{{ tr('Aucune actualité publiée', 'No news published') }}</h3>
                    <p class="mx-auto mt-2 max-w-lg leading-7 text-gray-600">
                        {{ tr('Les prochains communiqués de l’EDSP seront affichés sur cette page.', 'Future EDSP announcements will appear on this page.') }}
                    </p>
                </div>

                <nav
                    v-if="news.last_page > 1"
                    class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-gray-200 pt-7 sm:flex-row"
                    :aria-label="tr('Pagination des actualités', 'News pagination')"
                >
                    <component
                        :is="news.prev_page_url ? Link : 'span'"
                        :href="news.prev_page_url || undefined"
                        preserve-scroll
                        :aria-disabled="!news.prev_page_url"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2.5 text-sm font-semibold text-navy transition"
                        :class="news.prev_page_url ? 'hover:border-institutional hover:text-institutional' : 'cursor-not-allowed opacity-40'"
                    >
                        <ArrowLeft :size="16" aria-hidden="true" />
                        {{ tr('Précédent', 'Previous') }}
                    </component>

                    <p class="text-sm text-gray-600" aria-live="polite">
                        {{ tr('Page', 'Page') }} <strong class="text-navy">{{ news.current_page }}</strong> {{ tr('sur', 'of') }} {{ news.last_page }}
                    </p>

                    <component
                        :is="news.next_page_url ? Link : 'span'"
                        :href="news.next_page_url || undefined"
                        preserve-scroll
                        :aria-disabled="!news.next_page_url"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2.5 text-sm font-semibold text-navy transition"
                        :class="news.next_page_url ? 'hover:border-institutional hover:text-institutional' : 'cursor-not-allowed opacity-40'"
                    >
                        {{ tr('Suivant', 'Next') }}
                        <ArrowRight :size="16" aria-hidden="true" />
                    </component>
                </nav>
            </section>
        </div>
    </PublicLayout>
</template>

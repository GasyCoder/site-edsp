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

const formatDate = (date: string | null): string => {
    if (!date) {
        return 'Date non renseignée';
    }

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return date;
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(parsed);
};
</script>

<template>
    <SeoHead
        :title="seo?.title || 'Actualités | EDSP'"
        :description="seo?.description || 'Consultez les actualités et communiqués publiés par l’École de Droit et Science Politique de l’Université de Mahajanga.'"
        :canonical-url="seo?.canonical"
        :structured-data="seo?.schema"
    />

    <PublicLayout>
        <header class="relative isolate overflow-hidden bg-soft">
            <div
                class="absolute inset-y-0 right-0 -z-10 hidden w-[32%] bg-navy lg:block"
                aria-hidden="true"
            />
            <div
                class="absolute -left-24 -top-32 -z-10 h-80 w-80 rounded-full bg-gold/15 blur-3xl"
                aria-hidden="true"
            />

            <div class="mx-auto max-w-7xl px-6 py-14 sm:py-16 lg:py-20">
                <nav aria-label="Fil d’Ariane" class="mb-8">
                    <ol class="flex items-center gap-2 text-sm text-gray-500">
                        <li><Link href="/" class="transition hover:text-edsp-green">Accueil</Link></li>
                        <li aria-hidden="true"><ChevronRight :size="15" /></li>
                        <li class="font-semibold text-navy" aria-current="page">Actualités</li>
                    </ol>
                </nav>

                <div class="max-w-3xl">
                    <p class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-edsp-green">
                        À la une
                    </p>
                    <h1 class="text-3xl font-extrabold leading-tight text-navy sm:text-4xl lg:text-5xl">
                        Actualités et communiqués
                    </h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-gray-600 sm:text-lg">
                        Retrouvez les informations officiellement publiées par l’EDSP et suivez la vie de l’établissement.
                    </p>
                </div>
            </div>
        </header>

        <div class="bg-white">
            <section class="mx-auto max-w-7xl px-6 py-16 sm:py-20" aria-labelledby="news-heading">
                <div class="mb-9 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-edsp-green">
                            Publications
                        </p>
                        <h2 id="news-heading" class="mt-2 text-2xl font-bold text-navy sm:text-3xl">
                            Dernières actualités
                        </h2>
                    </div>
                    <p v-if="news.total" class="text-sm text-gray-500">
                        {{ news.total }} publication{{ news.total > 1 ? 's' : '' }}
                    </p>
                </div>

                <div v-if="news.data.length" class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 xl:gap-8">
                    <article
                        v-for="item in news.data"
                        :key="item.id"
                        class="group relative flex min-w-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-institutional/30 hover:shadow-xl"
                    >
                        <div class="relative flex h-40 items-center justify-center overflow-hidden bg-navy sm:h-44" aria-hidden="true">
                            <div class="absolute -right-10 -top-12 h-36 w-36 rounded-full border-[26px] border-white/5" />
                            <div class="absolute -bottom-16 -left-10 h-36 w-36 rounded-full bg-edsp-green/25 blur-2xl" />
                            <Newspaper :size="38" class="relative text-gold" :stroke-width="1.5" />
                        </div>

                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-gray-500">
                                <time :datetime="item.published_at ?? undefined" class="inline-flex items-center gap-2">
                                    <CalendarDays :size="15" class="text-edsp-green" aria-hidden="true" />
                                    {{ formatDate(item.published_at) }}
                                </time>
                                <span v-if="item.is_featured" class="rounded-full bg-gold/20 px-2.5 py-1 text-[#8A6410]">
                                    À la une
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
                                Lire l’actualité
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
                    <h3 class="mt-5 text-xl font-bold text-navy">Aucune actualité publiée</h3>
                    <p class="mx-auto mt-2 max-w-lg leading-7 text-gray-600">
                        Les prochains communiqués de l’EDSP seront affichés sur cette page.
                    </p>
                </div>

                <nav
                    v-if="news.last_page > 1"
                    class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-gray-200 pt-7 sm:flex-row"
                    aria-label="Pagination des actualités"
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
                        Précédent
                    </component>

                    <p class="text-sm text-gray-600" aria-live="polite">
                        Page <strong class="text-navy">{{ news.current_page }}</strong> sur {{ news.last_page }}
                    </p>

                    <component
                        :is="news.next_page_url ? Link : 'span'"
                        :href="news.next_page_url || undefined"
                        preserve-scroll
                        :aria-disabled="!news.next_page_url"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2.5 text-sm font-semibold text-navy transition"
                        :class="news.next_page_url ? 'hover:border-institutional hover:text-institutional' : 'cursor-not-allowed opacity-40'"
                    >
                        Suivant
                        <ArrowRight :size="16" aria-hidden="true" />
                    </component>
                </nav>
            </section>
        </div>
    </PublicLayout>
</template>

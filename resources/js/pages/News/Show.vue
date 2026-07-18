<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    ChevronRight,
    Download,
    FileText,
    Images,
    Tag,
    UserRound,
} from 'lucide-vue-next';
import { computed } from 'vue';
import PublicLayout from '../../layouts/PublicLayout.vue';
import MediaPlaceholder from '../../components/public/MediaPlaceholder.vue';
import SeoHead from '../../components/public/SeoHead.vue';
import RichText from '../../components/public/RichText.vue';
import { mediaUrl, safePublicUrl } from '../../lib/public-content';
import type { Article, PublicDocument, PublicGallery, SeoData } from '../../types';

type NewsArticle = Article & {
    content: string;
    meta_description?: string | null;
    meta_title?: string | null;
};

const props = defineProps<{ article: NewsArticle; seo?: SeoData }>();

const featuredImage = computed(
    () =>
        props.article.featured_image_url ||
        props.article.image_url ||
        mediaUrl(props.article.featured_image),
);
const featuredImageAlt = computed(
    () => props.article.featured_image?.alt_text || `Illustration de l’actualité : ${props.article.title}`,
);

const documentUrl = (document: PublicDocument): string | null => safePublicUrl(document.download_url);
const galleryImages = (gallery: PublicGallery) =>
    (gallery.images ?? []).filter((image) => image.is_visible !== false && mediaUrl(image.media));

const formatFileSize = (bytes?: number | null): string | null => {
    if (!bytes || bytes < 1) {
        return null;
    }

    if (bytes < 1024 * 1024) {
        return `${Math.ceil(bytes / 1024)} Ko`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1).replace('.', ',')} Mo`;
};

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
        :title="seo?.title || article.meta_title || `${article.title} | EDSP`"
        :description="seo?.description || article.meta_description || article.excerpt"
        :canonical-url="seo?.canonical"
        :image-url="seo?.og_image || article.featured_image_url || article.image_url"
        :keywords="seo?.keywords"
        :open-graph-title="seo?.og_title"
        :open-graph-description="seo?.og_description"
        :structured-data="seo?.schema"
        :no-index="seo?.robots?.includes('noindex')"
        :no-follow="seo?.robots?.includes('nofollow')"
        type="article"
    />

    <PublicLayout>
        <article>
            <header class="relative isolate overflow-hidden bg-navy text-white">
                <div
                    class="absolute -right-24 -top-32 -z-10 h-96 w-96 rounded-full border-[70px] border-white/5"
                    aria-hidden="true"
                />
                <div
                    class="absolute -bottom-24 left-1/4 -z-10 h-64 w-64 rounded-full bg-edsp-green/20 blur-3xl"
                    aria-hidden="true"
                />

                <div class="mx-auto max-w-5xl px-6 py-14 sm:py-16 lg:py-20">
                    <nav aria-label="Fil d’Ariane" class="mb-9">
                        <ol class="flex flex-wrap items-center gap-2 text-sm text-blue-100/80">
                            <li><Link href="/" class="transition hover:text-white">Accueil</Link></li>
                            <li aria-hidden="true"><ChevronRight :size="15" /></li>
                            <li><Link href="/actualites" class="transition hover:text-white">Actualités</Link></li>
                            <li aria-hidden="true"><ChevronRight :size="15" /></li>
                            <li class="max-w-xs truncate font-semibold text-white" aria-current="page">
                                {{ article.title }}
                            </li>
                        </ol>
                    </nav>

                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-blue-100">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5">
                            <CalendarDays :size="15" class="text-gold" aria-hidden="true" />
                            <time :datetime="article.published_at ?? undefined">{{ formatDate(article.published_at) }}</time>
                        </span>
                        <span
                            v-if="article.category?.name"
                            class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5"
                        >
                            <Tag :size="14" class="text-gold" aria-hidden="true" />
                            {{ article.category.name }}
                        </span>
                        <span
                            v-if="article.author_name"
                            class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5"
                        >
                            <UserRound :size="14" class="text-gold" aria-hidden="true" />
                            {{ article.author_name }}
                        </span>
                    </div>

                    <h1 class="mt-5 text-3xl font-extrabold leading-tight sm:text-4xl lg:text-5xl">
                        {{ article.title }}
                    </h1>
                    <p class="mt-6 max-w-3xl text-base leading-8 text-blue-100 sm:text-lg">
                        {{ article.excerpt }}
                    </p>
                </div>
            </header>

            <div class="mx-auto max-w-5xl px-6 py-16 sm:py-20">
                <figure
                    v-if="featuredImage"
                    class="mb-12 overflow-hidden rounded-2xl bg-soft shadow-[0_16px_45px_rgba(11,31,85,0.12)]"
                >
                    <div class="h-72 sm:h-[30rem]">
                        <MediaPlaceholder
                            :image-url="featuredImage"
                            :alt="featuredImageAlt"
                            :label="featuredImageAlt"
                        />
                    </div>
                    <figcaption v-if="article.featured_image?.caption" class="px-5 py-3 text-sm text-gray-600">
                        {{ article.featured_image.caption }}
                    </figcaption>
                </figure>

                <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_12rem] lg:gap-16">
                    <div class="min-w-0">
                        <div class="mb-8 h-1 w-14 rounded-full bg-gold" aria-hidden="true" />
                        <RichText :html="article.content" class="text-base text-gray-700 sm:text-lg" />

                        <section
                            v-if="article.documents?.length"
                            aria-labelledby="news-documents-title"
                            class="mt-12 border-t border-gray-200 pt-10"
                        >
                            <div class="flex items-center gap-3">
                                <FileText :size="22" class="text-edsp-green" aria-hidden="true" />
                                <h2 id="news-documents-title" class="text-xl font-bold text-navy">
                                    Documents associés
                                </h2>
                            </div>
                            <ul class="mt-5 grid gap-3">
                                <li
                                    v-for="document in article.documents"
                                    :key="document.id"
                                    class="rounded-xl border border-gray-200 bg-soft p-4"
                                >
                                    <a
                                        v-if="documentUrl(document)"
                                        :href="documentUrl(document) ?? undefined"
                                        class="group flex items-start justify-between gap-4"
                                    >
                                        <span>
                                            <span class="block font-semibold text-navy group-hover:text-institutional">
                                                {{ document.title }}
                                            </span>
                                            <span v-if="document.description" class="mt-1 block text-sm leading-6 text-gray-600">
                                                {{ document.description }}
                                            </span>
                                            <span
                                                v-if="document.category || formatFileSize(document.size)"
                                                class="mt-2 block text-xs font-semibold uppercase tracking-wide text-gray-500"
                                            >
                                                {{ [document.category, formatFileSize(document.size)].filter(Boolean).join(' · ') }}
                                            </span>
                                        </span>
                                        <Download :size="20" class="mt-0.5 shrink-0 text-edsp-green" aria-hidden="true" />
                                    </a>
                                </li>
                            </ul>
                        </section>
                    </div>

                    <aside class="border-t border-gray-200 pt-7 lg:border-l lg:border-t-0 lg:pl-8 lg:pt-0" aria-label="Publication">
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-institutional/10 text-institutional">
                            <CalendarDays :size="22" aria-hidden="true" />
                        </div>
                        <p class="mt-4 text-xs font-bold uppercase tracking-[0.14em] text-gray-500">
                            Date de publication
                        </p>
                        <p class="mt-1 font-semibold leading-6 text-navy">
                            {{ formatDate(article.published_at) }}
                        </p>
                        <template v-if="article.category?.name">
                            <p class="mt-7 text-xs font-bold uppercase tracking-[0.14em] text-gray-500">Rubrique</p>
                            <p class="mt-1 font-semibold leading-6 text-navy">{{ article.category.name }}</p>
                        </template>
                        <template v-if="article.author_name">
                            <p class="mt-7 text-xs font-bold uppercase tracking-[0.14em] text-gray-500">Auteur</p>
                            <p class="mt-1 font-semibold leading-6 text-navy">{{ article.author_name }}</p>
                        </template>
                    </aside>
                </div>

                <section
                    v-if="article.galleries?.some((gallery) => galleryImages(gallery).length)"
                    aria-labelledby="news-galleries-title"
                    class="mt-16 border-t border-gray-200 pt-12"
                >
                    <div class="flex items-center gap-3">
                        <Images :size="24" class="text-edsp-green" aria-hidden="true" />
                        <h2 id="news-galleries-title" class="text-2xl font-bold text-navy">Galeries photos</h2>
                    </div>

                    <article
                        v-for="gallery in article.galleries"
                        v-show="galleryImages(gallery).length"
                        :key="gallery.id"
                        class="mt-9"
                    >
                        <h3 class="text-xl font-bold text-navy">{{ gallery.title }}</h3>
                        <p v-if="gallery.description" class="mt-2 leading-7 text-gray-600">
                            {{ gallery.description }}
                        </p>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <figure
                                v-for="image in galleryImages(gallery)"
                                :key="image.id"
                                class="overflow-hidden rounded-xl bg-soft shadow-sm"
                            >
                                <div class="h-64">
                                    <MediaPlaceholder
                                        :image-url="mediaUrl(image.media)"
                                        :alt="image.alt_text || image.media?.alt_text || image.caption || gallery.title"
                                        :label="image.caption || image.title || gallery.title"
                                    />
                                </div>
                                <figcaption v-if="image.caption" class="px-4 py-3 text-sm text-gray-600">
                                    {{ image.caption }}
                                </figcaption>
                            </figure>
                        </div>
                    </article>
                </section>
            </div>

            <footer class="border-t border-gray-200 bg-soft">
                <div class="mx-auto max-w-5xl px-6 py-8">
                    <Link
                        href="/actualites"
                        class="inline-flex items-center gap-2 font-semibold text-institutional transition hover:text-navy"
                    >
                        <ArrowLeft :size="18" aria-hidden="true" />
                        Toutes les actualités
                    </Link>
                </div>
            </footer>
        </article>
    </PublicLayout>
</template>

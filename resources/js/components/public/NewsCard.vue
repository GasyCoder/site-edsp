<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Article } from '../../types';
import { formatPublicDate, mediaThumbnailUrl } from '../../lib/public-content';
import MediaPlaceholder from './MediaPlaceholder.vue';
import { useI18n } from '../../lib/i18n';

const props = defineProps<{
    article: Article;
}>();
const { languageTag, tr } = useI18n();

const category = computed(() => props.article.category?.name || props.article.category_name || null);
const date = computed(() => formatPublicDate(props.article.published_at, languageTag.value));
const image = computed(
    () =>
        mediaThumbnailUrl(props.article.featured_image) ||
        props.article.featured_image_url ||
        props.article.image_url ||
        null,
);
</script>

<template>
    <article
        class="group surface-card flex h-full flex-col overflow-hidden transition-colors hover:border-slate-300 dark:hover:border-slate-600"
    >
        <div class="h-44 overflow-hidden sm:h-48">
            <MediaPlaceholder
                :image-url="image"
                :alt="article.featured_image?.alt_text || article.title"
                :label="tr(`Illustration de l'actualité : ${article.title}`, `News illustration: ${article.title}`)"
                class="transition duration-300 group-hover:scale-[1.015]"
            />
        </div>
        <div class="flex flex-1 flex-col p-5 sm:p-6">
            <div v-if="category || date" class="mb-3 flex flex-wrap items-center gap-3 text-xs">
                <span
                    v-if="category"
                    class="rounded bg-edsp-green/10 px-2.5 py-1 font-bold uppercase tracking-wide text-edsp-green"
                >
                    {{ category }}
                </span>
                <time v-if="date" :datetime="article.published_at || undefined" class="text-slate-500">
                    {{ date }}
                </time>
            </div>
            <h3 class="text-lg font-semibold leading-snug text-navy sm:text-xl">{{ article.title }}</h3>
            <p class="mt-2.5 line-clamp-3 flex-1 text-[0.95rem] leading-6 text-slate-600">{{ article.excerpt }}</p>
            <Link
                :href="`/actualites/${article.slug}`"
                class="mt-5 inline-flex w-fit items-center gap-2 text-sm font-semibold text-edsp-green transition hover:text-institutional"
            >
                {{ tr('Lire la suite', 'Read more') }}
                <ArrowRight :size="16" aria-hidden="true" />
            </Link>
        </div>
    </article>
</template>

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
        class="group flex h-full flex-col overflow-hidden rounded-xl bg-white shadow-[0_3px_14px_rgba(11,31,85,0.08)] transition hover:-translate-y-1 hover:shadow-[0_18px_38px_rgba(11,31,85,0.14)]"
    >
        <div class="h-48 overflow-hidden">
            <MediaPlaceholder
                :image-url="image"
                :alt="article.featured_image?.alt_text || article.title"
                :label="tr(`Illustration de l'actualité : ${article.title}`, `News illustration: ${article.title}`)"
                class="transition duration-500 group-hover:scale-[1.03]"
            />
        </div>
        <div class="flex flex-1 flex-col p-6">
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
            <h3 class="text-xl font-semibold leading-snug text-navy">{{ article.title }}</h3>
            <p class="mt-3 line-clamp-3 flex-1 leading-6 text-slate-600">{{ article.excerpt }}</p>
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

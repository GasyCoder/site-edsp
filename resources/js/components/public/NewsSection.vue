<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Article, Section } from '../../types';
import EmptyState from './EmptyState.vue';
import NewsCard from './NewsCard.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass } from './section-theme';
import SmartLink from './SmartLink.vue';
import { useI18n } from '../../lib/i18n';

const props = withDefaults(
    defineProps<{
        news: Article[];
        section?: Section | null;
    }>(),
    {
        section: null,
    },
);
const { tr } = useI18n();

const title = computed(() => props.section?.title || tr('Actualités et communiqués', 'News and announcements'));
const eyebrow = computed(() => props.section?.subtitle || tr('À la une', 'Latest news'));
const content = computed(() => props.section?.content || tr('Retrouvez les informations académiques et les événements de l’établissement.', 'Keep up with academic information and events at the School.'));
const background = computed(() => sectionBackgroundClass(props.section, 'light'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section));
const dark = computed(() => isDarkSection(props.section, 'light'));
</script>

<template>
    <section id="actualites" :class="background" class="public-section">
        <div :class="container" class="mx-auto">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div :class="alignment === 'center' ? 'mx-auto max-w-3xl text-center sm:mx-0 sm:text-left' : 'text-left'">
                    <p class="section-eyebrow" :class="dark ? 'text-gold' : 'text-edsp-green'">
                        {{ eyebrow }}
                    </p>
                    <h2 class="section-title mt-2" :class="dark ? 'text-white' : 'text-navy'">{{ title }}</h2>
                    <p class="section-description" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ content }}</p>
                </div>
                <SmartLink :href="section?.button_url || '/actualites'" class="button-outline w-fit" :class="dark ? 'border-white text-white hover:bg-white hover:text-navy' : ''">
                    {{ section?.button_text || tr('Voir toutes les actualités', 'View all news') }}
                    <ArrowRight :size="17" aria-hidden="true" />
                </SmartLink>
            </div>

            <div v-if="news.length" class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                <NewsCard v-for="article in news" :key="article.id" :article="article" />
            </div>
            <EmptyState v-else class="mt-9" :message="tr('Les prochaines actualités seront publiées ici.', 'New updates will be published here.')" />
        </div>
    </section>
</template>

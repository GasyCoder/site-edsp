<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Article, Section } from '../../types';
import EmptyState from './EmptyState.vue';
import NewsCard from './NewsCard.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass } from './section-theme';
import SmartLink from './SmartLink.vue';

const props = withDefaults(
    defineProps<{
        news: Article[];
        section?: Section | null;
    }>(),
    {
        section: null,
    },
);

const title = computed(() => props.section?.title || 'Actualités et communiqués');
const eyebrow = computed(() => props.section?.subtitle || 'À la une');
const content = computed(() => props.section?.content || 'Retrouvez les informations académiques et les événements de l’établissement.');
const background = computed(() => sectionBackgroundClass(props.section, 'light'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section));
const dark = computed(() => isDarkSection(props.section, 'light'));
</script>

<template>
    <section id="actualites" :class="background" class="px-4 py-16 sm:px-6 sm:py-20">
        <div :class="container" class="mx-auto">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div :class="alignment === 'center' ? 'mx-auto max-w-3xl text-center sm:mx-0 sm:text-left' : 'text-left'">
                    <p class="text-xs font-bold uppercase tracking-[0.13em] sm:text-sm" :class="dark ? 'text-gold' : 'text-edsp-green'">
                        {{ eyebrow }}
                    </p>
                    <h2 class="mt-2 text-balance text-3xl font-bold sm:text-4xl" :class="dark ? 'text-white' : 'text-navy'">{{ title }}</h2>
                    <p class="mt-3 max-w-2xl text-pretty leading-7" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ content }}</p>
                </div>
                <SmartLink :href="section?.button_url || '/actualites'" class="button-outline w-fit" :class="dark ? 'border-white text-white hover:bg-white hover:text-navy' : ''">
                    {{ section?.button_text || 'Voir toutes les actualités' }}
                    <ArrowRight :size="17" aria-hidden="true" />
                </SmartLink>
            </div>

            <div v-if="news.length" class="mt-9 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <NewsCard v-for="article in news" :key="article.id" :article="article" />
            </div>
            <EmptyState v-else class="mt-9" message="Les prochaines actualités seront publiées ici." />
        </div>
    </section>
</template>

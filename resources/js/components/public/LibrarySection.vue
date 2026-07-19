<script setup lang="ts">
import { BookOpen, ExternalLink } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Section, SiteSettings } from '../../types';
import { sectionAlignment, sectionBackgroundClass, sectionContainerClass } from './section-theme';
import SmartLink from './SmartLink.vue';
import { useI18n } from '../../lib/i18n';

const props = withDefaults(
    defineProps<{
        section?: Section | null;
        settings?: SiteSettings;
    }>(),
    {
        section: null,
        settings: () => ({}),
    },
);
const { tr } = useI18n();

const title = computed(() => props.section?.title || tr('Bibliothèque et ressources documentaires', 'Library and learning resources'));
const content = computed(
    () =>
        props.section?.content ||
        tr("La bibliothèque de l'EDSP met à la disposition des étudiants des ouvrages juridiques, politiques et académiques pour soutenir leur formation et leurs travaux de recherche.", 'The EDSP library provides legal, political and academic resources to support students’ studies and research.'),
);
const buttonText = computed(() => props.section?.button_text || tr('Consulter le catalogue', 'Browse the catalogue'));
const libraryUrl = computed(
    () => props.section?.button_url || props.settings.library_url || '/bibliotheque',
);
const background = computed(() => sectionBackgroundClass(props.section, 'white'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section));
</script>

<template>
    <section id="bibliotheque" :class="background" class="px-4 py-12 sm:px-6 sm:py-16">
        <div
            :class="[container, alignment === 'center' ? 'text-center lg:justify-center' : 'text-left']"
            class="mx-auto flex flex-col items-start gap-6 rounded-xl bg-navy px-6 py-9 text-white sm:px-10 sm:py-11 lg:flex-row lg:items-center"
        >
            <span class="grid size-16 flex-none place-items-center rounded-xl bg-white/10 text-gold">
                <BookOpen :size="33" :stroke-width="1.7" aria-hidden="true" />
            </span>
            <div class="min-w-0 flex-1" :class="alignment === 'center' ? 'lg:flex-none' : ''">
                <h2 class="text-balance text-2xl font-bold sm:text-3xl">{{ title }}</h2>
                <p class="mt-3 max-w-3xl text-pretty text-sm leading-7 text-[#C9D4EE] sm:text-base">
                    {{ content }}
                </p>
            </div>
            <SmartLink :href="libraryUrl" class="button-light flex-none">
                {{ buttonText }}
                <ExternalLink v-if="libraryUrl.startsWith('http')" :size="16" aria-hidden="true" />
            </SmartLink>
        </div>
    </section>
</template>

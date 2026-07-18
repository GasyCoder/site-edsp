<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Program, Section } from '../../types';
import EmptyState from './EmptyState.vue';
import ProgramCard from './ProgramCard.vue';
import SectionHeading from './SectionHeading.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass, sectionSetting } from './section-theme';
import SmartLink from './SmartLink.vue';

const props = withDefaults(
    defineProps<{
        programs: Program[];
        section?: Section | null;
    }>(),
    {
        section: null,
    },
);

const title = computed(() => props.section?.title || 'Nos parcours de formation');
const eyebrow = computed(() => props.section?.subtitle || 'Formations');
const content = computed(
    () =>
        props.section?.content ||
        "Des parcours complémentaires pour comprendre le droit et l'action publique à Madagascar et dans le monde.",
);
const background = computed(() => sectionBackgroundClass(props.section, 'light'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section, 'center'));
const dark = computed(() => isDarkSection(props.section, 'light'));
const footerLabel = computed(() => sectionSetting(props.section, 'footer_label', 'Diplômes et niveaux proposés :'));
</script>

<template>
    <section id="formations" :class="background" class="px-4 py-16 sm:px-6 sm:py-20">
        <div :class="container" class="mx-auto">
            <SectionHeading :eyebrow="eyebrow" :title="title" :description="content" :align="alignment" :dark="dark" />
            <div v-if="programs.length" class="mt-10 grid gap-6 lg:grid-cols-2">
                <ProgramCard v-for="program in programs" :key="program.id" :program="program" />
            </div>
            <EmptyState v-else class="mt-10" message="Les parcours de formation seront bientôt publiés." />

            <div
                v-if="programs.length"
                class="mt-7 flex flex-col gap-4 rounded-lg border border-slate-200 bg-white px-5 py-5 sm:flex-row sm:flex-wrap sm:items-center sm:px-7"
            >
                <span class="font-heading text-sm font-semibold text-navy">{{ footerLabel }}</span>
                <span
                    v-for="program in programs"
                    :key="`level-${program.id}`"
                    class="w-fit rounded bg-institutional/10 px-3.5 py-1.5 text-xs font-semibold text-institutional"
                >
                    {{ program.level }}
                </span>
                <SmartLink :href="section?.button_url || '/formations'" class="inline-flex items-center gap-2 font-heading text-sm font-semibold text-edsp-green sm:ml-auto">
                    {{ section?.button_text || 'Toutes les formations' }}
                    <ArrowRight :size="16" aria-hidden="true" />
                </SmartLink>
            </div>
        </div>
    </section>
</template>

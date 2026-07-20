<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Program, Section } from '../../types';
import EmptyState from './EmptyState.vue';
import ProgramCard from './ProgramCard.vue';
import SectionHeading from './SectionHeading.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass, sectionSetting } from './section-theme';
import SmartLink from './SmartLink.vue';
import { useI18n } from '../../lib/i18n';

const props = withDefaults(
    defineProps<{
        programs: Program[];
        section?: Section | null;
    }>(),
    {
        section: null,
    },
);
const { tr } = useI18n();

const title = computed(() => props.section?.title || tr('Nos parcours de formation', 'Our degree programmes'));
const eyebrow = computed(() => props.section?.subtitle || tr('Formations', 'Programmes'));
const content = computed(
    () =>
        props.section?.content ||
        tr("Des parcours complémentaires pour comprendre le droit et l'action publique à Madagascar et dans le monde.", 'Complementary pathways for understanding law and public affairs in Madagascar and beyond.'),
);
const background = computed(() => sectionBackgroundClass(props.section, 'light'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section, 'center'));
const dark = computed(() => isDarkSection(props.section, 'light'));
const footerLabel = computed(() => sectionSetting(props.section, 'footer_label', tr('Diplômes délivrés :', 'Degrees awarded:')));
const degreeBadges = computed(() => [tr('Licence (L3)', "Bachelor's degree (L3)"), tr('Master (M2)', "Master's degree (M2)")]);
</script>

<template>
    <section id="formations" :class="background" class="px-4 py-16 sm:px-6 sm:py-20">
        <div :class="container" class="mx-auto">
            <SectionHeading :eyebrow="eyebrow" :title="title" :description="content" :align="alignment" :dark="dark" />
            <div v-if="programs.length" class="mt-10 grid gap-6 lg:grid-cols-2">
                <ProgramCard v-for="program in programs" :key="program.id" :program="program" />
            </div>
            <EmptyState v-else class="mt-10" :message="tr('Les parcours de formation seront bientôt publiés.', 'Degree programmes will be published soon.')" />

            <div
                v-if="programs.length"
                class="mt-7 flex flex-col gap-4 rounded-lg border border-slate-200 bg-white px-5 py-5 sm:flex-row sm:flex-wrap sm:items-center sm:px-7"
            >
                <span class="font-heading text-sm font-semibold text-navy">{{ footerLabel }}</span>
                <span
                    v-for="degree in degreeBadges"
                    :key="`degree-${degree}`"
                    class="w-fit rounded bg-institutional/10 px-3.5 py-1.5 text-xs font-semibold text-institutional"
                >
                    {{ degree }}
                </span>
                <SmartLink :href="section?.button_url || '/formations'" class="inline-flex items-center gap-2 font-heading text-sm font-semibold text-edsp-green sm:ml-auto">
                    {{ section?.button_text || tr('Toutes les formations', 'All programmes') }}
                    <ArrowRight :size="16" aria-hidden="true" />
                </SmartLink>
            </div>
        </div>
    </section>
</template>

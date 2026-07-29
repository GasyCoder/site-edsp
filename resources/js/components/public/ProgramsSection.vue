<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Program, Section } from '../../types';
import EmptyState from './EmptyState.vue';
import ProgramCard from './ProgramCard.vue';
import { isDarkSection, sectionBackgroundClass, sectionContainerClass, sectionSetting } from './section-theme';
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
const dark = computed(() => isDarkSection(props.section, 'light'));
const footerLabel = computed(() => sectionSetting(props.section, 'footer_label', tr('Diplômes délivrés :', 'Degrees awarded:')));
const degreeBadges = computed(() => [tr('Licence (L3)', "Bachelor's degree (L3)"), tr('Master (M2)', "Master's degree (M2)")]);
</script>

<template>
    <section id="formations" :class="background" class="px-4 py-10 sm:px-6 sm:py-12">
        <div :class="container" class="mx-auto">
            <header class="grid gap-3 md:grid-cols-[minmax(0,0.8fr)_minmax(0,1fr)] md:items-end md:gap-10">
                <div>
                    <p class="section-eyebrow mb-2" :class="dark ? 'text-emerald-300' : 'text-edsp-green'">{{ eyebrow }}</p>
                    <h2 class="section-title" :class="dark ? 'text-white' : 'text-navy'">{{ title }}</h2>
                </div>
                <p class="max-w-2xl text-sm leading-6 sm:text-[0.95rem] sm:leading-7" :class="dark ? 'text-slate-300' : 'text-slate-600'">
                    {{ content }}
                </p>
            </header>

            <div v-if="programs.length" class="mt-6 grid gap-4 lg:grid-cols-2">
                <ProgramCard v-for="program in programs" :key="program.id" :program="program" />
            </div>
            <EmptyState v-else class="mt-8" :message="tr('Les parcours de formation seront bientôt publiés.', 'Degree programmes will be published soon.')" />

            <div
                v-if="programs.length"
                class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-3 border-t border-slate-200 pt-5"
            >
                <span class="font-heading text-xs font-semibold text-navy sm:text-sm">{{ footerLabel }}</span>
                <span
                    v-for="degree in degreeBadges"
                    :key="`degree-${degree}`"
                    class="w-fit rounded-md border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold text-institutional sm:text-xs"
                >
                    {{ degree }}
                </span>
                <SmartLink :href="section?.button_url || '/formations'" class="inline-flex basis-full items-center gap-2 font-heading text-xs font-semibold text-edsp-green sm:ml-auto sm:basis-auto sm:text-sm">
                    {{ section?.button_text || tr('Toutes les formations', 'All programmes') }}
                    <ArrowRight :size="15" aria-hidden="true" />
                </SmartLink>
            </div>
        </div>
    </section>
</template>

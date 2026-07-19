<script setup lang="ts">
import { ArrowRight, GraduationCap, Landmark, MapPin, Scale, UserPlus } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import type { Section } from '../../types';
import { mediaUrl } from '../../lib/public-content';
import MediaPlaceholder from './MediaPlaceholder.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass, sectionSetting } from './section-theme';
import SmartLink from './SmartLink.vue';
import { useI18n } from '../../lib/i18n';

const props = withDefaults(
    defineProps<{
        section?: Section | null;
    }>(),
    {
        section: null,
    },
);
const { tr } = useI18n();

const title = computed(
    () => props.section?.title || tr('Comprendre le droit. Agir sur la société.', 'Understand the law. Shape society.'),
);
const content = computed(
    () =>
        props.section?.content ||
        tr('L’EDSP vous forme à l’analyse juridique, aux institutions et aux politiques publiques, de la Licence au Master, au cœur de Mahajanga.', 'EDSP equips you to analyse law, institutions and public policy, from Bachelor’s to Master’s level, in the heart of Mahajanga.'),
);
const buttonText = computed(() => props.section?.button_text || tr('Découvrir les parcours', 'Explore our programmes'));
const buttonUrl = computed(() => props.section?.button_url || '/formations');
const secondaryButtonText = computed(() => sectionSetting(props.section, 'secondary_button_text', tr('S’inscrire', 'Apply now')));
const secondaryButtonUrl = computed(() => sectionSetting(props.section, 'secondary_button_url', '/inscription'));
const kickerText = computed(() => sectionSetting(props.section, 'kicker_text', tr('Deux parcours :', 'Two pathways:')));
const locationText = computed(() => sectionSetting(props.section, 'location_text', 'Ambondrona, Mahajanga'));
const degreeText = computed(() => sectionSetting(props.section, 'degree_text', tr('Licence · Master', 'Bachelor’s · Master’s')));
const visualEyebrow = computed(() => sectionSetting(props.section, 'visual_eyebrow', tr('Choisissez votre parcours', 'Choose your programme')));
const visualTitle = computed(() => sectionSetting(props.section, 'visual_title', tr('Une formation ancrée dans les réalités juridiques et publiques de Madagascar.', 'A degree grounded in Madagascar’s legal and public realities.')));
const visualFooter = computed(() => sectionSetting(props.section, 'visual_footer', tr('Droit privé · Science politique', 'Private Law · Political Science')));
const background = computed(() => sectionBackgroundClass(props.section, 'light'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section));
const dark = computed(() => isDarkSection(props.section, 'light'));
const image = computed(() => mediaUrl(props.section));
const alt = computed(
    () =>
        (typeof props.section?.settings?.alt_text === 'string' && props.section.settings.alt_text) ||
        props.section?.image?.alt_text ||
        tr("Campus et vie étudiante de l'EDSP", 'EDSP campus and student life'),
);

const programs = computed(() => [
    sectionSetting(props.section, 'rotating_item_1', tr('Droit privé', 'Private Law')),
    sectionSetting(props.section, 'rotating_item_2', tr('Science politique', 'Political Science')),
]);
const displayedProgram = ref(programs.value[0]);
let programIndex = 0;
let characterIndex = programs.value[0].length;
let deleting = true;
let typingTimer: number | undefined;

function animateProgram(): void {
    const program = programs.value[programIndex];

    if (deleting && characterIndex > 0) {
        characterIndex -= 1;
        displayedProgram.value = program.slice(0, characterIndex);
        typingTimer = window.setTimeout(animateProgram, 45);
        return;
    }

    if (deleting) {
        deleting = false;
        programIndex = (programIndex + 1) % programs.value.length;
        typingTimer = window.setTimeout(animateProgram, 220);
        return;
    }

    const nextProgram = programs.value[programIndex];
    characterIndex += 1;
    displayedProgram.value = nextProgram.slice(0, characterIndex);

    if (characterIndex >= nextProgram.length) {
        deleting = true;
        typingTimer = window.setTimeout(animateProgram, 1800);
        return;
    }

    typingTimer = window.setTimeout(animateProgram, 75);
}

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        displayedProgram.value = programs.value.join(' · ');
        return;
    }

    typingTimer = window.setTimeout(animateProgram, 1800);
});

onBeforeUnmount(() => {
    if (typingTimer !== undefined) {
        window.clearTimeout(typingTimer);
    }
});
</script>

<template>
    <section id="accueil" :class="background" class="overflow-hidden border-b border-slate-200 px-4 py-12 sm:px-6 sm:py-16 lg:py-18">
        <div :class="container" class="mx-auto grid items-center gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:gap-16">
            <div class="min-w-0" :class="alignment === 'center' ? 'text-center lg:text-left' : 'text-left'">
                <h1
                    class="max-w-xl text-balance text-[clamp(2rem,4vw,3rem)] font-bold leading-[1.12] tracking-[-0.025em]"
                    :class="[dark ? 'text-white' : 'text-navy', alignment === 'center' ? 'mx-auto lg:mx-0' : '']"
                >
                    {{ title }}
                </h1>
                <p class="mt-5 max-w-xl text-pretty text-base leading-7 sm:text-[1.05rem] sm:leading-8" :class="[dark ? 'text-[#C9D4EE]' : 'text-slate-600', alignment === 'center' ? 'mx-auto lg:mx-0' : '']">
                    {{ content }}
                </p>

                <div class="mt-6 flex min-h-7 items-center gap-2 text-sm font-semibold" :class="[dark ? 'text-white' : 'text-slate-700', alignment === 'center' ? 'justify-center lg:justify-start' : '']">
                    <span class="size-1.5 flex-none rounded-full bg-edsp-green" aria-hidden="true" />
                    <span>{{ kickerText }}</span>
                    <span class="text-edsp-green" aria-hidden="true">{{ displayedProgram }}</span>
                    <span class="h-4 w-px bg-edsp-green motion-safe:animate-pulse" aria-hidden="true" />
                    <span class="sr-only">{{ programs.join(tr(' et ', ' and ')) }}</span>
                </div>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <SmartLink :href="buttonUrl" class="button-dark justify-center sm:justify-start">
                        {{ buttonText }}
                        <ArrowRight :size="17" aria-hidden="true" />
                    </SmartLink>
                    <SmartLink :href="secondaryButtonUrl" class="button-primary justify-center sm:justify-start">
                        <UserPlus :size="17" aria-hidden="true" />
                        {{ secondaryButtonText }}
                    </SmartLink>
                </div>
                <div class="mt-7 flex flex-wrap gap-x-6 gap-y-3 text-sm" :class="[dark ? 'text-[#C9D4EE]' : 'text-slate-500', alignment === 'center' ? 'justify-center lg:justify-start' : '']">
                    <span class="inline-flex items-center gap-2">
                        <MapPin :size="16" class="flex-none text-edsp-green" aria-hidden="true" />
                        {{ locationText }}
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <GraduationCap :size="17" class="flex-none text-edsp-green" aria-hidden="true" />
                        {{ degreeText }}
                    </span>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-2xl pt-4 pl-4 sm:pt-6 sm:pl-6 lg:mx-0">
                <div
                    class="pointer-events-none absolute top-0 right-4 bottom-4 left-0 rounded-2xl border border-dashed border-institutional/25 bg-institutional/[0.035] sm:right-6 sm:bottom-6 dark:border-slate-500/40 dark:bg-white/[0.025]"
                    aria-hidden="true"
                />
                <div
                    class="relative z-10 h-[22rem] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_18px_45px_rgba(11,31,85,0.12)] sm:h-[27rem]"
                >
                    <MediaPlaceholder
                        v-if="image"
                        :image-url="image"
                        :alt="alt"
                        eager
                        :label="tr(`Campus et vie étudiante de l'EDSP`, 'EDSP campus and student life')"
                    />
                    <div
                        v-else
                        class="flex h-full flex-col bg-navy px-6 py-7 text-white sm:px-9 sm:py-9"
                        role="img"
                        :aria-label="tr('Les parcours de formation de l’EDSP', 'EDSP degree programmes')"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <span class="grid size-12 place-items-center rounded-xl bg-white/10 text-gold">
                                <Scale :size="25" aria-hidden="true" />
                            </span>
                            <span class="rounded-full border border-white/20 px-3 py-1.5 text-xs font-semibold text-[#C9D4EE]">
                                {{ degreeText }}
                            </span>
                        </div>

                        <div class="mt-auto">
                            <p class="text-sm font-semibold text-gold">{{ visualEyebrow }}</p>
                            <p class="mt-2 max-w-md text-pretty font-heading text-2xl font-semibold leading-snug sm:text-3xl">
                                {{ visualTitle }}
                            </p>
                            <div class="mt-7 grid gap-3 sm:grid-cols-2">
                                <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/8 px-4 py-4">
                                    <Scale :size="20" class="flex-none text-gold" aria-hidden="true" />
                                    <span class="font-heading text-sm font-semibold">{{ programs[0] }}</span>
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/8 px-4 py-4">
                                    <Landmark :size="20" class="flex-none text-gold" aria-hidden="true" />
                                    <span class="font-heading text-sm font-semibold">{{ programs[1] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="image"
                        class="absolute inset-x-0 bottom-0 flex items-center gap-3 bg-gradient-to-t from-navy/95 to-transparent px-6 pb-6 pt-16 text-white"
                    >
                        <span class="grid size-10 flex-none place-items-center rounded-lg bg-gold text-navy">
                            <Scale :size="21" aria-hidden="true" />
                        </span>
                        <span class="font-heading text-sm font-semibold">{{ visualFooter }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

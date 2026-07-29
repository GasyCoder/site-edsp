<script setup lang="ts">
import { ArrowRight, GraduationCap, Landmark, MapPin, Pencil, Scale, UserPlus } from 'lucide-vue-next';
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { Section } from '../../types';
import { mediaUrl } from '../../lib/public-content';
import MediaPlaceholder from './MediaPlaceholder.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass, sectionSetting } from './section-theme';
import SmartLink from './SmartLink.vue';
import { useI18n } from '../../lib/i18n';
import { editSectionContextKey } from './edit-section-context';

const props = withDefaults(
    defineProps<{
        section?: Section | null;
    }>(),
    {
        section: null,
    },
);
const { tr } = useI18n();
const editContext = inject(editSectionContextKey, null);
const editing = computed(() => editContext?.editing.value === true);
const editField = (fieldKey: string): void => editContext?.openEditor(fieldKey);

const title = computed(
    () => props.section?.title || tr('Comprendre le droit. Agir sur la société.', 'Understand the law. Shape society.'),
);
type HighlightColor = 'gold' | 'green' | 'institutional';
type TitleSegment = { color?: HighlightColor; text: string };

const firstHighlight = computed(() => sectionSetting(props.section, 'title_highlight_1', tr('droit', 'law')));
const secondHighlight = computed(() => sectionSetting(props.section, 'title_highlight_2', tr('science politique', 'political science')));
const firstHighlightColor = computed(() => sectionSetting(props.section, 'title_highlight_1_color', 'green') as HighlightColor);
const secondHighlightColor = computed(() => sectionSetting(props.section, 'title_highlight_2_color', 'institutional') as HighlightColor);
const titleFontSize = computed(() => {
    const value = Number(props.section?.settings?.title_font_size ?? 44);

    return Number.isFinite(value) ? Math.min(56, Math.max(30, value)) : 44;
});
const highlightClasses: Record<HighlightColor, string> = {
    gold: 'hero-title-highlight hero-title-highlight--gold',
    green: 'hero-title-highlight hero-title-highlight--green',
    institutional: 'hero-title-highlight hero-title-highlight--blue',
};
const titleSegments = computed<TitleSegment[]>(() => {
    const highlights = [
        { color: firstHighlightColor.value, text: firstHighlight.value.trim() },
        { color: secondHighlightColor.value, text: secondHighlight.value.trim() },
    ].filter((highlight) => highlight.text.length > 0);

    if (!highlights.length) {
        return [{ text: title.value }];
    }

    const escaped = highlights
        .map((highlight) => highlight.text)
        .sort((left, right) => right.length - left.length)
        .map((text) => text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    const matcher = new RegExp(`(${escaped.join('|')})`, 'giu');

    return title.value.split(matcher).filter(Boolean).map((text) => {
        const normalized = text.toLocaleLowerCase();
        const highlight = highlights.find((candidate) => candidate.text.toLocaleLowerCase() === normalized);

        return highlight ? { text, color: highlight.color } : { text };
    });
});
const content = computed(
    () =>
        props.section?.content ||
        tr('L’EDSP vous forme à l’analyse juridique, aux institutions et aux politiques publiques, de la Licence au Master, au cœur de Mahajanga.', 'EDSP equips you to analyse law, institutions and public policy, from Bachelor’s to Master’s level, in the heart of Mahajanga.'),
);
const buttonText = computed(() => props.section?.button_text || tr('Découvrir les parcours', 'Explore our programmes'));
const buttonUrl = computed(() => props.section?.button_url || '/formations');
const secondaryButtonText = computed(() => sectionSetting(props.section, 'secondary_button_text', tr('S’inscrire', 'Apply now')));
const secondaryButtonUrl = computed(() => sectionSetting(props.section, 'secondary_button_url', '/inscription'));
const kickerText = computed(() => sectionSetting(props.section, 'kicker_text', tr('Deux mentions :', 'Two subject areas:')));
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
    sectionSetting(props.section, 'rotating_item_1', tr('Droit', 'Law')),
    sectionSetting(props.section, 'rotating_item_2', tr('Sciences Politiques', 'Political Science')),
]);
const visualPrograms = computed(() => [
    sectionSetting(props.section, 'visual_program_1', programs.value[0]),
    sectionSetting(props.section, 'visual_program_2', programs.value[1]),
]);
const displayedProgram = ref(programs.value[0] || '');
let programIndex = 0;
let characterIndex = 0;
let deleting = false;
let typingTimer: number | undefined;
let typingMounted = false;

function clearTypingTimer(): void {
    if (typingTimer !== undefined) {
        window.clearTimeout(typingTimer);
        typingTimer = undefined;
    }
}

function animateProgram(): void {
    const items = programs.value.filter(Boolean);

    if (!items.length) {
        displayedProgram.value = '';
        return;
    }

    programIndex %= items.length;
    const currentProgram = items[programIndex];

    if (deleting && characterIndex > 0) {
        characterIndex -= 1;
        displayedProgram.value = currentProgram.slice(0, characterIndex);
        typingTimer = window.setTimeout(animateProgram, 45);
        return;
    }

    if (deleting) {
        deleting = false;
        programIndex = (programIndex + 1) % items.length;
        characterIndex = 0;
        typingTimer = window.setTimeout(animateProgram, 220);
        return;
    }

    const nextProgram = items[programIndex];
    characterIndex += 1;
    displayedProgram.value = nextProgram.slice(0, characterIndex);

    if (characterIndex >= nextProgram.length) {
        deleting = true;
        typingTimer = window.setTimeout(animateProgram, 1800);
        return;
    }

    typingTimer = window.setTimeout(animateProgram, 75);
}

function restartProgramAnimation(): void {
    clearTypingTimer();

    const items = programs.value.filter(Boolean);
    displayedProgram.value = items[0] || '';
    programIndex = 0;
    characterIndex = displayedProgram.value.length;
    deleting = true;

    if (!items.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        displayedProgram.value = items.join(' · ');
        return;
    }

    typingTimer = window.setTimeout(animateProgram, 1800);
}

watch(
    () => programs.value.join('\u0000'),
    () => {
        if (typingMounted) {
            restartProgramAnimation();
        }
    },
);

onMounted(() => {
    typingMounted = true;
    restartProgramAnimation();
});

onBeforeUnmount(() => {
    typingMounted = false;
    clearTypingTimer();
});
</script>

<template>
    <section id="accueil" :class="background" class="overflow-hidden border-b border-slate-200 px-4 py-10 sm:px-6 sm:py-14 lg:py-16">
        <div :class="container" class="mx-auto grid items-center gap-9 lg:grid-cols-[0.95fr_1.05fr] lg:gap-14">
            <div class="min-w-0" :class="alignment === 'center' ? 'text-center lg:text-left' : 'text-left'">
                <div class="relative max-w-xl" :class="alignment === 'center' ? 'mx-auto lg:mx-0' : ''">
                    <h1
                        class="hero-title text-balance font-bold leading-[1.12] tracking-[-0.025em]"
                        :class="dark ? 'text-white' : 'text-navy'"
                        :style="{ '--hero-title-size': `${titleFontSize}px` }"
                    >
                        <span
                            v-for="(segment, index) in titleSegments"
                            :key="`${index}-${segment.text}`"
                            :class="segment.color ? highlightClasses[segment.color] : undefined"
                        >
                            <span class="relative z-10">{{ segment.text }}</span>
                            <svg
                                v-if="segment.color"
                                class="hero-title-stroke"
                                viewBox="0 0 220 14"
                                preserveAspectRatio="none"
                                aria-hidden="true"
                            >
                                <path d="M4 10 C 60 3, 150 4, 216 8" />
                            </svg>
                        </span>
                    </h1>
                    <button
                        v-if="editing"
                        type="button"
                        class="absolute -right-2 -top-2 grid size-8 place-items-center rounded-full border border-edsp-green/30 bg-white text-edsp-green shadow-sm transition hover:bg-edsp-green hover:text-white dark:bg-slate-800"
                        aria-label="Modifier le titre et ses couleurs"
                        title="Modifier le titre et ses mises en évidence"
                        @click.stop="editField('settings.title_highlight_1')"
                    >
                        <Pencil :size="14" aria-hidden="true" />
                    </button>
                </div>
                <p class="mt-4 max-w-xl text-pretty text-[0.95rem] leading-7 sm:text-base" :class="[dark ? 'text-[#C9D4EE]' : 'text-slate-600', alignment === 'center' ? 'mx-auto lg:mx-0' : '']">
                    {{ content }}
                </p>

                <div class="mt-5 flex min-h-7 flex-wrap items-center gap-x-2 gap-y-1 text-sm font-semibold" :class="[dark ? 'text-white' : 'text-slate-700', alignment === 'center' ? 'justify-center lg:justify-start' : '']">
                    <span class="size-1.5 flex-none rounded-full bg-edsp-green" aria-hidden="true" />
                    <span>{{ kickerText }}</span>
                    <button
                        v-if="editing"
                        type="button"
                        class="grid size-7 flex-none place-items-center rounded-full border border-edsp-green/30 bg-white text-edsp-green shadow-sm transition hover:bg-edsp-green hover:text-white dark:bg-slate-800"
                        aria-label="Modifier le libellé des mentions"
                        title="Modifier le texte fixe"
                        @click.stop="editField('settings.kicker_text')"
                    >
                        <Pencil :size="13" aria-hidden="true" />
                    </button>
                    <span class="text-edsp-green" aria-hidden="true">{{ displayedProgram }}</span>
                    <span
                        class="h-4 w-px flex-none bg-edsp-green motion-safe:animate-pulse"
                        aria-hidden="true"
                    />
                    <button
                        v-if="editing"
                        type="button"
                        class="grid size-7 flex-none place-items-center rounded-full border border-edsp-green/30 bg-white text-edsp-green shadow-sm transition hover:bg-edsp-green hover:text-white dark:bg-slate-800"
                        aria-label="Modifier les deux parcours animés"
                        title="Modifier les textes animés"
                        @click.stop="editField('settings.rotating_item_1')"
                    >
                        <Pencil :size="13" aria-hidden="true" />
                    </button>
                    <span class="sr-only">{{ programs.join(tr(' et ', ' and ')) }}</span>
                </div>

                <div
                    class="mt-6 flex flex-row flex-wrap items-center gap-2.5"
                    :class="alignment === 'center' ? 'justify-center lg:justify-start' : 'justify-start'"
                >
                    <SmartLink :href="buttonUrl" class="button-dark hero-action-button justify-center">
                        {{ buttonText }}
                        <ArrowRight :size="16" aria-hidden="true" />
                    </SmartLink>
                    <SmartLink :href="secondaryButtonUrl" class="button-primary hero-action-button justify-center">
                        <UserPlus :size="16" aria-hidden="true" />
                        {{ secondaryButtonText }}
                    </SmartLink>
                </div>
                <div class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm" :class="[dark ? 'text-[#C9D4EE]' : 'text-slate-500', alignment === 'center' ? 'justify-center lg:justify-start' : '']">
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

            <div class="relative mx-auto w-full max-w-2xl pt-3 pl-3 sm:pt-5 sm:pl-5 lg:mx-0">
                <div
                    class="pointer-events-none absolute top-0 right-3 bottom-3 left-0 rounded-xl border border-dashed border-institutional/25 bg-institutional/[0.025] sm:right-5 sm:bottom-5 dark:border-slate-500/40 dark:bg-white/[0.02]"
                    aria-hidden="true"
                />
                <div
                    class="hero-media-card group relative z-10 h-64 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_10px_28px_rgba(11,31,85,0.09)] sm:h-[24rem] lg:h-[25rem]"
                >
                    <div v-if="image" class="hero-media-motion h-full w-full">
                        <MediaPlaceholder
                            :image-url="image"
                            :alt="alt"
                            eager
                            :label="tr(`Campus et vie étudiante de l'EDSP`, 'EDSP campus and student life')"
                        />
                    </div>
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
                                    <span class="font-heading text-sm font-semibold">{{ visualPrograms[0] }}</span>
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/8 px-4 py-4">
                                    <Landmark :size="20" class="flex-none text-gold" aria-hidden="true" />
                                    <span class="font-heading text-sm font-semibold">{{ visualPrograms[1] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="image"
                        class="absolute inset-x-0 bottom-0 flex items-center gap-3 bg-navy/90 px-4 py-3.5 text-white backdrop-blur-[2px] sm:px-5"
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

<style scoped>
.hero-title {
    font-size: clamp(2rem, 7vw, min(var(--hero-title-size, 2.75rem), 3.5rem));
}

.hero-title-highlight {
    position: relative;
    display: inline-block;
    color: inherit;
}

.hero-title-stroke {
    position: absolute;
    right: -0.04em;
    bottom: -0.06em;
    left: -0.04em;
    width: calc(100% + 0.08em);
    height: 0.22em;
    overflow: visible;
    pointer-events: none;
}

.hero-title-stroke path {
    fill: none;
    stroke: var(--hero-highlight-color);
    stroke-width: 4;
    stroke-linecap: round;
    stroke-linejoin: round;
    vector-effect: non-scaling-stroke;
}

.hero-title-highlight--green {
    --hero-highlight-color: rgb(7 139 62 / 82%);
}

.hero-title-highlight--blue {
    --hero-highlight-color: rgb(21 58 138 / 78%);
}

.hero-title-highlight--gold {
    --hero-highlight-color: rgb(184 129 18 / 78%);
}

.hero-media-card {
    transition:
        transform 500ms cubic-bezier(0.22, 1, 0.36, 1),
        box-shadow 500ms cubic-bezier(0.22, 1, 0.36, 1);
}

.hero-media-motion {
    transition:
        transform 750ms cubic-bezier(0.22, 1, 0.36, 1),
        filter 750ms ease;
    will-change: transform;
}

.hero-media-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 40px rgb(11 31 85 / 14%);
}

.hero-media-card:hover .hero-media-motion {
    filter: saturate(1.04) contrast(1.015);
    transform: scale(1.035) translate3d(0, -0.25%, 0);
}

:global(html.dark) .hero-title-highlight--green {
    --hero-highlight-color: rgb(74 222 128 / 88%);
}

:global(html.dark) .hero-title-highlight--blue {
    --hero-highlight-color: rgb(96 165 250 / 88%);
}

:global(html.dark) .hero-title-highlight--gold {
    --hero-highlight-color: rgb(245 183 49 / 88%);
}

@media (max-width: 639px) {
    .hero-title {
        font-size: clamp(1.85rem, 8vw, min(var(--hero-title-size, 2.25rem), 2.25rem));
        line-height: 1.16;
    }
}

@media (prefers-reduced-motion: reduce) {
    .hero-media-card,
    .hero-media-motion {
        transition: none;
    }

    .hero-media-card:hover,
    .hero-media-card:hover .hero-media-motion {
        filter: none;
        transform: none;
    }
}
</style>

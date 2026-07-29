<script setup lang="ts">
import { ArrowRight, BriefcaseBusiness, Camera, GraduationCap, Quote, UsersRound } from 'lucide-vue-next';
import { computed, inject } from 'vue';
import type { Section } from '../../types';
import { mediaUrl } from '../../lib/public-content';
import MediaPlaceholder from './MediaPlaceholder.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass, sectionSetting } from './section-theme';
import SmartLink from './SmartLink.vue';
import { useI18n } from '../../lib/i18n';
import { editSectionContextKey } from './edit-section-context';

const props = withDefaults(
    defineProps<{
        director?: Section | null;
        section?: Section | null;
    }>(),
    {
        director: null,
        section: null,
    },
);
const { tr } = useI18n();
const editContext = inject(editSectionContextKey, null);
const editing = computed(() => editContext?.editing.value === true);

const source = computed(() => props.director || props.section);
function numericSetting(key: string, fallback: number, minimum: number, maximum: number): number {
    const value = Number(source.value?.settings?.[key] ?? fallback);

    return Number.isFinite(value) ? Math.min(maximum, Math.max(minimum, value)) : fallback;
}

const imageZoom = computed(() => numericSetting('image_zoom', 100, 50, 200));
const imagePosition = computed(() => `${numericSetting('image_position_x', 50, 0, 100)}% ${numericSetting('image_position_y', 50, 0, 100)}%`);
const title = computed(() => source.value?.title || tr('Direction de l’EDSP', 'EDSP leadership'));
const eyebrow = computed(() => source.value?.subtitle || tr('Mot du directeur', "Director's message"));

function excerptFromHtml(value: string, maximumLength = 430): string {
    const plainText = value
        .replace(/<\/?(p|div|br|li|h[1-6])[^>]*>/gi, ' ')
        .replace(/<[^>]+>/g, '')
        .replace(/&nbsp;/gi, ' ')
        .replace(/&amp;/gi, '&')
        .replace(/&lt;/gi, '<')
        .replace(/&gt;/gi, '>')
        .replace(/&quot;/gi, '"')
        .replace(/&#039;|&apos;/gi, "'")
        .replace(/\s+/g, ' ')
        .trim();

    if (plainText.length <= maximumLength) {
        return plainText;
    }

    const shortened = plainText.slice(0, maximumLength + 1);
    const lastSpace = shortened.lastIndexOf(' ');

    return `${shortened.slice(0, lastSpace > maximumLength * 0.75 ? lastSpace : maximumLength).trim()}…`;
}

const content = computed(
    () =>
        (source.value?.content ? excerptFromHtml(source.value.content) : null) ||
        tr(
            'Chères étudiantes, chers étudiants, l’EDSP vous accueille dans un environnement où l’exigence académique, l’esprit critique et le sens des responsabilités guident chaque formation. Notre ambition est de former des juristes et des spécialistes de la science politique capables de servir la société et d’accompagner les transformations de Madagascar.',
            'Dear students, EDSP welcomes you to an environment where academic excellence, critical thinking and a sense of responsibility guide every programme. Our ambition is to educate legal professionals and political science specialists who are ready to serve society and support Madagascar’s transformation.',
        ),
);
const directorPosition = computed(() =>
    sectionSetting(source.value, 'director_position', tr("Directeur de l’EDSP", 'Director of EDSP')),
);
const signature = computed(() =>
    sectionSetting(source.value, 'director_signature', tr('Bienvenue à toutes et à tous à l’EDSP.', 'Welcome to EDSP.')),
);
const imageAlt = computed(() =>
    sectionSetting(source.value, 'alt_text', tr("Portrait du directeur de l’EDSP", 'Portrait of the Director of EDSP')),
);
const buttonText = computed(() => props.section?.button_text || tr('Lire le mot du directeur', "Read the Director's message"));
const buttonUrl = computed(() => props.section?.button_url || '/presentation');
const image = computed(() => mediaUrl(source.value));
const background = computed(() => sectionBackgroundClass(props.section, 'white'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section));
const dark = computed(() => isDarkSection(props.section));
const features = computed(() => [
    {
        description: sectionSetting(
            props.section,
            'feature_1_description',
            tr(
                'Des enseignements rigoureux, ancrés dans le droit positif malagasy et ouverts sur les débats contemporains.',
                'Rigorous teaching grounded in Malagasy law and open to contemporary debate.',
            ),
        ),
        title: sectionSetting(props.section, 'feature_1_title', tr('Excellence académique', 'Academic excellence')),
    },
    {
        description: sectionSetting(
            props.section,
            'feature_2_description',
            tr(
                'Une équipe pédagogique disponible accompagne chaque étudiant tout au long de son parcours.',
                'An accessible teaching team supports every student throughout their studies.',
            ),
        ),
        title: sectionSetting(props.section, 'feature_2_title', tr('Encadrement de proximité', 'Personal academic support')),
    },
    {
        description: sectionSetting(
            props.section,
            'feature_3_description',
            tr(
                'Des liens avec les institutions, les juridictions et le monde professionnel préparent l’insertion.',
                'Links with institutions, courts and employers prepare students for professional life.',
            ),
        ),
        title: sectionSetting(props.section, 'feature_3_title', tr('Ouverture professionnelle', 'Career readiness')),
    },
]);
</script>

<template>
    <section id="ecole" :class="background" class="public-section overflow-hidden">
        <div :class="container" class="mx-auto">
            <div class="grid items-center gap-9 lg:grid-cols-[0.92fr_1.08fr] lg:gap-14">
                <div class="relative mx-auto w-full max-w-xl pb-5 pr-4 sm:pb-7 sm:pr-7 lg:mx-0">
                    <div
                        class="pointer-events-none absolute inset-3 bottom-0 left-3 rounded-xl border border-dashed"
                        :class="dark ? 'border-emerald-400/35 bg-emerald-400/5' : 'border-slate-300 bg-slate-100/70 dark:border-emerald-400/35 dark:bg-emerald-400/5'"
                        aria-hidden="true"
                    ></div>
                    <div class="relative h-72 overflow-hidden rounded-xl border border-slate-200 shadow-[0_10px_28px_rgba(11,31,85,0.09)] sm:h-[24rem] dark:border-slate-700">
                        <MediaPlaceholder
                            :image-url="image"
                            :alt="imageAlt"
                            fit="contain"
                            :label="imageAlt"
                            :object-position="imagePosition"
                            :scale="imageZoom"
                        />
                        <button
                            v-if="editing && editContext?.openRelatedEditor"
                            type="button"
                            class="absolute right-3 top-3 z-20 inline-flex items-center gap-2 rounded-lg border border-white/80 bg-white/95 px-3 py-2 text-xs font-bold text-navy shadow-lg transition hover:bg-navy hover:text-white"
                            aria-label="Changer le portrait du directeur"
                            title="Changer le portrait du directeur"
                            @click.stop="editContext.openRelatedEditor('image_id')"
                        >
                            <Camera :size="16" aria-hidden="true" />
                            <span class="hidden sm:inline">Changer la photo</span>
                        </button>
                    </div>
                    <div
                        class="absolute bottom-0 left-4 right-8 flex items-center gap-3 rounded-lg border px-4 py-3 shadow-md sm:left-6 sm:right-auto sm:min-w-64"
                        :class="dark ? 'border-slate-700 bg-slate-900 text-white' : 'border-slate-100 bg-white text-navy dark:border-slate-700 dark:bg-slate-900 dark:text-white'"
                    >
                        <span class="grid size-10 flex-none place-items-center rounded-lg bg-gold/20 text-[#9A6B06]">
                            <Quote :size="20" aria-hidden="true" />
                        </span>
                        <span>
                            <strong class="block font-heading text-sm font-semibold">{{ title }}</strong>
                            <span class="mt-0.5 block text-xs" :class="dark ? 'text-slate-300' : 'text-slate-500'">
                                {{ directorPosition }}
                            </span>
                        </span>
                    </div>
                </div>

                <div :class="alignment === 'center' ? 'text-center lg:text-left' : 'text-left'">
                    <div
                        class="mb-5 inline-flex size-11 items-center justify-center rounded-xl"
                        :class="dark ? 'bg-gold/15 text-gold' : 'bg-edsp-green/10 text-edsp-green'"
                    >
                        <Quote :size="22" aria-hidden="true" />
                    </div>
                    <p class="section-eyebrow" :class="dark ? 'text-gold' : 'text-edsp-green'">
                        {{ eyebrow }}
                    </p>
                    <h2 class="section-title mt-2" :class="dark ? 'text-white' : 'text-navy'">
                        {{ title }}
                    </h2>
                    <p class="mt-2 font-heading text-sm font-semibold" :class="dark ? 'text-emerald-300' : 'text-edsp-green'">
                        {{ directorPosition }}
                    </p>
                    <blockquote
                        class="mt-5 max-w-2xl border-l-2 pl-5 text-pretty text-[0.95rem] leading-7 sm:text-base"
                        :class="dark ? 'border-gold/70 text-slate-300' : 'border-edsp-green/50 text-slate-600'"
                    >
                        <p class="whitespace-pre-line">{{ content }}</p>
                        <footer class="mt-4 font-heading text-sm font-semibold" :class="dark ? 'text-white' : 'text-navy'">
                            {{ signature }}
                        </footer>
                    </blockquote>
                    <SmartLink :href="buttonUrl" class="button-primary mt-7">
                        {{ buttonText }}
                        <ArrowRight :size="17" aria-hidden="true" />
                    </SmartLink>
                </div>
            </div>

            <div class="mt-12 grid gap-4 border-t pt-8 md:grid-cols-3" :class="dark ? 'border-white/10' : 'border-slate-200/80'">
                <article class="institution-card">
                    <span class="feature-icon bg-edsp-green/10 text-edsp-green">
                        <GraduationCap :size="25" aria-hidden="true" />
                    </span>
                    <h3 class="mt-4 text-lg font-semibold text-navy">{{ features[0].title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ features[0].description }}</p>
                </article>
                <article class="institution-card">
                    <span class="feature-icon bg-institutional/10 text-institutional dark:text-blue-300">
                        <UsersRound :size="25" aria-hidden="true" />
                    </span>
                    <h3 class="mt-4 text-lg font-semibold text-navy">{{ features[1].title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ features[1].description }}</p>
                </article>
                <article class="institution-card">
                    <span class="feature-icon bg-gold/20 text-[#8A6410] dark:text-gold">
                        <BriefcaseBusiness :size="25" aria-hidden="true" />
                    </span>
                    <h3 class="mt-4 text-lg font-semibold text-navy">{{ features[2].title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ features[2].description }}</p>
                </article>
            </div>
        </div>
    </section>
</template>

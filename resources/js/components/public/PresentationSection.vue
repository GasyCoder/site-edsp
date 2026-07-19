<script setup lang="ts">
import { ArrowRight, BriefcaseBusiness, GraduationCap, UsersRound } from 'lucide-vue-next';
import { computed } from 'vue';
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

const title = computed(() => props.section?.title || tr("Bienvenue à l'EDSP", 'Welcome to EDSP'));
const eyebrow = computed(() => props.section?.subtitle || tr("L'établissement", 'The School'));
const content = computed(
    () =>
        props.section?.content ||
        tr("L'École de Droit et Science Politique de l'Université de Mahajanga forme des étudiants capables de comprendre, d'analyser et d'accompagner les transformations juridiques, administratives, sociales et politiques de Madagascar.", 'The University of Mahajanga School of Law and Political Science educates students to understand, analyse and support Madagascar’s legal, administrative, social and political transformations.'),
);
const image = computed(() => mediaUrl(props.section));
const background = computed(() => sectionBackgroundClass(props.section, 'white'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section));
const dark = computed(() => isDarkSection(props.section));
const features = computed(() => [
    {
        description: sectionSetting(props.section, 'feature_1_description', tr('Des enseignements rigoureux, ancrés dans le droit positif malagasy et ouverts sur les débats contemporains.', 'Rigorous teaching grounded in Malagasy law and open to contemporary debate.')),
        title: sectionSetting(props.section, 'feature_1_title', tr('Excellence académique', 'Academic excellence')),
    },
    {
        description: sectionSetting(props.section, 'feature_2_description', tr('Une équipe pédagogique disponible accompagne chaque étudiant tout au long de son parcours.', 'An accessible teaching team supports every student throughout their studies.')),
        title: sectionSetting(props.section, 'feature_2_title', tr('Encadrement de proximité', 'Personal academic support')),
    },
    {
        description: sectionSetting(props.section, 'feature_3_description', tr("Des liens avec les institutions, les juridictions et le monde professionnel préparent l'insertion.", 'Links with institutions, courts and employers prepare students for professional life.')),
        title: sectionSetting(props.section, 'feature_3_title', tr('Ouverture professionnelle', 'Career readiness')),
    },
]);
</script>

<template>
    <section id="ecole" :class="background" class="px-4 py-16 sm:px-6 sm:py-20">
        <div :class="container" class="mx-auto">
            <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-14">
                <div class="h-72 overflow-hidden rounded-xl shadow-[0_12px_30px_rgba(11,31,85,0.12)] sm:h-90">
                    <MediaPlaceholder
                        :image-url="image"
                        :alt="title"
                        :label="tr(`Photo de l'établissement ou des étudiants`, 'Photo of the School or its students')"
                    />
                </div>
                <div :class="alignment === 'center' ? 'text-center' : 'text-left'">
                    <p class="text-xs font-bold uppercase tracking-[0.13em] sm:text-sm" :class="dark ? 'text-gold' : 'text-edsp-green'">
                        {{ eyebrow }}
                    </p>
                    <h2 class="mt-2 text-balance text-3xl font-bold sm:text-4xl" :class="dark ? 'text-white' : 'text-navy'">{{ title }}</h2>
                    <p class="mt-5 text-pretty text-base leading-8" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ content }}</p>
                    <SmartLink v-if="section?.button_text && section?.button_url" :href="section.button_url" class="button-primary mt-6">
                        {{ section.button_text }}
                        <ArrowRight :size="17" aria-hidden="true" />
                    </SmartLink>
                </div>
            </div>

            <div class="mt-12 grid gap-5 md:grid-cols-3">
                <article class="institution-card">
                    <span class="feature-icon bg-edsp-green/10 text-edsp-green">
                        <GraduationCap :size="25" aria-hidden="true" />
                    </span>
                    <h3 class="mt-4 text-lg font-semibold text-navy">{{ features[0].title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ features[0].description }}</p>
                </article>
                <article class="institution-card">
                    <span class="feature-icon bg-institutional/10 text-institutional">
                        <UsersRound :size="25" aria-hidden="true" />
                    </span>
                    <h3 class="mt-4 text-lg font-semibold text-navy">{{ features[1].title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ features[1].description }}</p>
                </article>
                <article class="institution-card">
                    <span class="feature-icon bg-gold/20 text-[#8A6410]">
                        <BriefcaseBusiness :size="25" aria-hidden="true" />
                    </span>
                    <h3 class="mt-4 text-lg font-semibold text-navy">{{ features[2].title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ features[2].description }}</p>
                </article>
            </div>
        </div>
    </section>
</template>

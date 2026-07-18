<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Section } from '../../types';
import { mediaUrl } from '../../lib/public-content';
import MediaPlaceholder from './MediaPlaceholder.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass, sectionSetting } from './section-theme';
import SmartLink from './SmartLink.vue';

const props = withDefaults(
    defineProps<{
        section?: Section | null;
    }>(),
    {
        section: null,
    },
);

const title = computed(() => props.section?.title || 'Une expérience universitaire enrichissante');
const eyebrow = computed(() => props.section?.subtitle || 'Vie étudiante');
const content = computed(
    () =>
        props.section?.content ||
        "Au-delà des cours, l'EDSP offre un cadre vivant où les étudiants apprennent, débattent et s'engagent.",
);
const image = computed(() => mediaUrl(props.section));
const background = computed(() => sectionBackgroundClass(props.section, 'white'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section));
const dark = computed(() => isDarkSection(props.section));
const activities = computed(() => [
    sectionSetting(props.section, 'item_1', 'Activités académiques'),
    sectionSetting(props.section, 'item_2', 'Conférences'),
    sectionSetting(props.section, 'item_3', 'Associations étudiantes'),
    sectionSetting(props.section, 'item_4', 'Événements culturels'),
    sectionSetting(props.section, 'item_5', 'Accompagnement pédagogique'),
    sectionSetting(props.section, 'item_6', 'Insertion professionnelle'),
]);
const secondaryMediaLabel = computed(() => sectionSetting(props.section, 'secondary_media_label', 'Conférence'));
const tertiaryMediaLabel = computed(() => sectionSetting(props.section, 'tertiary_media_label', 'Événement étudiant'));
</script>

<template>
    <section id="vie-etudiante" :class="background" class="px-4 py-16 sm:px-6 sm:py-20">
        <div :class="container" class="mx-auto grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center lg:gap-14">
            <div :class="alignment === 'center' ? 'text-center lg:text-left' : 'text-left'">
                <p class="text-xs font-bold uppercase tracking-[0.13em] sm:text-sm" :class="dark ? 'text-gold' : 'text-edsp-green'">
                    {{ eyebrow }}
                </p>
                <h2 class="mt-2 text-balance text-3xl font-bold sm:text-4xl" :class="dark ? 'text-white' : 'text-navy'">{{ title }}</h2>
                <p class="mt-5 text-pretty leading-7" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ content }}</p>
                <ul class="mt-6 grid gap-x-6 gap-y-3 sm:grid-cols-2">
                    <li v-for="(activity, index) in activities" :key="index" class="life-item" :class="dark ? 'text-[#C9D4EE]' : ''">
                        <span :class="index % 3 === 0 ? 'bg-edsp-green' : index % 3 === 1 ? 'bg-institutional' : 'bg-gold'" />{{ activity }}
                    </li>
                </ul>
                <SmartLink v-if="section?.button_text && section?.button_url" :href="section.button_url" class="button-primary mt-7">
                    {{ section.button_text }}
                    <ArrowRight :size="17" aria-hidden="true" />
                </SmartLink>
            </div>

            <div class="grid h-[25rem] grid-cols-2 gap-3 sm:grid-cols-[2fr_1fr] sm:grid-rows-2">
                <div class="col-span-2 overflow-hidden rounded-xl sm:col-span-1 sm:row-span-2">
                    <MediaPlaceholder
                        :image-url="image"
                        :alt="title"
                        label="Grande photo — vie étudiante"
                    />
                </div>
                <div class="overflow-hidden rounded-xl">
                    <MediaPlaceholder :label="secondaryMediaLabel" />
                </div>
                <div class="overflow-hidden rounded-xl">
                    <MediaPlaceholder :label="tertiaryMediaLabel" />
                </div>
            </div>
        </div>
    </section>
</template>

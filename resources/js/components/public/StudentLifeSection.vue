<script setup lang="ts">
import { ArrowRight, Camera } from 'lucide-vue-next';
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
        section?: Section | null;
    }>(),
    {
        section: null,
    },
);
const { tr } = useI18n();
const editContext = inject(editSectionContextKey, null);

const title = computed(() => props.section?.title || tr('Une expérience universitaire enrichissante', 'A rewarding university experience'));
const eyebrow = computed(() => props.section?.subtitle || tr('Vie étudiante', 'Student life'));
const content = computed(
    () =>
        props.section?.content ||
        tr("Au-delà des cours, l'EDSP offre un cadre vivant où les étudiants apprennent, débattent et s'engagent.", 'Beyond the classroom, EDSP offers a vibrant environment where students learn, debate and get involved.'),
);
const image = computed(() => mediaUrl(props.section));
const secondaryImage = computed(() => props.section?.secondary_thumbnail_url ?? props.section?.secondary_image_url ?? null);
const tertiaryImage = computed(() => props.section?.tertiary_thumbnail_url ?? props.section?.tertiary_image_url ?? null);
const editing = computed(() => editContext?.editing.value === true);
const background = computed(() => sectionBackgroundClass(props.section, 'white'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section));
const dark = computed(() => isDarkSection(props.section));
const activities = computed(() => [
    sectionSetting(props.section, 'item_1', tr('Activités académiques', 'Academic activities')),
    sectionSetting(props.section, 'item_2', tr('Conférences', 'Conferences')),
    sectionSetting(props.section, 'item_3', tr('Associations étudiantes', 'Student societies')),
    sectionSetting(props.section, 'item_4', tr('Événements culturels', 'Cultural events')),
    sectionSetting(props.section, 'item_5', tr('Accompagnement pédagogique', 'Academic support')),
    sectionSetting(props.section, 'item_6', tr('Insertion professionnelle', 'Career support')),
]);
const secondaryMediaLabel = computed(() => sectionSetting(props.section, 'secondary_media_label', tr('Conférence', 'Conference')));
const tertiaryMediaLabel = computed(() => sectionSetting(props.section, 'tertiary_media_label', tr('Événement étudiant', 'Student event')));
</script>

<template>
    <section id="vie-etudiante" :class="background" class="public-section">
        <div :class="container" class="mx-auto grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center lg:gap-14">
            <div :class="alignment === 'center' ? 'text-center lg:text-left' : 'text-left'">
                <p class="section-eyebrow" :class="dark ? 'text-gold' : 'text-edsp-green'">
                    {{ eyebrow }}
                </p>
                <h2 class="section-title mt-2" :class="dark ? 'text-white' : 'text-navy'">{{ title }}</h2>
                <p class="section-description mt-4" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ content }}</p>
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

            <div class="grid grid-cols-2 gap-3 sm:h-[23rem] sm:grid-cols-[2fr_1fr] sm:grid-rows-2">
                <div class="relative col-span-2 h-64 overflow-hidden rounded-lg sm:col-span-1 sm:row-span-2 sm:h-auto">
                    <MediaPlaceholder
                        :image-url="image"
                        :alt="title"
                        :label="tr('Grande photo de la vie étudiante', 'Student life photo')"
                    />
                    <button
                        v-if="editing"
                        type="button"
                        class="absolute right-2 top-2 z-10 inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white/95 px-3 py-2 text-xs font-bold text-navy shadow-md backdrop-blur transition hover:border-edsp-green hover:text-edsp-green"
                        title="Changer la grande photo"
                        aria-label="Changer la grande photo de la vie étudiante"
                        @click="editContext?.openEditor('image_id')"
                    >
                        <Camera :size="16" aria-hidden="true" />
                        <span class="hidden sm:inline">Changer la photo</span>
                    </button>
                </div>
                <div class="relative h-36 overflow-hidden rounded-lg sm:h-auto">
                    <MediaPlaceholder :image-url="secondaryImage" :alt="secondaryMediaLabel" :label="secondaryMediaLabel" />
                    <button
                        v-if="editing"
                        type="button"
                        class="absolute right-2 top-2 z-10 grid size-9 place-items-center rounded-lg border border-slate-200 bg-white/95 text-navy shadow-md backdrop-blur transition hover:border-edsp-green hover:text-edsp-green"
                        title="Changer la photo de conférence"
                        aria-label="Changer la photo de conférence"
                        @click="editContext?.openEditor('settings.secondary_media_id')"
                    >
                        <Camera :size="16" aria-hidden="true" />
                    </button>
                </div>
                <div class="relative h-36 overflow-hidden rounded-lg sm:h-auto">
                    <MediaPlaceholder :image-url="tertiaryImage" :alt="tertiaryMediaLabel" :label="tertiaryMediaLabel" />
                    <button
                        v-if="editing"
                        type="button"
                        class="absolute right-2 top-2 z-10 grid size-9 place-items-center rounded-lg border border-slate-200 bg-white/95 text-navy shadow-md backdrop-blur transition hover:border-edsp-green hover:text-edsp-green"
                        title="Changer la photo de l’événement étudiant"
                        aria-label="Changer la photo de l’événement étudiant"
                        @click="editContext?.openEditor('settings.tertiary_media_id')"
                    >
                        <Camera :size="16" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Section, Testimonial } from '../../types';
import SectionHeading from './SectionHeading.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass } from './section-theme';
import TestimonialCard from './TestimonialCard.vue';
import { useI18n } from '../../lib/i18n';

const props = withDefaults(
    defineProps<{
        section?: Section | null;
        testimonials: Testimonial[];
    }>(),
    {
        section: null,
    },
);
const { tr } = useI18n();

const background = computed(() => sectionBackgroundClass(props.section, 'white'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section, 'center'));
const dark = computed(() => isDarkSection(props.section));
</script>

<template>
    <section :class="background" class="px-4 py-16 sm:px-6 sm:py-20">
        <div :class="container" class="mx-auto">
            <SectionHeading
                :eyebrow="section?.subtitle || tr('Ils en parlent', 'Their experience')"
                :title="section?.title || tr(`Paroles d'étudiants`, 'Student voices')"
                :description="section?.content"
                :align="alignment"
                :dark="dark"
            />
            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <TestimonialCard
                    v-for="testimonial in testimonials"
                    :key="testimonial.id"
                    :testimonial="testimonial"
                />
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Section, TeamMember } from '../../types';
import SectionHeading from './SectionHeading.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass } from './section-theme';
import SmartLink from './SmartLink.vue';
import TeamMemberCard from './TeamMemberCard.vue';

const props = withDefaults(
    defineProps<{
        members: TeamMember[];
        section?: Section | null;
    }>(),
    {
        section: null,
    },
);

const background = computed(() => sectionBackgroundClass(props.section, 'light'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section, 'center'));
const dark = computed(() => isDarkSection(props.section, 'light'));
</script>

<template>
    <section id="equipe" :class="background" class="px-4 py-16 sm:px-6 sm:py-20">
        <div :class="container" class="mx-auto">
            <SectionHeading
                :eyebrow="section?.subtitle || `L'équipe`"
                :title="section?.title || 'Direction et équipe pédagogique'"
                :description="section?.content"
                :align="alignment"
                :dark="dark"
            />
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <TeamMemberCard v-for="member in members" :key="member.id" :member="member" />
            </div>
            <div v-if="section?.button_text && section?.button_url" class="mt-8 flex" :class="alignment === 'center' ? 'justify-center' : 'justify-start'">
                <SmartLink :href="section.button_url" class="button-primary">
                    {{ section.button_text }}
                    <ArrowRight :size="17" aria-hidden="true" />
                </SmartLink>
            </div>
        </div>
    </section>
</template>

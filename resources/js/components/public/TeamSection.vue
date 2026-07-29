<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Section, TeamMember } from '../../types';
import SectionHeading from './SectionHeading.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass } from './section-theme';
import SmartLink from './SmartLink.vue';
import TeamMemberCard from './TeamMemberCard.vue';
import { useI18n } from '../../lib/i18n';

const props = withDefaults(
    defineProps<{
        members: TeamMember[];
        section?: Section | null;
    }>(),
    {
        section: null,
    },
);
const { tr } = useI18n();

const background = computed(() => sectionBackgroundClass(props.section, 'light'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section, 'center'));
const dark = computed(() => isDarkSection(props.section, 'light'));
</script>

<template>
    <section id="equipe" :class="background" class="public-section">
        <div :class="container" class="mx-auto">
            <SectionHeading
                :eyebrow="section?.subtitle || tr(`L'équipe`, 'Our team')"
                :title="section?.title || tr('Direction et équipe pédagogique', 'Leadership and teaching team')"
                :description="section?.content"
                :align="alignment"
                :dark="dark"
            />
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
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

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import AdmissionsSection from '../components/public/AdmissionsSection.vue';
import EditableSection from '../components/public/EditableSection.vue';
import EditModeToggle from '../components/public/EditModeToggle.vue';
import FinalCtaSection from '../components/public/FinalCtaSection.vue';
import HeroSection from '../components/public/HeroSection.vue';
import LibrarySection from '../components/public/LibrarySection.vue';
import NewsSection from '../components/public/NewsSection.vue';
import PresentationSection from '../components/public/PresentationSection.vue';
import PartnersSection from '../components/public/PartnersSection.vue';
import ProgramsSection from '../components/public/ProgramsSection.vue';
import SeoHead from '../components/public/SeoHead.vue';
import StatsSection from '../components/public/StatsSection.vue';
import StudentLifeSection from '../components/public/StudentLifeSection.vue';
import TeamSection from '../components/public/TeamSection.vue';
import TestimonialsSection from '../components/public/TestimonialsSection.vue';
import PublicLayout from '../layouts/PublicLayout.vue';
import { mediaUrl } from '../lib/public-content';
import type {
    AdmissionCampaign,
    Article,
    Page,
    Partner,
    Program,
    Section,
    SiteSettings,
    TeamMember,
    Testimonial,
    SeoData,
} from '../types';

const props = withDefaults(
    defineProps<{
        campaign?: AdmissionCampaign | null;
        canEdit?: boolean;
        directorMessage?: Section | null;
        news?: Article[];
        page?: Page | null;
        partners?: Partner[];
        programs?: Program[];
        settings?: SiteSettings;
        seo?: SeoData;
        teamMembers?: TeamMember[];
        testimonials?: Testimonial[];
    }>(),
    {
        campaign: null,
        canEdit: false,
        directorMessage: null,
        news: () => [],
        page: null,
        partners: () => [],
        programs: () => [],
        settings: () => ({}),
        seo: () => ({}),
        teamMembers: () => [],
        testimonials: () => [],
    },
);

const editing = ref(false);
const sections = computed(() => (props.page?.sections ?? [])
    .filter((section) => section.is_visible || (props.canEdit && editing.value))
    .sort((left, right) => (left.position ?? 0) - (right.position ?? 0)));

function sectionKind(section: Section): string {
    const value = `${section.section_key} ${section.section_type}`.toLocaleLowerCase('fr');

    if (value.includes('call-to-action') || value.includes('cta')) return 'cta';
    if (value.includes('hero')) return 'hero';
    if (value.includes('presentation')) return 'presentation';
    if (value.includes('program')) return 'programs';
    if (value.includes('stats') || value.includes('chiffre')) return 'stats';
    if (value.includes('admission')) return 'admissions';
    if (value.includes('news') || value.includes('actualite')) return 'news';
    if (value.includes('student') || value.includes('vie-etudiante')) return 'student-life';
    if (value.includes('library') || value.includes('bibliotheque')) return 'library';
    if (value.includes('testimonial') || value.includes('temoignage')) return 'testimonials';
    if (value.includes('partner') || value.includes('partenaire')) return 'partners';
    if (value.includes('team') || value.includes('equipe')) return 'team';

    return 'content';
}

const heroImage = computed(() => mediaUrl(sections.value.find((section) => sectionKind(section) === 'hero')));

watch(
    () => props.canEdit,
    (canEdit) => {
        if (!canEdit) {
            editing.value = false;
        }
    },
);
</script>

<template>
    <SeoHead
        :title="seo.title || page?.meta_title || settings.default_meta_title || page?.title"
        :description="seo.description || page?.meta_description || settings.default_meta_description"
        :canonical-url="seo.canonical || page?.canonical_url"
        :image-url="seo.og_image || page?.og_image_url || heroImage"
        :keywords="seo.keywords"
        :open-graph-title="seo.og_title"
        :open-graph-description="seo.og_description"
        :structured-data="seo.schema"
        :no-index="seo.robots?.includes('noindex') || page?.robots_index === false"
        :no-follow="seo.robots?.includes('nofollow') || page?.robots_follow === false"
    />

    <PublicLayout :settings="settings" :editing="editing">
        <template v-for="section in sections" :key="section.id">
            <EditableSection
                :section="section"
                :editing="editing"
                :related-section="sectionKind(section) === 'presentation' ? directorMessage : null"
            >
                <HeroSection v-if="sectionKind(section) === 'hero'" :section="section" />
                <PresentationSection
                    v-else-if="sectionKind(section) === 'presentation' || sectionKind(section) === 'content'"
                    :section="section"
                    :director="directorMessage"
                />
                <ProgramsSection v-else-if="sectionKind(section) === 'programs'" :section="section" :programs="programs" />
                <StatsSection v-else-if="sectionKind(section) === 'stats'" :section="section" :programs="programs" />
                <AdmissionsSection v-else-if="sectionKind(section) === 'admissions'" :section="section" :campaign="campaign" />
                <NewsSection v-else-if="sectionKind(section) === 'news'" :section="section" :news="news" />
                <StudentLifeSection v-else-if="sectionKind(section) === 'student-life'" :section="section" />
                <LibrarySection v-else-if="sectionKind(section) === 'library'" :section="section" :settings="settings" />
                <TeamSection v-else-if="sectionKind(section) === 'team'" :section="section" :members="teamMembers" />
                <TestimonialsSection v-else-if="sectionKind(section) === 'testimonials'" :section="section" :testimonials="testimonials" />
                <PartnersSection v-else-if="sectionKind(section) === 'partners'" :section="section" :partners="partners" />
                <FinalCtaSection v-else-if="sectionKind(section) === 'cta'" :section="section" />
            </EditableSection>

        </template>
    </PublicLayout>

    <EditModeToggle v-if="canEdit" :active="editing" @toggle="editing = !editing" />
</template>

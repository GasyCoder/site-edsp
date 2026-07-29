<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowRight,
    CalendarDays,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Clock,
    Download,
    GraduationCap,
    Globe,
    HeartHandshake,
    Lightbulb,
    Mail,
    MapPin,
    Phone,
    PlayCircle,
    Send,
    UserPlus,
} from 'lucide-vue-next';
import PublicLayout from '../layouts/PublicLayout.vue';
import SeoHead from '../components/public/SeoHead.vue';
import RichText from '../components/public/RichText.vue';
import EditableSection from '../components/public/EditableSection.vue';
import DirectorMessageSection from '../components/public/DirectorMessageSection.vue';
import EditModeToggle from '../components/public/EditModeToggle.vue';
import MediaPlaceholder from '../components/public/MediaPlaceholder.vue';
import GalleryCollection from '../components/public/GalleryCollection.vue';
import SmartLink from '../components/public/SmartLink.vue';
import NewsCard from '../components/public/NewsCard.vue';
import TeamMemberCard from '../components/public/TeamMemberCard.vue';
import type { AdmissionCampaign, Article, MediaAsset, Page, PublicGallery, Section, SeoData, SiteSettings, TeamMember } from '../types';
import { formatPublicDate, mediaThumbnailUrl, mediaUrl, safePublicUrl, setting } from '../lib/public-content';
import { useI18n } from '../lib/i18n';

type CmsPage = Page & {
    canonical_url?: string | null;
    robots_follow?: boolean;
    robots_index?: boolean;
};

type Partner = {
    id: number;
    name: string;
    description?: string | null;
    url?: string | null;
    logo?: MediaAsset | null;
};

type PublicDocument = {
    id: number;
    title: string;
    category?: string | null;
    description?: string | null;
    download_url?: string | null;
};

const props = withDefaults(defineProps<{
    campaign?: AdmissionCampaign | null;
    canEdit?: boolean;
    documents?: PublicDocument[];
    galleries?: PublicGallery[];
    news?: Article[];
    page: CmsPage;
    partners?: Partner[];
    seo?: SeoData;
    teamMembers?: TeamMember[];
}>(), {
    campaign: null,
    canEdit: false,
    documents: () => [],
    galleries: () => [],
    news: () => [],
    partners: () => [],
    seo: () => ({}),
    teamMembers: () => [],
});
const editing = ref(false);
const { languageTag, tr } = useI18n();

const contactForm = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    organization: '',
    subject: '',
    message: '',
    consent: false,
    website: '',
});

const visibleSections = computed<Section[]>(() =>
    props.page.sections
        .filter((section) => section.is_visible || (props.canEdit && editing.value))
        .filter((section) => section.title || section.subtitle || section.content || (props.canEdit && editing.value))
        .sort((left, right) => (left.position ?? 0) - (right.position ?? 0)),
);

const heroImage = computed(() => {
    for (const section of props.page.sections) {
        if (!section.is_visible) continue;

        const url = mediaUrl(section);

        if (url && !section.title && !section.subtitle && !section.content) {
            return { alt: section.image?.alt_text || props.page.title, url };
        }
    }

    return null;
});
const sectionBackground = (section: Section): string => {
    const backgrounds: Record<string, string> = {
        blue: 'bg-navy text-white',
        light: 'bg-soft',
        white: 'bg-white',
    };

    return backgrounds[section.settings?.background ?? 'white'] ?? backgrounds.white;
};

const sectionContainer = (section: Section): string => {
    const containers: Record<string, string> = {
        default: 'max-w-5xl',
        narrow: 'max-w-3xl',
        wide: 'max-w-7xl',
    };

    return containers[section.settings?.container ?? 'default'] ?? containers.default;
};

const sectionAlignment = (section: Section): string => section.settings?.alignment === 'center'
    ? 'text-center'
    : 'text-left';

const valueIcons = [GraduationCap, Lightbulb, HeartHandshake, Globe];

const directionMembers = computed(() => props.teamMembers.filter((member) => /direction/i.test(member.position ?? '')));
const otherMembers = computed(() => props.teamMembers.filter((member) => !directionMembers.value.includes(member)));

const sectionsWrapperClass = computed(() => {
    if (props.page.slug === 'historique') return 'mx-auto max-w-7xl px-6 py-14 sm:py-16';
    if (props.page.slug === 'missions-et-valeurs') return 'mx-auto grid max-w-7xl gap-6 px-6 py-14 sm:grid-cols-2 sm:py-16';

    return '';
});

const documentUrl = (document: PublicDocument): string | null => safePublicUrl(document.download_url);

const studentLifePhotos = computed(() => props.galleries
    .flatMap((gallery) => gallery.images ?? [])
    .filter((image) => mediaUrl(image.media))
    .slice(0, 10));

const photoCarousel = ref<HTMLElement | null>(null);
let carouselTimer: ReturnType<typeof setInterval> | null = null;

const scrollPhotos = (direction: 1 | -1): void => {
    const element = photoCarousel.value;

    if (!element) return;

    const nearEnd = element.scrollLeft + element.clientWidth >= element.scrollWidth - 16;

    if (direction === 1 && nearEnd) {
        element.scrollTo({ left: 0, behavior: 'smooth' });
        return;
    }

    element.scrollBy({ left: direction * element.clientWidth * 0.75, behavior: 'smooth' });
};

const pausePhotoAutoplay = (): void => {
    if (carouselTimer !== null) {
        clearInterval(carouselTimer);
        carouselTimer = null;
    }
};

const startPhotoAutoplay = (): void => {
    pausePhotoAutoplay();

    if (props.page.slug === 'vie-etudiante' && studentLifePhotos.value.length > 1) {
        carouselTimer = setInterval(() => scrollPhotos(1), 4500);
    }
};

onMounted(startPhotoAutoplay);
onBeforeUnmount(pausePhotoAutoplay);

const siteSettings = computed(() => (usePage().props as { settings?: SiteSettings }).settings);
const contactDetails = computed(() => ({
    address: setting(siteSettings.value, 'address', 'Ambondrona, Mahajanga'),
    email: setting(siteSettings.value, 'email', 'edsp.mahajanga@gmail.com'),
    phones: [
        setting(siteSettings.value, 'phone', '+261 32 05 579 90'),
        siteSettings.value?.phone_secondary || '+261 32 98 091 18',
    ].filter((phone, index, list) => phone && list.indexOf(phone) === index),
}));

const submitContact = (): void => {
    contactForm.post('/contact', {
        preserveScroll: true,
        onSuccess: () => contactForm.reset(),
    });
};
</script>

<template>
    <SeoHead
        :title="seo.title || page.meta_title || `${page.title} | EDSP`"
        :description="seo.description || page.meta_description"
        :canonical-url="seo.canonical || page.canonical_url"
        :image-url="seo.og_image || page.og_image_url"
        :keywords="seo.keywords"
        :open-graph-title="seo.og_title"
        :open-graph-description="seo.og_description"
        :structured-data="seo.schema"
        :no-index="seo.robots?.includes('noindex') || page.robots_index === false"
        :no-follow="seo.robots?.includes('nofollow') || page.robots_follow === false"
    />

    <PublicLayout :editing="editing">
        <header
            class="public-page-hero"
            :class="page.slug === 'galerie' ? 'bg-navy' : 'bg-soft'"
        >
            <div class="mx-auto max-w-7xl">
                <nav :aria-label="tr('Fil d’Ariane', 'Breadcrumb')" class="mb-6">
                    <ol class="flex flex-wrap items-center gap-2 text-sm" :class="page.slug === 'galerie' ? 'text-slate-300' : 'text-gray-500'">
                        <li>
                            <Link href="/" class="transition" :class="page.slug === 'galerie' ? 'hover:text-white' : 'hover:text-edsp-green'">{{ tr('Accueil', 'Home') }}</Link>
                        </li>
                        <li aria-hidden="true"><ChevronRight :size="15" /></li>
                        <li class="font-semibold" :class="page.slug === 'galerie' ? 'text-white' : 'text-navy'" aria-current="page">{{ page.title }}</li>
                    </ol>
                </nav>

                <div :class="heroImage ? 'grid items-center gap-10 lg:grid-cols-[1.05fr_0.95fr]' : 'max-w-3xl'">
                    <div>
                        <p class="section-eyebrow mb-3">
                            {{ tr('École de Droit et Science Politique', 'School of Law and Political Science') }}
                        </p>
                        <h1 class="page-title" :class="page.slug === 'galerie' ? 'text-white' : 'text-navy'">
                            {{ page.title }}
                        </h1>
                        <p v-if="page.meta_description" class="section-description max-w-2xl" :class="page.slug === 'galerie' ? 'text-slate-300' : 'text-gray-600'">
                            {{ page.meta_description }}
                        </p>
                    </div>

                    <div v-if="heroImage" class="h-56 overflow-hidden rounded-xl border border-slate-200 sm:h-64 dark:border-white/15">
                        <MediaPlaceholder
                            :image-url="heroImage.url"
                            :alt="heroImage.alt"
                            :label="page.title"
                            eager
                        />
                    </div>
                </div>
            </div>
        </header>

        <div class="bg-white">
            <div v-if="visibleSections.length" :class="sectionsWrapperClass">
                <EditableSection
                    v-for="(section, sectionIndex) in visibleSections"
                    :key="section.id"
                    :section="section"
                    :editing="editing"
                >
                    <DirectorMessageSection
                        v-if="page.slug === 'presentation' && section.section_type === 'director-message'"
                        :section="section"
                    />
                    <article
                        v-else-if="page.slug === 'missions-et-valeurs'"
                        class="surface-card h-full p-6"
                        :aria-labelledby="section.title ? `section-${section.id}` : undefined"
                    >
                        <span class="grid size-12 place-items-center rounded-xl bg-edsp-green/10 text-edsp-green" aria-hidden="true">
                            <component :is="valueIcons[sectionIndex % valueIcons.length]" :size="24" />
                        </span>
                        <p v-if="section.subtitle" class="mt-5 text-xs font-bold uppercase tracking-[0.14em] text-edsp-green">
                            {{ section.subtitle }}
                        </p>
                        <h2
                            v-if="section.title"
                            :id="`section-${section.id}`"
                            class="mt-5 text-xl font-bold leading-snug text-navy"
                        >
                            {{ section.title }}
                        </h2>
                        <RichText
                            v-if="section.content"
                            :html="section.content"
                            class="mt-3 text-base leading-7 text-gray-600"
                        />
                    </article>
                    <article
                        v-else-if="page.slug === 'historique'"
                        class="relative ml-3 border-l-2 border-edsp-green/25 pb-12 pl-8 last:pb-0 sm:pl-10"
                        :aria-labelledby="section.title ? `section-${section.id}` : undefined"
                    >
                        <span class="absolute -left-[9px] top-1.5 size-4 rounded-full border-2 border-white bg-edsp-green shadow-md dark:border-slate-950" aria-hidden="true" />
                        <p v-if="section.subtitle" class="text-sm font-bold uppercase tracking-[0.14em] text-edsp-green">
                            {{ section.subtitle }}
                        </p>
                        <h2
                            v-if="section.title"
                            :id="`section-${section.id}`"
                            class="mt-1.5 text-xl font-bold leading-snug text-navy sm:text-2xl"
                        >
                            {{ section.title }}
                        </h2>
                        <RichText
                            v-if="section.content"
                            :html="section.content"
                            class="mt-3 text-base leading-7 text-gray-600"
                        />
                        <div v-if="mediaUrl(section)" class="mt-5 h-56 overflow-hidden rounded-xl shadow-md sm:h-64">
                            <MediaPlaceholder
                                :image-url="mediaUrl(section)"
                                :alt="section.image?.alt_text || section.title"
                                :label="section.title || tr('Illustration de l’étape', 'Milestone illustration')"
                            />
                        </div>
                    </article>
                    <section
                        v-else
                        :class="sectionBackground(section)"
                        class="relative border-b border-gray-200 px-6 py-14 sm:py-18"
                        :aria-labelledby="section.title ? `section-${section.id}` : undefined"
                    >
                        <div
                            :class="[sectionContainer(section), sectionAlignment(section)]"
                            class="mx-auto grid items-center gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(16rem,0.8fr)]"
                        >
                            <div class="min-w-0" :class="!mediaUrl(section) && 'lg:col-span-2'">
                            <p
                                v-if="section.subtitle"
                                class="text-xs font-bold uppercase tracking-[0.16em]"
                                :class="section.settings?.background === 'blue' ? 'text-gold' : 'text-edsp-green'"
                            >
                                {{ section.subtitle }}
                            </p>
                            <h2
                                v-if="section.title"
                                :id="`section-${section.id}`"
                                class="mt-2 text-2xl font-bold leading-tight sm:text-3xl"
                                :class="section.settings?.background === 'blue' ? 'text-white' : 'text-navy'"
                            >
                                {{ section.title }}
                            </h2>
                            <RichText
                                v-if="section.content"
                                :html="section.content"
                                class="mt-5 text-base sm:text-lg"
                                :class="section.settings?.background === 'blue' ? 'text-blue-100' : 'text-gray-600'"
                            />

                            <SmartLink
                                v-if="section.button_text && section.button_url"
                                :href="section.button_url"
                                class="mt-7 inline-flex items-center gap-2 rounded-md bg-navy px-5 py-3 font-heading text-sm font-semibold text-white transition hover:bg-institutional"
                            >
                                {{ section.button_text }}
                                <ArrowRight :size="17" aria-hidden="true" />
                            </SmartLink>
                            </div>

                            <div v-if="mediaUrl(section)" class="h-64 overflow-hidden rounded-xl border border-slate-200 sm:h-80">
                                <MediaPlaceholder
                                    :image-url="mediaUrl(section)"
                                    :alt="section.image?.alt_text || section.title"
                                    :label="section.title || tr('Illustration de la section', 'Section illustration')"
                                />
                            </div>
                        </div>
                    </section>
                </EditableSection>
            </div>

            <section v-if="page.slug === 'equipe' && teamMembers.length" class="public-section bg-soft" aria-labelledby="team-list-title">
                <div class="mx-auto max-w-7xl">
                    <h2 id="team-list-title" class="sr-only">{{ tr('Membres de l’équipe', 'Team members') }}</h2>

                    <div v-if="directionMembers.length" class="grid gap-6">
                        <article
                            v-for="member in directionMembers"
                            :key="member.id"
                            class="surface-card flex flex-col items-center gap-6 p-6 text-center sm:flex-row sm:p-8 sm:text-left"
                        >
                            <div class="size-36 flex-none overflow-hidden rounded-xl border border-slate-200 sm:size-40">
                                <MediaPlaceholder
                                    :image-url="mediaThumbnailUrl(member.photo) || member.photo_url"
                                    :alt="member.photo?.alt_text || `${member.first_name} ${member.last_name}`"
                                    :label="tr(`Portrait de ${member.first_name} ${member.last_name}`, `Portrait of ${member.first_name} ${member.last_name}`)"
                                />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-edsp-green">{{ member.position }}</p>
                                <h3 class="mt-2 text-2xl font-bold text-navy">{{ `${member.first_name} ${member.last_name}`.trim() }}</h3>
                                <p v-if="member.biography" class="mt-3 leading-7 text-gray-600">{{ member.biography }}</p>
                                <div v-if="member.email || member.phone" class="mt-5 flex flex-wrap justify-center gap-3 sm:justify-start">
                                    <a
                                        v-if="member.email"
                                        :href="`mailto:${member.email}`"
                                        class="inline-flex items-center gap-2 rounded-md border border-slate-200 bg-soft px-4 py-2 text-sm font-semibold text-navy transition hover:border-edsp-green hover:text-edsp-green"
                                    >
                                        <Mail :size="15" aria-hidden="true" /> {{ member.email }}
                                    </a>
                                    <a
                                        v-if="member.phone"
                                        :href="`tel:${member.phone.replace(/\s+/g, '')}`"
                                        class="inline-flex items-center gap-2 rounded-md border border-slate-200 bg-soft px-4 py-2 text-sm font-semibold text-navy transition hover:border-edsp-green hover:text-edsp-green"
                                    >
                                        <Phone :size="15" aria-hidden="true" /> {{ member.phone }}
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div v-if="otherMembers.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" :class="directionMembers.length > 0 && 'mt-8'">
                        <TeamMemberCard v-for="member in otherMembers" :key="member.id" :member="member" />
                    </div>
                </div>
            </section>

            <GalleryCollection v-if="page.slug === 'galerie'" :galleries="galleries" />

            <section v-if="page.slug === 'partenaires' && partners.length" class="public-section bg-soft" aria-labelledby="partners-list-title">
                <div class="mx-auto max-w-7xl">
                    <h2 id="partners-list-title" class="text-2xl font-bold text-navy sm:text-3xl">{{ tr('Partenaires', 'Partners') }}</h2>
                    <div class="mt-9 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <article v-for="partner in partners" :key="partner.id" class="surface-card p-6">
                            <div class="h-28 overflow-hidden rounded-lg">
                                <MediaPlaceholder
                                    :image-url="mediaThumbnailUrl(partner.logo)"
                                    :alt="partner.logo?.alt_text || tr(`Logo ${partner.name}`, `${partner.name} logo`)"
                                    :label="tr(`Logo ${partner.name}`, `${partner.name} logo`)"
                                />
                            </div>
                            <h3 class="mt-5 text-lg font-bold text-navy">{{ partner.name }}</h3>
                            <p v-if="partner.description" class="mt-2 text-sm leading-6 text-gray-600">{{ partner.description }}</p>
                            <SmartLink v-if="partner.url" :href="partner.url" class="mt-4 inline-flex font-semibold text-institutional hover:underline">
                                {{ tr('Visiter le site', 'Visit website') }}
                            </SmartLink>
                        </article>
                    </div>
                </div>
            </section>

            <section v-if="page.slug === 'bibliotheque' && documents.length" class="public-section bg-soft" aria-labelledby="documents-list-title">
                <div class="mx-auto max-w-5xl">
                    <h2 id="documents-list-title" class="text-2xl font-bold text-navy sm:text-3xl">{{ tr('Ressources disponibles', 'Available resources') }}</h2>
                    <ul class="surface-card mt-8 divide-y divide-gray-200 px-6">
                        <li v-for="document in documents" :key="document.id" class="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p v-if="document.category" class="text-xs font-bold uppercase tracking-wide text-edsp-green">{{ document.category }}</p>
                                <h3 class="mt-1 font-bold text-navy">{{ document.title }}</h3>
                                <p v-if="document.description" class="mt-1 text-sm text-gray-600">{{ document.description }}</p>
                            </div>
                            <SmartLink v-if="documentUrl(document)" :href="documentUrl(document)!" class="button-secondary flex-none">
                                <Download :size="17" aria-hidden="true" /> {{ tr('Télécharger', 'Download') }}
                            </SmartLink>
                        </li>
                    </ul>
                </div>
            </section>

            <section v-if="page.slug === 'admissions'" class="bg-soft px-6 py-14 sm:py-16" aria-labelledby="campaign-information-title">
                <div class="mx-auto max-w-7xl">
                    <template v-if="campaign">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-edsp-green">
                                    {{ tr('Campagne d’admission', 'Admission round') }}
                                    <template v-if="campaign.academic_year"> · {{ campaign.academic_year }}</template>
                                </p>
                                <h2 id="campaign-information-title" class="mt-2 text-2xl font-bold text-navy sm:text-3xl">{{ campaign.title }}</h2>
                            </div>
                            <p
                                v-if="campaign.closes_at"
                                class="inline-flex items-center gap-2 rounded-md border border-edsp-green/30 bg-edsp-green/10 px-4 py-2 text-sm font-bold text-edsp-green"
                            >
                                <CalendarDays :size="16" aria-hidden="true" />
                                {{ tr('Ouverte jusqu’au', 'Open until') }} {{ formatPublicDate(campaign.closes_at, languageTag) }}
                            </p>
                        </div>

                        <div class="mt-8 grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
                            <article class="surface-card p-7 sm:p-8">
                                <h3 class="text-xl font-bold text-navy">{{ tr('Comment candidater', 'How to apply') }}</h3>
                                <RichText v-if="campaign.instructions" :html="campaign.instructions" class="mt-4 leading-7 text-gray-600" />
                                <div class="mt-7 flex flex-wrap gap-3">
                                    <Link href="/inscription" class="button-primary">
                                        <UserPlus :size="18" aria-hidden="true" />
                                        {{ tr('Commencer l’inscription', 'Start your application') }}
                                    </Link>
                                    <SmartLink
                                        v-if="safePublicUrl(campaign.tutorial_video_url)"
                                        :href="safePublicUrl(campaign.tutorial_video_url)!"
                                        class="button-secondary"
                                    >
                                        <PlayCircle :size="18" aria-hidden="true" />
                                        {{ tr('Voir le tutoriel vidéo', 'Watch the video tutorial') }}
                                    </SmartLink>
                                </div>
                            </article>

                            <aside class="surface-card p-7 sm:p-8" aria-labelledby="required-documents-title">
                                <h3 id="required-documents-title" class="text-xl font-bold text-navy">{{ tr('Pièces à fournir', 'Required documents') }}</h3>
                                <ul v-if="campaign.required_documents?.length" class="mt-5 space-y-3.5">
                                    <li
                                        v-for="document in campaign.required_documents"
                                        :key="document.key"
                                        class="flex items-start gap-3 leading-6 text-gray-600"
                                    >
                                        <CheckCircle2 :size="19" class="mt-0.5 flex-none text-edsp-green" aria-hidden="true" />
                                        {{ document.label }}
                                    </li>
                                </ul>
                                <p v-else class="mt-4 leading-7 text-gray-600">
                                    {{ tr('La liste des pièces est précisée dans le formulaire d’inscription.', 'The document list is detailed in the application form.') }}
                                </p>
                            </aside>
                        </div>
                    </template>

                    <div v-else class="surface-card mx-auto max-w-2xl p-8 text-center">
                        <h2 id="campaign-information-title" class="text-2xl font-bold text-navy">
                            {{ tr('Aucune campagne n’est ouverte actuellement', 'No admission round is currently open') }}
                        </h2>
                        <p class="mt-3 leading-7 text-gray-600">
                            {{ tr('Revenez prochainement, ou contactez-nous pour être informé de l’ouverture des inscriptions.', 'Check back soon, or contact us to be notified when applications open.') }}
                        </p>
                        <Link href="/contact" class="button-secondary mt-6">
                            {{ tr('Nous contacter', 'Contact us') }}
                        </Link>
                    </div>
                </div>
            </section>

            <section
                v-if="page.slug === 'vie-etudiante' && studentLifePhotos.length"
                class="bg-white py-12 sm:py-14"
                aria-labelledby="student-photos-title"
            >
                <div class="mx-auto flex max-w-7xl flex-wrap items-end justify-between gap-4 px-6">
                    <h2 id="student-photos-title" class="text-2xl font-bold text-navy sm:text-3xl">{{ tr('La vie étudiante en images', 'Student life in pictures') }}</h2>
                    <div class="flex items-center gap-3">
                        <Link href="/galerie" class="inline-flex items-center gap-2 font-semibold text-edsp-green transition hover:text-green-700">
                            {{ tr('Voir toute la galerie', 'View the full gallery') }}
                            <ArrowRight :size="17" aria-hidden="true" />
                        </Link>
                        <div v-if="studentLifePhotos.length > 1" class="flex gap-2">
                            <button
                                type="button"
                                class="grid size-10 place-items-center rounded-full border border-slate-200 bg-white text-navy shadow-sm transition hover:border-edsp-green hover:text-edsp-green"
                                :aria-label="tr('Photos précédentes', 'Previous photos')"
                                @click="pausePhotoAutoplay(); scrollPhotos(-1);"
                            >
                                <ChevronLeft :size="20" aria-hidden="true" />
                            </button>
                            <button
                                type="button"
                                class="grid size-10 place-items-center rounded-full border border-slate-200 bg-white text-navy shadow-sm transition hover:border-edsp-green hover:text-edsp-green"
                                :aria-label="tr('Photos suivantes', 'Next photos')"
                                @click="pausePhotoAutoplay(); scrollPhotos(1);"
                            >
                                <ChevronRight :size="20" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
                <div
                    ref="photoCarousel"
                    class="mx-auto mt-7 flex max-w-7xl snap-x snap-mandatory gap-4 overflow-x-auto px-6 pb-3 sm:gap-5"
                    @mouseenter="pausePhotoAutoplay"
                    @mouseleave="startPhotoAutoplay"
                    @focusin="pausePhotoAutoplay"
                    @focusout="startPhotoAutoplay"
                    @touchstart.passive="pausePhotoAutoplay"
                >
                    <Link
                        v-for="image in studentLifePhotos"
                        :key="image.id"
                        href="/galerie"
                        class="group relative h-64 w-[86%] flex-none snap-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 focus:outline-none focus-visible:ring-4 focus-visible:ring-edsp-green/50 sm:h-80 sm:w-[62%] lg:w-[44%]"
                        :aria-label="tr('Ouvrir la galerie', 'Open the gallery')"
                    >
                        <span class="absolute inset-0 transition duration-500 ease-out group-hover:scale-[1.03]">
                            <MediaPlaceholder
                                :image-url="mediaThumbnailUrl(image.media)"
                                :alt="image.alt_text || image.media?.alt_text || tr('Vie étudiante à l’EDSP', 'Student life at EDSP')"
                                :label="tr('Vie étudiante à l’EDSP', 'Student life at EDSP')"
                            />
                        </span>
                        <span v-if="image.title || image.caption" class="pointer-events-none absolute inset-x-0 bottom-0 bg-navy/90 p-4 text-white">
                            <span class="block truncate font-heading text-sm font-bold">{{ image.title || image.caption }}</span>
                        </span>
                    </Link>
                </div>
            </section>

            <section v-if="page.slug === 'vie-etudiante' && news.length" class="bg-soft px-6 py-14 sm:py-16" aria-labelledby="student-news-title">
                <div class="mx-auto max-w-7xl">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <h2 id="student-news-title" class="text-2xl font-bold text-navy sm:text-3xl">{{ tr('Actualités et événements', 'News and events') }}</h2>
                        <Link href="/actualites" class="inline-flex items-center gap-2 font-semibold text-edsp-green transition hover:text-green-700">
                            {{ tr('Toutes les actualités', 'All news') }}
                            <ArrowRight :size="17" aria-hidden="true" />
                        </Link>
                    </div>
                    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <NewsCard v-for="article in news" :key="article.id" :article="article" />
                    </div>
                </div>
            </section>

            <section
                v-if="page.slug === 'contact'"
                class="border-t border-gray-200 bg-soft"
                aria-labelledby="contact-form-title"
            >
                <div class="mx-auto grid max-w-7xl gap-10 px-6 py-14 sm:py-16 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.35fr)] lg:gap-14">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-edsp-green">
                            {{ tr('Écrivez-nous', 'Write to us') }}
                        </p>
                        <h2 id="contact-form-title" class="mt-2 text-2xl font-bold text-navy sm:text-3xl">
                            {{ tr('Envoyer un message', 'Send us a message') }}
                        </h2>
                        <p class="mt-4 leading-7 text-gray-600">
                            {{ tr('Remplissez ce formulaire pour transmettre votre demande à l’établissement, ou contactez-nous directement via les coordonnées ci-dessous.', 'Complete this form to send your enquiry to the School, or reach us directly using the details below.') }}
                        </p>

                        <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                            <li class="flex items-start gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-navy text-gold">
                                    <MapPin :size="20" aria-hidden="true" />
                                </span>
                                <span>
                                    <span class="block font-heading text-sm font-bold text-navy">{{ tr('Adresse', 'Address') }}</span>
                                    <span class="mt-1 block leading-6 text-gray-600">{{ contactDetails.address }}</span>
                                </span>
                            </li>
                            <li class="flex items-start gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-navy text-gold">
                                    <Phone :size="20" aria-hidden="true" />
                                </span>
                                <span>
                                    <span class="block font-heading text-sm font-bold text-navy">{{ tr('Téléphone', 'Phone') }}</span>
                                    <a
                                        v-for="phone in contactDetails.phones"
                                        :key="phone"
                                        :href="`tel:${phone.replace(/\s+/g, '')}`"
                                        class="mt-1 block leading-6 text-gray-600 transition hover:text-edsp-green"
                                    >{{ phone }}</a>
                                </span>
                            </li>
                            <li class="flex items-start gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-navy text-gold">
                                    <Mail :size="20" aria-hidden="true" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block font-heading text-sm font-bold text-navy">{{ tr('Email', 'Email') }}</span>
                                    <a
                                        :href="`mailto:${contactDetails.email}`"
                                        class="mt-1 block truncate leading-6 text-gray-600 transition hover:text-edsp-green"
                                    >{{ contactDetails.email }}</a>
                                </span>
                            </li>
                            <li class="flex items-start gap-4 rounded-xl border border-edsp-green/25 bg-edsp-green/5 p-5">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-edsp-green text-white">
                                    <Clock :size="20" aria-hidden="true" />
                                </span>
                                <span>
                                    <span class="block font-heading text-sm font-bold text-navy">{{ tr('Délai de réponse', 'Response time') }}</span>
                                    <span class="mt-1 block leading-6 text-gray-600">
                                        {{ tr('Nous répondons généralement sous 48 h ouvrées.', 'We usually reply within 48 working hours.') }}
                                    </span>
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                        <form class="grid gap-x-6 gap-y-5 sm:grid-cols-2" novalidate @submit.prevent="submitContact">
                            <div>
                                <label for="contact-first-name" class="block text-sm font-semibold text-navy">{{ tr('Prénom', 'First name') }}</label>
                                <input
                                    id="contact-first-name"
                                    v-model="contactForm.first_name"
                                    type="text"
                                    name="first_name"
                                    autocomplete="given-name"
                                    maxlength="100"
                                    :aria-invalid="Boolean(contactForm.errors.first_name)"
                                    :aria-describedby="contactForm.errors.first_name ? 'contact-first-name-error' : undefined"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                >
                                <p v-if="contactForm.errors.first_name" id="contact-first-name-error" class="mt-1.5 text-sm text-red-700">
                                    {{ contactForm.errors.first_name }}
                                </p>
                            </div>

                            <div>
                                <label for="contact-last-name" class="block text-sm font-semibold text-navy">{{ tr('Nom', 'Last name') }} <span aria-hidden="true">*</span></label>
                                <input
                                    id="contact-last-name"
                                    v-model="contactForm.last_name"
                                    type="text"
                                    name="last_name"
                                    autocomplete="family-name"
                                    required
                                    maxlength="100"
                                    :aria-invalid="Boolean(contactForm.errors.last_name)"
                                    :aria-describedby="contactForm.errors.last_name ? 'contact-last-name-error' : undefined"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                >
                                <p v-if="contactForm.errors.last_name" id="contact-last-name-error" class="mt-1.5 text-sm text-red-700">
                                    {{ contactForm.errors.last_name }}
                                </p>
                            </div>

                            <div>
                                <label for="contact-email" class="block text-sm font-semibold text-navy">{{ tr('Adresse email', 'Email address') }} <span aria-hidden="true">*</span></label>
                                <input
                                    id="contact-email"
                                    v-model="contactForm.email"
                                    type="email"
                                    name="email"
                                    autocomplete="email"
                                    required
                                    maxlength="255"
                                    :aria-invalid="Boolean(contactForm.errors.email)"
                                    :aria-describedby="contactForm.errors.email ? 'contact-email-error' : undefined"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                >
                                <p v-if="contactForm.errors.email" id="contact-email-error" class="mt-1.5 text-sm text-red-700">
                                    {{ contactForm.errors.email }}
                                </p>
                            </div>

                            <div>
                                <label for="contact-phone" class="block text-sm font-semibold text-navy">{{ tr('Téléphone', 'Phone') }}</label>
                                <input
                                    id="contact-phone"
                                    v-model="contactForm.phone"
                                    type="tel"
                                    name="phone"
                                    autocomplete="tel"
                                    inputmode="tel"
                                    maxlength="40"
                                    :aria-invalid="Boolean(contactForm.errors.phone)"
                                    :aria-describedby="contactForm.errors.phone ? 'contact-phone-error' : undefined"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                >
                                <p v-if="contactForm.errors.phone" id="contact-phone-error" class="mt-1.5 text-sm text-red-700">
                                    {{ contactForm.errors.phone }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="contact-organization" class="block text-sm font-semibold text-navy">{{ tr('Organisation', 'Organisation') }}</label>
                                <input
                                    id="contact-organization"
                                    v-model="contactForm.organization"
                                    type="text"
                                    name="organization"
                                    autocomplete="organization"
                                    maxlength="180"
                                    :aria-invalid="Boolean(contactForm.errors.organization)"
                                    :aria-describedby="contactForm.errors.organization ? 'contact-organization-error' : undefined"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                >
                                <p v-if="contactForm.errors.organization" id="contact-organization-error" class="mt-1.5 text-sm text-red-700">
                                    {{ contactForm.errors.organization }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="contact-subject" class="block text-sm font-semibold text-navy">{{ tr('Objet', 'Subject') }} <span aria-hidden="true">*</span></label>
                                <input
                                    id="contact-subject"
                                    v-model="contactForm.subject"
                                    type="text"
                                    name="subject"
                                    required
                                    maxlength="180"
                                    :aria-invalid="Boolean(contactForm.errors.subject)"
                                    :aria-describedby="contactForm.errors.subject ? 'contact-subject-error' : undefined"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                >
                                <p v-if="contactForm.errors.subject" id="contact-subject-error" class="mt-1.5 text-sm text-red-700">
                                    {{ contactForm.errors.subject }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="contact-message" class="block text-sm font-semibold text-navy">{{ tr('Message', 'Message') }} <span aria-hidden="true">*</span></label>
                                <textarea
                                    id="contact-message"
                                    v-model="contactForm.message"
                                    name="message"
                                    rows="7"
                                    required
                                    minlength="10"
                                    maxlength="5000"
                                    :aria-invalid="Boolean(contactForm.errors.message)"
                                    :aria-describedby="contactForm.errors.message ? 'contact-message-error' : 'contact-message-help'"
                                    class="mt-2 w-full resize-y rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                />
                                <p id="contact-message-help" class="mt-1.5 text-sm text-gray-500">{{ tr('10 à 5 000 caractères.', '10 to 5,000 characters.') }}</p>
                                <p v-if="contactForm.errors.message" id="contact-message-error" class="mt-1.5 text-sm text-red-700">
                                    {{ contactForm.errors.message }}
                                </p>
                            </div>

                            <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                                <label for="contact-website">{{ tr('Site web', 'Website') }}</label>
                                <input id="contact-website" v-model="contactForm.website" type="text" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-soft p-4">
                                    <input
                                        v-model="contactForm.consent"
                                        type="checkbox"
                                        name="consent"
                                        required
                                        :aria-invalid="Boolean(contactForm.errors.consent)"
                                        :aria-describedby="contactForm.errors.consent ? 'contact-consent-error' : undefined"
                                        class="mt-1 h-4 w-4 shrink-0 accent-edsp-green"
                                    >
                                    <span class="text-sm leading-6 text-gray-700">
                                        {{ tr('J’accepte le traitement de mes données afin que l’EDSP puisse répondre à ma demande et j’ai lu la', 'I agree to the processing of my data so that EDSP can respond to my enquiry, and I have read the') }}
                                        <Link href="/politique-de-confidentialite" class="font-semibold text-institutional underline underline-offset-2">
                                            {{ tr('politique de confidentialité', 'privacy policy') }}
                                        </Link>.
                                        <span aria-hidden="true">*</span>
                                    </span>
                                </label>
                                <p v-if="contactForm.errors.consent" id="contact-consent-error" class="mt-1.5 text-sm text-red-700">
                                    {{ contactForm.errors.consent }}
                                </p>
                            </div>

                            <div
                                v-if="Object.keys(contactForm.errors).length"
                                class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 sm:col-span-2"
                                role="alert"
                            >
                                <AlertCircle :size="20" class="mt-0.5 shrink-0" aria-hidden="true" />
                                <p>{{ tr('Certains champs doivent être corrigés avant l’envoi du formulaire.', 'Please correct the highlighted fields before submitting the form.') }}</p>
                            </div>

                            <div class="sm:col-span-2">
                                <button
                                    type="submit"
                                    :disabled="contactForm.processing"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-edsp-green px-6 py-4 font-heading text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-wait disabled:opacity-60 sm:w-auto"
                                >
                                    <Send :size="18" aria-hidden="true" />
                                    {{ contactForm.processing ? tr('Envoi en cours…', 'Sending…') : tr('Envoyer le message', 'Send message') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <div v-else-if="!visibleSections.length && !(page.slug === 'vie-etudiante' && (news.length || studentLifePhotos.length))" class="mx-auto max-w-3xl px-6 py-20 text-center sm:py-24">
                <div class="mx-auto h-1 w-14 rounded-full bg-gold" aria-hidden="true" />
                <h2 class="mt-6 text-2xl font-bold text-navy">{{ tr('Contenu en cours de publication', 'Content coming soon') }}</h2>
                <p class="mx-auto mt-3 max-w-xl leading-7 text-gray-600">
                    {{ tr('Les informations de cette page seront disponibles prochainement.', 'Information for this page will be available soon.') }}
                </p>
                <Link
                    href="/"
                    class="mt-7 inline-flex items-center gap-2 font-semibold text-edsp-green transition hover:text-green-700"
                >
                    {{ tr('Retour à l’accueil', 'Back to home') }}
                    <ArrowRight :size="17" aria-hidden="true" />
                </Link>
            </div>
        </div>
    </PublicLayout>

    <EditModeToggle v-if="canEdit" :active="editing" @toggle="editing = !editing" />
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowRight,
    ChevronRight,
    Download,
    Images,
    Mail,
    Send,
    Users,
} from 'lucide-vue-next';
import PublicLayout from '../layouts/PublicLayout.vue';
import SeoHead from '../components/public/SeoHead.vue';
import RichText from '../components/public/RichText.vue';
import EditableSection from '../components/public/EditableSection.vue';
import EditModeToggle from '../components/public/EditModeToggle.vue';
import MediaPlaceholder from '../components/public/MediaPlaceholder.vue';
import SmartLink from '../components/public/SmartLink.vue';
import TeamMemberCard from '../components/public/TeamMemberCard.vue';
import type { AdmissionCampaign, MediaAsset, Page, Section, SeoData, TeamMember } from '../types';
import { mediaUrl, safePublicUrl } from '../lib/public-content';

type CmsPage = Page & {
    canonical_url?: string | null;
    robots_follow?: boolean;
    robots_index?: boolean;
};

type Gallery = {
    id: number;
    title: string;
    description?: string | null;
    images?: Array<{ id: number; caption?: string | null; media?: MediaAsset | null }>;
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
    galleries?: Gallery[];
    page: CmsPage;
    partners?: Partner[];
    seo?: SeoData;
    teamMembers?: TeamMember[];
}>(), {
    campaign: null,
    canEdit: false,
    documents: () => [],
    galleries: () => [],
    partners: () => [],
    seo: () => ({}),
    teamMembers: () => [],
});
const editing = ref(false);

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
        .sort((left, right) => (left.position ?? 0) - (right.position ?? 0)),
);

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

const documentUrl = (document: PublicDocument): string | null => safePublicUrl(document.download_url);

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

    <PublicLayout>
        <header class="relative isolate overflow-hidden bg-soft">
            <div
                class="absolute inset-y-0 right-0 -z-10 hidden w-1/3 bg-navy lg:block"
                aria-hidden="true"
            />
            <div
                class="absolute -left-28 -top-36 -z-10 h-80 w-80 rounded-full bg-edsp-green/10 blur-3xl"
                aria-hidden="true"
            />

            <div class="mx-auto max-w-7xl px-6 py-14 sm:py-16 lg:py-20">
                <nav aria-label="Fil d’Ariane" class="mb-8">
                    <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <li>
                            <Link href="/" class="transition hover:text-edsp-green">Accueil</Link>
                        </li>
                        <li aria-hidden="true"><ChevronRight :size="15" /></li>
                        <li class="font-semibold text-navy" aria-current="page">{{ page.title }}</li>
                    </ol>
                </nav>

                <div class="max-w-3xl">
                    <p class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-edsp-green">
                        École de Droit et Science Politique
                    </p>
                    <h1 class="text-3xl font-extrabold leading-tight text-navy sm:text-4xl lg:text-5xl">
                        {{ page.title }}
                    </h1>
                    <p v-if="page.meta_description" class="mt-5 max-w-2xl text-base leading-7 text-gray-600 sm:text-lg">
                        {{ page.meta_description }}
                    </p>
                </div>
            </div>
        </header>

        <div class="bg-white">
            <div v-if="visibleSections.length">
                <EditableSection
                    v-for="section in visibleSections"
                    :key="section.id"
                    :section="section"
                    :editing="editing"
                >
                    <section
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

                            <div v-if="mediaUrl(section)" class="h-72 overflow-hidden rounded-xl shadow-lg sm:h-88">
                                <MediaPlaceholder
                                    :image-url="mediaUrl(section)"
                                    :alt="section.image?.alt_text || section.title"
                                    :label="section.title || 'Illustration de la section'"
                                />
                            </div>
                        </div>
                    </section>
                </EditableSection>
            </div>

            <section v-if="page.slug === 'equipe' && teamMembers.length" class="bg-soft px-6 py-16 sm:py-20" aria-labelledby="team-list-title">
                <div class="mx-auto max-w-7xl">
                    <div class="flex items-center gap-3">
                        <Users :size="25" class="text-edsp-green" aria-hidden="true" />
                        <h2 id="team-list-title" class="text-2xl font-bold text-navy sm:text-3xl">Membres de l’équipe</h2>
                    </div>
                    <div class="mt-9 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <TeamMemberCard v-for="member in teamMembers" :key="member.id" :member="member" />
                    </div>
                </div>
            </section>

            <section v-if="page.slug === 'galerie' && galleries.length" class="bg-soft px-6 py-16 sm:py-20" aria-labelledby="gallery-list-title">
                <div class="mx-auto max-w-7xl">
                    <div class="flex items-center gap-3">
                        <Images :size="25" class="text-edsp-green" aria-hidden="true" />
                        <h2 id="gallery-list-title" class="text-2xl font-bold text-navy sm:text-3xl">Galeries publiées</h2>
                    </div>
                    <article v-for="gallery in galleries" :key="gallery.id" class="mt-10">
                        <h3 class="text-xl font-bold text-navy">{{ gallery.title }}</h3>
                        <p v-if="gallery.description" class="mt-2 text-gray-600">{{ gallery.description }}</p>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <figure v-for="image in gallery.images" :key="image.id" class="overflow-hidden rounded-xl bg-white shadow-sm">
                                <div class="h-64">
                                    <MediaPlaceholder
                                        :image-url="mediaUrl(image.media)"
                                        :alt="image.media?.alt_text || image.caption || gallery.title"
                                        :label="image.caption || gallery.title"
                                    />
                                </div>
                                <figcaption v-if="image.caption" class="px-4 py-3 text-sm text-gray-600">{{ image.caption }}</figcaption>
                            </figure>
                        </div>
                    </article>
                </div>
            </section>

            <section v-if="page.slug === 'partenaires' && partners.length" class="bg-soft px-6 py-16 sm:py-20" aria-labelledby="partners-list-title">
                <div class="mx-auto max-w-7xl">
                    <h2 id="partners-list-title" class="text-2xl font-bold text-navy sm:text-3xl">Partenaires</h2>
                    <div class="mt-9 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <article v-for="partner in partners" :key="partner.id" class="rounded-xl bg-white p-6 shadow-sm">
                            <div class="h-28 overflow-hidden rounded-lg">
                                <MediaPlaceholder
                                    :image-url="mediaUrl(partner.logo)"
                                    :alt="partner.logo?.alt_text || `Logo ${partner.name}`"
                                    :label="`Logo ${partner.name}`"
                                />
                            </div>
                            <h3 class="mt-5 text-lg font-bold text-navy">{{ partner.name }}</h3>
                            <p v-if="partner.description" class="mt-2 text-sm leading-6 text-gray-600">{{ partner.description }}</p>
                            <SmartLink v-if="partner.url" :href="partner.url" class="mt-4 inline-flex font-semibold text-institutional hover:underline">
                                Visiter le site
                            </SmartLink>
                        </article>
                    </div>
                </div>
            </section>

            <section v-if="page.slug === 'bibliotheque' && documents.length" class="bg-soft px-6 py-16 sm:py-20" aria-labelledby="documents-list-title">
                <div class="mx-auto max-w-5xl">
                    <h2 id="documents-list-title" class="text-2xl font-bold text-navy sm:text-3xl">Ressources disponibles</h2>
                    <ul class="mt-8 divide-y divide-gray-200 rounded-xl bg-white px-6 shadow-sm">
                        <li v-for="document in documents" :key="document.id" class="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p v-if="document.category" class="text-xs font-bold uppercase tracking-wide text-edsp-green">{{ document.category }}</p>
                                <h3 class="mt-1 font-bold text-navy">{{ document.title }}</h3>
                                <p v-if="document.description" class="mt-1 text-sm text-gray-600">{{ document.description }}</p>
                            </div>
                            <SmartLink v-if="documentUrl(document)" :href="documentUrl(document)!" class="button-secondary flex-none">
                                <Download :size="17" aria-hidden="true" /> Télécharger
                            </SmartLink>
                        </li>
                    </ul>
                </div>
            </section>

            <section v-if="page.slug === 'admissions'" class="bg-soft px-6 py-16 sm:py-20" aria-labelledby="campaign-information-title">
                <div class="mx-auto max-w-5xl rounded-xl bg-white p-7 shadow-sm sm:p-9">
                    <h2 id="campaign-information-title" class="text-2xl font-bold text-navy">Campagne d’admission</h2>
                    <template v-if="campaign">
                        <p class="mt-4 text-lg font-semibold text-edsp-green">{{ campaign.title }}</p>
                        <RichText v-if="campaign.instructions" :html="campaign.instructions" class="mt-4 text-gray-600" />
                        <ul v-if="campaign.required_documents?.length" class="mt-5 list-disc space-y-1 pl-5 text-gray-600">
                            <li v-for="document in campaign.required_documents" :key="document.key">{{ document.label }}</li>
                        </ul>
                        <Link href="/preinscription" class="button-primary mt-7">Commencer la préinscription</Link>
                    </template>
                    <p v-else class="mt-4 text-gray-600">Aucune campagne n’est ouverte actuellement.</p>
                </div>
            </section>

            <section
                v-if="page.slug === 'contact'"
                class="border-t border-gray-200 bg-soft"
                aria-labelledby="contact-form-title"
            >
                <div class="mx-auto grid max-w-7xl gap-10 px-6 py-16 sm:py-20 lg:grid-cols-[18rem_minmax(0,1fr)] lg:gap-16">
                    <div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-navy text-gold">
                            <Mail :size="23" aria-hidden="true" />
                        </div>
                        <p class="mt-6 text-xs font-bold uppercase tracking-[0.16em] text-edsp-green">
                            Écrivez-nous
                        </p>
                        <h2 id="contact-form-title" class="mt-2 text-2xl font-bold text-navy sm:text-3xl">
                            Envoyer un message
                        </h2>
                        <p class="mt-4 leading-7 text-gray-600">
                            Remplissez ce formulaire pour transmettre votre demande à l’établissement.
                        </p>
                    </div>

                    <div>
                        <form class="grid gap-x-6 gap-y-5 sm:grid-cols-2" novalidate @submit.prevent="submitContact">
                            <div>
                                <label for="contact-first-name" class="block text-sm font-semibold text-navy">Prénom</label>
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
                                <label for="contact-last-name" class="block text-sm font-semibold text-navy">Nom <span aria-hidden="true">*</span></label>
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
                                <label for="contact-email" class="block text-sm font-semibold text-navy">Adresse email <span aria-hidden="true">*</span></label>
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
                                <label for="contact-phone" class="block text-sm font-semibold text-navy">Téléphone</label>
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
                                <label for="contact-organization" class="block text-sm font-semibold text-navy">Organisation</label>
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
                                <label for="contact-subject" class="block text-sm font-semibold text-navy">Objet <span aria-hidden="true">*</span></label>
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
                                <label for="contact-message" class="block text-sm font-semibold text-navy">Message <span aria-hidden="true">*</span></label>
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
                                <p id="contact-message-help" class="mt-1.5 text-sm text-gray-500">10 à 5 000 caractères.</p>
                                <p v-if="contactForm.errors.message" id="contact-message-error" class="mt-1.5 text-sm text-red-700">
                                    {{ contactForm.errors.message }}
                                </p>
                            </div>

                            <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                                <label for="contact-website">Site web</label>
                                <input id="contact-website" v-model="contactForm.website" type="text" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-white p-4">
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
                                        J’accepte le traitement de mes données afin que l’EDSP puisse répondre à ma demande et j’ai lu la
                                        <Link href="/politique-de-confidentialite" class="font-semibold text-institutional underline underline-offset-2">
                                            politique de confidentialité
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
                                <p>Certains champs doivent être corrigés avant l’envoi du formulaire.</p>
                            </div>

                            <div class="sm:col-span-2">
                                <button
                                    type="submit"
                                    :disabled="contactForm.processing"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-edsp-green px-6 py-4 font-heading text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-wait disabled:opacity-60 sm:w-auto"
                                >
                                    <Send :size="18" aria-hidden="true" />
                                    {{ contactForm.processing ? 'Envoi en cours…' : 'Envoyer le message' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <div v-else-if="!visibleSections.length" class="mx-auto max-w-3xl px-6 py-20 text-center sm:py-24">
                <div class="mx-auto h-1 w-14 rounded-full bg-gold" aria-hidden="true" />
                <h2 class="mt-6 text-2xl font-bold text-navy">Contenu en cours de publication</h2>
                <p class="mx-auto mt-3 max-w-xl leading-7 text-gray-600">
                    Les informations de cette page seront disponibles prochainement.
                </p>
                <Link
                    href="/"
                    class="mt-7 inline-flex items-center gap-2 font-semibold text-edsp-green transition hover:text-green-700"
                >
                    Retour à l’accueil
                    <ArrowRight :size="17" aria-hidden="true" />
                </Link>
            </div>
        </div>
    </PublicLayout>

    <EditModeToggle v-if="canEdit" :active="editing" @toggle="editing = !editing" />
</template>

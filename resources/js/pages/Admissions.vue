<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    ArrowRight,
    CalendarClock,
    Check,
    ChevronRight,
    ClipboardList,
    FileText,
    GraduationCap,
    HeartHandshake,
    IdCard,
    ImageIcon,
    ExternalLink,
    PlayCircle,
    Send,
    Upload,
    X,
} from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import PublicLayout from '../layouts/PublicLayout.vue';
import RichText from '../components/public/RichText.vue';
import SeoHead from '../components/public/SeoHead.vue';
import { safePublicUrl } from '../lib/public-content';
import type { RequiredDocument, SeoData } from '../types';

type CampaignProgram = {
    id: number;
    level?: string | null;
    slug?: string | null;
    title: string;
};

type AdmissionCampaign = {
    academic_year: string;
    closes_at: string;
    id: number;
    instructions: string | null;
    opens_at: string;
    programs: CampaignProgram[];
    required_documents: RequiredDocument[] | null;
    title: string;
    tutorial_video_url: string | null;
};

type AcademicOption = {
    level_code: string;
    level_id: number;
    level_name: string;
    mention_code: string;
    mention_id: number;
    mention_name: string;
    parcours_id: number;
    parcours_name: string;
};

type DocumentPreview = {
    isImage: boolean;
    name: string;
    size: string;
    url: string | null;
};

const props = withDefaults(defineProps<{
    academicOptions?: AcademicOption[];
    campaign: AdmissionCampaign | null;
    seo?: SeoData;
}>(), {
    academicOptions: () => [],
    seo: () => ({}),
});

const form = useForm({
    admission_campaign_id: props.campaign?.id ?? 0,
    program_id: props.campaign?.programs[0]?.id ?? 0,
    civility: '',
    first_name: '',
    last_name: '',
    gender: '',
    email: '',
    phone: '',
    birth_date: '',
    birth_place: '',
    nationality: 'Malagasy',
    national_id: '',
    address: '',
    father_name: '',
    mother_name: '',
    parent_phone: '',
    guardian_name: '',
    guardian_relationship: '',
    guardian_phone: '',
    academic_level_id: 0,
    mention_id: 0,
    parcours_id: 0,
    last_diploma: '',
    graduation_year: 0,
    previous_institution: '',
    academic_background: '',
    documents: {} as Record<string, File | null>,
    privacy_accepted: false,
    website: '',
});

const steps = [
    { number: 1, short: 'Identité', title: 'État civil et coordonnées' },
    { number: 2, short: 'Famille', title: 'Parents et répondant' },
    { number: 3, short: 'Formation', title: 'Orientation pédagogique' },
    { number: 4, short: 'Validation', title: 'Pièces et confirmation' },
];
const currentStep = ref(1);
const highestStep = ref(1);
const documentPreviews = ref<Record<string, DocumentPreview>>({});
const currentYear = new Date().getFullYear();
const yesterday = new Date();
yesterday.setDate(yesterday.getDate() - 1);
const maxBirthDate = [
    yesterday.getFullYear(),
    String(yesterday.getMonth() + 1).padStart(2, '0'),
    String(yesterday.getDate()).padStart(2, '0'),
].join('-');

const levels = computed(() => Array.from(new Map(
    props.academicOptions.map((option) => [option.level_id, {
        id: option.level_id,
        code: option.level_code,
        name: option.level_name,
    }]),
).values()));

const mentions = computed(() => Array.from(new Map(
    props.academicOptions
        .filter((option) => option.level_id === form.academic_level_id)
        .map((option) => [option.mention_id, {
            id: option.mention_id,
            code: option.mention_code,
            name: option.mention_name,
        }]),
).values()));

const parcours = computed(() => Array.from(new Map(
    props.academicOptions
        .filter((option) => option.level_id === form.academic_level_id && option.mention_id === form.mention_id)
        .map((option) => [option.parcours_id, {
            id: option.parcours_id,
            name: option.parcours_name,
        }]),
).values()));

const selectedLevel = computed(() => levels.value.find((level) => level.id === form.academic_level_id));
const selectedMention = computed(() => mentions.value.find((mention) => mention.id === form.mention_id));
const selectedParcours = computed(() => parcours.value.find((item) => item.id === form.parcours_id));
const selectedProgram = computed(() => props.campaign?.programs.find((program) => program.id === form.program_id)?.title ?? 'Non déterminée');
const attachedDocuments = computed(() => Object.values(form.documents).filter((file) => file instanceof File).length);
const tutorialVideoUrl = computed(() => safePublicUrl(props.campaign?.tutorial_video_url));

watch(() => form.civility, (civility) => {
    if (civility === 'monsieur') {
        form.gender = 'masculin';
    } else if (civility === 'madame' || civility === 'mademoiselle') {
        form.gender = 'feminin';
    }
});

watch(() => form.academic_level_id, () => {
    form.mention_id = 0;
    form.parcours_id = 0;
});

watch(() => form.mention_id, (mentionId) => {
    form.parcours_id = 0;
    const mention = mentions.value.find((item) => item.id === mentionId);
    const expectedSlug = mention?.code === 'SCPO' ? 'science-politique' : 'droit-prive';
    form.program_id = props.campaign?.programs.find((program) => program.slug === expectedSlug)?.id
        ?? props.campaign?.programs[0]?.id
        ?? 0;
});

const formatDate = (date: string): string => {
    const parsed = new Date(date);

    return Number.isNaN(parsed.getTime())
        ? date
        : new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }).format(parsed);
};

const documentError = (key: string): string | undefined => (
    form.errors as Record<string, string | undefined>
)[`documents.${key}`];

const fieldError = (field: string): string | undefined => (
    form.errors as Record<string, string | undefined>
)[field];

const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) return `${bytes} octets`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} Ko`;

    return `${(bytes / (1024 * 1024)).toFixed(1)} Mo`;
};

const revokePreview = (key: string): void => {
    const url = documentPreviews.value[key]?.url;
    if (url) URL.revokeObjectURL(url);
};

const setDocument = (key: string, event: Event): void => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    revokePreview(key);
    form.clearErrors(`documents.${key}` as never);

    if (file && file.size > 5 * 1024 * 1024) {
        form.documents[key] = null;
        delete documentPreviews.value[key];
        input.value = '';
        form.setError(`documents.${key}` as never, 'Ce fichier dépasse la taille maximale autorisée de 5 Mo.');
        return;
    }

    form.documents[key] = file;

    if (!file) {
        delete documentPreviews.value[key];
        return;
    }

    const isImage = file.type.startsWith('image/');
    documentPreviews.value[key] = {
        isImage,
        name: file.name,
        size: formatFileSize(file.size),
        url: isImage ? URL.createObjectURL(file) : null,
    };
};

const removeDocument = (key: string): void => {
    revokePreview(key);
    delete documentPreviews.value[key];
    form.documents[key] = null;

    const input = document.getElementById(`document-${key}`) as HTMLInputElement | null;
    if (input) input.value = '';
};

const clearDocumentPreviews = (): void => {
    Object.keys(documentPreviews.value).forEach(revokePreview);
    documentPreviews.value = {};
};

onBeforeUnmount(clearDocumentPreviews);

const scrollToForm = (): void => {
    void nextTick(() => document.getElementById('application-form-title')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
};

const validateStep = (): boolean => {
    const step = document.getElementById(`application-step-${currentStep.value}`);

    if (!step) return true;

    const invalidField = Array.from(step.querySelectorAll<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>('input, select, textarea'))
        .find((field) => !field.checkValidity());

    if (invalidField) {
        invalidField.reportValidity();
        invalidField.focus();
        return false;
    }

    return true;
};

const nextStep = (): void => {
    if (!validateStep() || currentStep.value >= steps.length) return;

    currentStep.value += 1;
    highestStep.value = Math.max(highestStep.value, currentStep.value);
    scrollToForm();
};

const previousStep = (): void => {
    if (currentStep.value <= 1) return;
    currentStep.value -= 1;
    scrollToForm();
};

const openStep = (step: number): void => {
    if (step > highestStep.value) return;
    currentStep.value = step;
    scrollToForm();
};

const stepForError = (field: string): number => {
    if (['father_name', 'mother_name', 'parent_phone', 'guardian_name', 'guardian_relationship', 'guardian_phone'].includes(field)) return 2;
    if (['program_id', 'academic_level_id', 'mention_id', 'parcours_id', 'last_diploma', 'graduation_year', 'previous_institution', 'academic_background'].includes(field)) return 3;
    if (field === 'privacy_accepted' || field === 'documents') return 4;
    return 1;
};

const submit = (): void => {
    if (!validateStep()) return;

    form.post('/inscription', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            clearDocumentPreviews();
            form.reset();
            currentStep.value = 1;
            highestStep.value = 1;
        },
        onError: (errors) => {
            const firstField = Object.keys(errors)[0]?.split('.')[0] ?? '';
            currentStep.value = stepForError(firstField);
            highestStep.value = Math.max(highestStep.value, currentStep.value);
            scrollToForm();
        },
    });
};
</script>

<template>
    <SeoHead
        :title="seo?.title || 'Inscription | EDSP'"
        :description="seo?.description || 'Déposez votre dossier d’inscription à l’École de Droit et Science Politique.'"
        :canonical-url="seo?.canonical"
        :open-graph-title="seo?.og_title"
        :open-graph-description="seo?.og_description"
        :structured-data="seo?.schema"
        :no-index="!campaign"
    />

    <PublicLayout>
        <header class="relative isolate overflow-hidden border-b border-slate-200 bg-soft">
            <div class="absolute inset-y-0 right-0 -z-10 hidden w-[30%] bg-navy lg:block" aria-hidden="true" />
            <div class="absolute -left-24 -top-32 -z-10 size-80 rounded-full bg-edsp-green/10 blur-3xl" aria-hidden="true" />
            <div class="mx-auto max-w-7xl px-6 py-12 sm:py-14 lg:py-16">
                <nav aria-label="Fil d’Ariane" class="mb-7">
                    <ol class="flex items-center gap-2 text-sm text-slate-500">
                        <li><Link href="/" class="transition hover:text-edsp-green">Accueil</Link></li>
                        <li aria-hidden="true"><ChevronRight :size="15" /></li>
                        <li class="font-semibold text-navy" aria-current="page">Inscription</li>
                    </ol>
                </nav>
                <div class="max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-edsp-green">Rejoindre l’EDSP</p>
                    <h1 class="mt-2 text-3xl font-extrabold leading-tight text-navy sm:text-4xl">Votre dossier d’inscription</h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                        Renseignez votre situation, choisissez votre parcours et vérifiez votre dossier avant l’envoi.
                    </p>
                </div>
            </div>
        </header>

        <div class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16">
                <div v-if="!campaign" class="mx-auto max-w-3xl rounded-2xl border border-amber-200 bg-amber-50 px-6 py-10 text-center" role="status">
                    <CalendarClock :size="40" class="mx-auto text-[#8A6410]" aria-hidden="true" />
                    <h2 class="mt-5 text-2xl font-bold text-navy">Campagne actuellement fermée</h2>
                    <p class="mx-auto mt-3 max-w-xl leading-7 text-amber-950">Aucune campagne d’inscription n’est ouverte actuellement.</p>
                    <Link href="/actualites" class="button-dark mt-7">Consulter les actualités <ArrowRight :size="17" aria-hidden="true" /></Link>
                </div>

                <div v-else class="grid items-start gap-8 xl:grid-cols-[19rem_minmax(0,1fr)] xl:gap-12">
                    <aside class="rounded-2xl bg-navy p-6 text-white xl:sticky xl:top-28" aria-labelledby="campaign-title">
                        <span class="grid size-11 place-items-center rounded-xl bg-white/10 text-gold"><ClipboardList :size="23" aria-hidden="true" /></span>
                        <p class="mt-5 text-xs font-bold uppercase tracking-[0.15em] text-gold">Campagne ouverte</p>
                        <h2 id="campaign-title" class="mt-2 text-xl font-bold leading-snug">{{ campaign.title }}</h2>
                        <dl class="mt-5 divide-y divide-white/15 text-sm">
                            <div class="py-3"><dt class="text-blue-200">Année universitaire</dt><dd class="mt-1 font-semibold">{{ campaign.academic_year }}</dd></div>
                            <div class="py-3"><dt class="text-blue-200">Clôture</dt><dd class="mt-1 font-semibold">{{ formatDate(campaign.closes_at) }}</dd></div>
                        </dl>
                        <RichText
                            v-if="campaign.instructions"
                            :html="campaign.instructions"
                            class="mt-4 border-t border-white/15 pt-4 text-sm leading-6 text-blue-100 [&_a]:font-semibold [&_a]:text-white [&_a]:underline [&_li]:my-1 [&_p]:my-0 [&_ul]:mt-2"
                        />
                        <a
                            v-if="tutorialVideoUrl"
                            :href="tutorialVideoUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-5 flex items-center gap-3 rounded-xl border border-white/20 bg-white/10 p-3.5 text-left transition hover:border-gold/60 hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold"
                            aria-label="Voir le tutoriel vidéo dans un nouvel onglet"
                        >
                            <span class="grid size-10 flex-none place-items-center rounded-lg bg-gold text-navy">
                                <PlayCircle :size="22" aria-hidden="true" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-white">Voir le tutoriel vidéo</span>
                                <span class="mt-0.5 block text-xs leading-5 text-blue-200">Comment remplir votre dossier</span>
                            </span>
                            <ExternalLink :size="17" class="flex-none text-blue-200" aria-hidden="true" />
                        </a>
                    </aside>

                    <section class="min-w-0" aria-labelledby="application-form-title">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-edsp-green">Dossier candidat</p>
                            <h2 id="application-form-title" class="mt-2 scroll-mt-28 text-2xl font-bold text-navy sm:text-3xl">Formulaire d’inscription</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Les champs avec un astérisque sont obligatoires. Vos données sont enregistrées uniquement après l’envoi final.</p>
                        </div>

                        <ol class="mt-7 grid grid-cols-4 overflow-hidden rounded-xl border border-slate-200 bg-soft" aria-label="Étapes de l’inscription">
                            <li v-for="step in steps" :key="step.number" class="relative min-w-0">
                                <button
                                    type="button"
                                    class="flex w-full flex-col items-center gap-1 px-1 py-3 text-center transition sm:px-3"
                                    :class="currentStep === step.number ? 'bg-white text-navy shadow-sm' : step.number <= highestStep ? 'text-edsp-green hover:bg-white/70' : 'cursor-not-allowed text-slate-400'"
                                    :disabled="step.number > highestStep"
                                    :aria-current="currentStep === step.number ? 'step' : undefined"
                                    @click="openStep(step.number)"
                                >
                                    <span
                                        class="grid size-7 place-items-center rounded-full text-xs font-bold"
                                        :class="currentStep === step.number ? 'bg-navy text-white' : step.number < currentStep ? 'bg-edsp-green text-white' : 'border border-current bg-white'"
                                    >
                                        <Check v-if="step.number < currentStep" :size="14" aria-hidden="true" />
                                        <template v-else>{{ step.number }}</template>
                                    </span>
                                    <span class="truncate text-[10px] font-bold sm:text-xs">{{ step.short }}</span>
                                </button>
                            </li>
                        </ol>

                        <form class="mt-6" novalidate @submit.prevent="submit">
                            <input v-model="form.admission_campaign_id" type="hidden" name="admission_campaign_id">
                            <input v-model="form.program_id" type="hidden" name="program_id">

                            <fieldset v-show="currentStep === 1" id="application-step-1" class="grid gap-5 rounded-2xl border border-slate-200 p-5 sm:grid-cols-2 sm:p-7">
                                <legend class="px-2"><span class="inline-flex items-center gap-2 font-heading text-lg font-bold text-navy"><IdCard :size="21" class="text-edsp-green" aria-hidden="true" /> État civil et coordonnées</span></legend>
                                <p class="-mt-1 text-sm text-slate-500 sm:col-span-2">Renseignez les informations telles qu’elles figurent sur vos pièces officielles.</p>

                                <fieldset>
                                    <legend class="text-sm font-semibold text-navy">Civilité *</legend>
                                    <div class="mt-2 grid grid-cols-3 gap-2">
                                        <label
                                            v-for="option in [{ value: 'monsieur', label: 'M.' }, { value: 'madame', label: 'Mme' }, { value: 'mademoiselle', label: 'Mlle' }]"
                                            :key="option.value"
                                            class="flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-lg border px-2 py-2.5 text-sm font-semibold transition focus-within:ring-2 focus-within:ring-edsp-green/30"
                                            :class="form.civility === option.value ? 'border-edsp-green bg-edsp-green/5 text-edsp-green shadow-sm' : 'border-slate-300 bg-white text-slate-700 hover:border-edsp-green/60'"
                                        >
                                            <input v-model="form.civility" type="radio" name="civility" :value="option.value" required class="size-4 accent-edsp-green" :aria-invalid="Boolean(fieldError('civility'))">
                                            <span>{{ option.label }}</span>
                                        </label>
                                    </div>
                                    <p v-if="fieldError('civility')" class="mt-1.5 text-sm text-red-700">{{ fieldError('civility') }}</p>
                                </fieldset>
                                <fieldset>
                                    <legend class="text-sm font-semibold text-navy">Genre *</legend>
                                    <div class="mt-2 grid grid-cols-2 gap-2">
                                        <label
                                            v-for="option in [{ value: 'masculin', label: 'Masculin' }, { value: 'feminin', label: 'Féminin' }]"
                                            :key="option.value"
                                            class="flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-lg border px-3 py-2.5 text-sm font-semibold transition focus-within:ring-2 focus-within:ring-edsp-green/30"
                                            :class="form.gender === option.value ? 'border-edsp-green bg-edsp-green/5 text-edsp-green shadow-sm' : 'border-slate-300 bg-white text-slate-700 hover:border-edsp-green/60'"
                                        >
                                            <input v-model="form.gender" type="radio" name="gender" :value="option.value" required class="size-4 accent-edsp-green" :aria-invalid="Boolean(fieldError('gender'))">
                                            <span>{{ option.label }}</span>
                                        </label>
                                    </div>
                                    <p v-if="fieldError('gender')" class="mt-1.5 text-sm text-red-700">{{ fieldError('gender') }}</p>
                                </fieldset>
                                <div>
                                    <label for="first-name" class="text-sm font-semibold text-navy">Prénom(s) *</label>
                                    <input id="first-name" v-model="form.first_name" name="first_name" type="text" autocomplete="given-name" required maxlength="100" class="form-control mt-2" :aria-invalid="Boolean(fieldError('first_name'))">
                                    <p v-if="fieldError('first_name')" class="mt-1.5 text-sm text-red-700">{{ fieldError('first_name') }}</p>
                                </div>
                                <div>
                                    <label for="last-name" class="text-sm font-semibold text-navy">Nom *</label>
                                    <input id="last-name" v-model="form.last_name" name="last_name" type="text" autocomplete="family-name" required maxlength="100" class="form-control mt-2" :aria-invalid="Boolean(fieldError('last_name'))">
                                    <p v-if="fieldError('last_name')" class="mt-1.5 text-sm text-red-700">{{ fieldError('last_name') }}</p>
                                </div>
                                <div>
                                    <label for="birth-date" class="text-sm font-semibold text-navy">Date de naissance *</label>
                                    <input id="birth-date" v-model="form.birth_date" name="birth_date" type="date" autocomplete="bday" required :max="maxBirthDate" class="form-control mt-2" :aria-invalid="Boolean(fieldError('birth_date'))">
                                    <p v-if="fieldError('birth_date')" class="mt-1.5 text-sm text-red-700">{{ fieldError('birth_date') }}</p>
                                </div>
                                <div>
                                    <label for="birth-place" class="text-sm font-semibold text-navy">Lieu de naissance *</label>
                                    <input id="birth-place" v-model="form.birth_place" name="birth_place" type="text" required maxlength="255" class="form-control mt-2" :aria-invalid="Boolean(fieldError('birth_place'))">
                                    <p v-if="fieldError('birth_place')" class="mt-1.5 text-sm text-red-700">{{ fieldError('birth_place') }}</p>
                                </div>
                                <div>
                                    <label for="nationality" class="text-sm font-semibold text-navy">Nationalité *</label>
                                    <input id="nationality" v-model="form.nationality" name="nationality" type="text" autocomplete="country-name" required maxlength="100" class="form-control mt-2" :aria-invalid="Boolean(fieldError('nationality'))">
                                    <p v-if="fieldError('nationality')" class="mt-1.5 text-sm text-red-700">{{ fieldError('nationality') }}</p>
                                </div>
                                <div>
                                    <label for="national-id" class="text-sm font-semibold text-navy">CIN ou passeport <span class="font-normal text-slate-500">(facultatif)</span></label>
                                    <input id="national-id" v-model="form.national_id" name="national_id" type="text" maxlength="80" class="form-control mt-2" :aria-invalid="Boolean(fieldError('national_id'))">
                                    <p v-if="fieldError('national_id')" class="mt-1.5 text-sm text-red-700">{{ fieldError('national_id') }}</p>
                                </div>
                                <div>
                                    <label for="email" class="text-sm font-semibold text-navy">Adresse e-mail *</label>
                                    <input id="email" v-model="form.email" name="email" type="email" inputmode="email" autocomplete="email" required maxlength="255" class="form-control mt-2" :aria-invalid="Boolean(fieldError('email'))">
                                    <p v-if="fieldError('email')" class="mt-1.5 text-sm text-red-700">{{ fieldError('email') }}</p>
                                </div>
                                <div>
                                    <label for="phone" class="text-sm font-semibold text-navy">Téléphone du candidat *</label>
                                    <input id="phone" v-model="form.phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required maxlength="40" class="form-control mt-2" :aria-invalid="Boolean(fieldError('phone'))">
                                    <p v-if="fieldError('phone')" class="mt-1.5 text-sm text-red-700">{{ fieldError('phone') }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="address" class="text-sm font-semibold text-navy">Adresse complète *</label>
                                    <textarea id="address" v-model="form.address" name="address" rows="3" autocomplete="street-address" required maxlength="500" class="form-control mt-2 resize-y" :aria-invalid="Boolean(fieldError('address'))" />
                                    <p v-if="fieldError('address')" class="mt-1.5 text-sm text-red-700">{{ fieldError('address') }}</p>
                                </div>
                            </fieldset>

                            <fieldset v-show="currentStep === 2" id="application-step-2" class="grid gap-5 rounded-2xl border border-slate-200 p-5 sm:grid-cols-2 sm:p-7">
                                <legend class="px-2"><span class="inline-flex items-center gap-2 font-heading text-lg font-bold text-navy"><HeartHandshake :size="21" class="text-edsp-green" aria-hidden="true" /> Parents et répondant</span></legend>
                                <div class="-mt-1 rounded-lg bg-blue-50 px-4 py-3 text-sm leading-6 text-blue-950 sm:col-span-2">Indiquez au minimum un numéro joignable : téléphone des parents ou téléphone du répondant.</div>
                                <div>
                                    <label for="father-name" class="text-sm font-semibold text-navy">Nom complet du père</label>
                                    <input id="father-name" v-model="form.father_name" name="father_name" type="text" maxlength="255" class="form-control mt-2" :aria-invalid="Boolean(fieldError('father_name'))">
                                </div>
                                <div>
                                    <label for="mother-name" class="text-sm font-semibold text-navy">Nom complet de la mère</label>
                                    <input id="mother-name" v-model="form.mother_name" name="mother_name" type="text" maxlength="255" class="form-control mt-2" :aria-invalid="Boolean(fieldError('mother_name'))">
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="parent-phone" class="text-sm font-semibold text-navy">Téléphone des parents <span v-if="!form.guardian_phone">*</span></label>
                                    <input id="parent-phone" v-model="form.parent_phone" name="parent_phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="40" :required="!form.guardian_phone" class="form-control mt-2" :aria-invalid="Boolean(fieldError('parent_phone'))">
                                    <p v-if="fieldError('parent_phone')" class="mt-1.5 text-sm text-red-700">{{ fieldError('parent_phone') }}</p>
                                </div>
                                <div class="sm:col-span-2 mt-2 border-t border-slate-200 pt-5"><h3 class="font-heading text-base font-bold text-navy">Tuteur ou répondant <span class="font-body text-sm font-normal text-slate-500">(si différent des parents)</span></h3></div>
                                <div>
                                    <label for="guardian-name" class="text-sm font-semibold text-navy">Nom complet</label>
                                    <input id="guardian-name" v-model="form.guardian_name" name="guardian_name" type="text" maxlength="255" class="form-control mt-2" :aria-invalid="Boolean(fieldError('guardian_name'))">
                                </div>
                                <div>
                                    <label for="guardian-relationship" class="text-sm font-semibold text-navy">Lien avec le candidat</label>
                                    <input id="guardian-relationship" v-model="form.guardian_relationship" name="guardian_relationship" type="text" maxlength="100" placeholder="Ex. oncle, tante, répondant légal" class="form-control mt-2" :aria-invalid="Boolean(fieldError('guardian_relationship'))">
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="guardian-phone" class="text-sm font-semibold text-navy">Téléphone du répondant <span v-if="!form.parent_phone">*</span></label>
                                    <input id="guardian-phone" v-model="form.guardian_phone" name="guardian_phone" type="tel" inputmode="tel" maxlength="40" :required="!form.parent_phone" class="form-control mt-2" :aria-invalid="Boolean(fieldError('guardian_phone'))">
                                    <p v-if="fieldError('guardian_phone')" class="mt-1.5 text-sm text-red-700">{{ fieldError('guardian_phone') }}</p>
                                </div>
                            </fieldset>

                            <fieldset v-show="currentStep === 3" id="application-step-3" class="grid gap-5 rounded-2xl border border-slate-200 p-5 sm:grid-cols-2 sm:p-7">
                                <legend class="px-2"><span class="inline-flex items-center gap-2 font-heading text-lg font-bold text-navy"><GraduationCap :size="22" class="text-edsp-green" aria-hidden="true" /> Orientation pédagogique</span></legend>
                                <p class="-mt-1 text-sm text-slate-500 sm:col-span-2">Les mentions et parcours proposés s’adaptent automatiquement au niveau choisi.</p>
                                <div>
                                    <label for="academic-level" class="text-sm font-semibold text-navy">Niveau demandé *</label>
                                    <select id="academic-level" v-model.number="form.academic_level_id" name="academic_level_id" required class="form-control mt-2" :aria-invalid="Boolean(fieldError('academic_level_id'))">
                                        <option :value="0" disabled>Sélectionnez le niveau</option>
                                        <option v-for="level in levels" :key="level.id" :value="level.id">{{ level.code }} — {{ level.name }}</option>
                                    </select>
                                    <p v-if="fieldError('academic_level_id')" class="mt-1.5 text-sm text-red-700">{{ fieldError('academic_level_id') }}</p>
                                </div>
                                <div>
                                    <label for="mention" class="text-sm font-semibold text-navy">Mention *</label>
                                    <select id="mention" v-model.number="form.mention_id" name="mention_id" required :disabled="!form.academic_level_id" class="form-control mt-2 disabled:cursor-not-allowed disabled:bg-slate-100" :aria-invalid="Boolean(fieldError('mention_id'))">
                                        <option :value="0" disabled>{{ form.academic_level_id ? 'Sélectionnez la mention' : 'Choisissez d’abord un niveau' }}</option>
                                        <option v-for="mention in mentions" :key="mention.id" :value="mention.id">{{ mention.name }}</option>
                                    </select>
                                    <p v-if="fieldError('mention_id')" class="mt-1.5 text-sm text-red-700">{{ fieldError('mention_id') }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="parcours" class="text-sm font-semibold text-navy">Parcours *</label>
                                    <select id="parcours" v-model.number="form.parcours_id" name="parcours_id" required :disabled="!form.mention_id" class="form-control mt-2 disabled:cursor-not-allowed disabled:bg-slate-100" :aria-invalid="Boolean(fieldError('parcours_id'))">
                                        <option :value="0" disabled>{{ form.mention_id ? 'Sélectionnez le parcours' : 'Choisissez d’abord une mention' }}</option>
                                        <option v-for="item in parcours" :key="item.id" :value="item.id">{{ item.name }}</option>
                                    </select>
                                    <p v-if="fieldError('parcours_id')" class="mt-1.5 text-sm text-red-700">{{ fieldError('parcours_id') }}</p>
                                </div>
                                <div>
                                    <label for="last-diploma" class="text-sm font-semibold text-navy">Dernier diplôme obtenu *</label>
                                    <input id="last-diploma" v-model="form.last_diploma" name="last_diploma" type="text" required maxlength="255" placeholder="Ex. Baccalauréat série A2" class="form-control mt-2" :aria-invalid="Boolean(fieldError('last_diploma'))">
                                    <p v-if="fieldError('last_diploma')" class="mt-1.5 text-sm text-red-700">{{ fieldError('last_diploma') }}</p>
                                </div>
                                <div>
                                    <label for="graduation-year" class="text-sm font-semibold text-navy">Année d’obtention *</label>
                                    <input id="graduation-year" v-model.number="form.graduation_year" name="graduation_year" type="number" inputmode="numeric" required min="1950" :max="currentYear" class="form-control mt-2" :aria-invalid="Boolean(fieldError('graduation_year'))">
                                    <p v-if="fieldError('graduation_year')" class="mt-1.5 text-sm text-red-700">{{ fieldError('graduation_year') }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="previous-institution" class="text-sm font-semibold text-navy">Établissement précédent *</label>
                                    <input id="previous-institution" v-model="form.previous_institution" name="previous_institution" type="text" required maxlength="255" class="form-control mt-2" :aria-invalid="Boolean(fieldError('previous_institution'))">
                                    <p v-if="fieldError('previous_institution')" class="mt-1.5 text-sm text-red-700">{{ fieldError('previous_institution') }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="academic-background" class="text-sm font-semibold text-navy">Informations pédagogiques complémentaires <span class="font-normal text-slate-500">(facultatif)</span></label>
                                    <textarea id="academic-background" v-model="form.academic_background" name="academic_background" rows="4" maxlength="3000" placeholder="Redoublement, équivalences, autre diplôme ou information utile…" class="form-control mt-2 resize-y" :aria-invalid="Boolean(fieldError('academic_background'))" />
                                </div>
                            </fieldset>

                            <fieldset v-show="currentStep === 4" id="application-step-4" class="grid gap-5 rounded-2xl border border-slate-200 p-5 sm:grid-cols-2 sm:p-7">
                                <legend class="px-2"><span class="inline-flex items-center gap-2 font-heading text-lg font-bold text-navy"><Upload :size="21" class="text-edsp-green" aria-hidden="true" /> Pièces et confirmation</span></legend>

                                <div v-if="campaign.required_documents?.length" class="grid gap-4 sm:col-span-2 sm:grid-cols-2">
                                    <div v-for="document in campaign.required_documents" :key="document.key" class="rounded-xl border border-slate-200 bg-soft p-4 transition" :class="documentPreviews[document.key] ? 'border-edsp-green/40 bg-green-50/40' : ''">
                                        <label :for="`document-${document.key}`" class="block text-sm font-semibold text-navy">{{ document.label }} <span v-if="document.required">*</span></label>
                                        <input :id="`document-${document.key}`" type="file" :name="`documents[${document.key}]`" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx" :required="document.required" class="mt-3 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-navy file:px-3 file:py-2.5 file:font-semibold file:text-white" :aria-invalid="Boolean(documentError(document.key))" @change="setDocument(document.key, $event)">

                                        <div v-if="documentPreviews[document.key]" class="relative mt-4 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                                            <img
                                                v-if="documentPreviews[document.key].isImage && documentPreviews[document.key].url"
                                                :src="documentPreviews[document.key].url || undefined"
                                                :alt="`Aperçu de ${documentPreviews[document.key].name}`"
                                                class="h-36 w-full bg-slate-100 object-contain"
                                            >
                                            <div v-else class="grid h-24 place-items-center bg-slate-50 text-institutional">
                                                <FileText :size="38" aria-hidden="true" />
                                            </div>
                                            <div class="flex min-w-0 items-center gap-3 border-t border-slate-200 px-3 py-3">
                                                <span class="grid size-9 flex-none place-items-center rounded-md bg-edsp-green/10 text-edsp-green">
                                                    <ImageIcon v-if="documentPreviews[document.key].isImage" :size="18" aria-hidden="true" />
                                                    <FileText v-else :size="18" aria-hidden="true" />
                                                </span>
                                                <span class="min-w-0 flex-1">
                                                    <span class="block truncate text-xs font-semibold text-navy" :title="documentPreviews[document.key].name">{{ documentPreviews[document.key].name }}</span>
                                                    <span class="mt-0.5 block text-xs text-slate-500">{{ documentPreviews[document.key].size }}</span>
                                                </span>
                                                <button type="button" class="grid size-8 flex-none place-items-center rounded-md text-slate-500 transition hover:bg-red-50 hover:text-red-700" :aria-label="`Retirer ${documentPreviews[document.key].name}`" @click="removeDocument(document.key)">
                                                    <X :size="17" aria-hidden="true" />
                                                </button>
                                            </div>
                                        </div>
                                        <p v-if="documentError(document.key)" class="mt-2 text-sm text-red-700">{{ documentError(document.key) }}</p>
                                    </div>
                                    <p class="text-xs leading-5 text-slate-500 sm:col-span-2">JPG, PNG, WebP, PDF, DOC ou DOCX — 5 Mo maximum par fichier.</p>
                                </div>

                                <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 sm:col-span-2">
                                    <h3 class="font-heading text-base font-bold text-navy">Résumé du dossier</h3>
                                    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                                        <div><dt class="text-slate-500">Candidat</dt><dd class="mt-1 font-semibold text-navy">{{ form.first_name }} {{ form.last_name }}</dd></div>
                                        <div><dt class="text-slate-500">Contact</dt><dd class="mt-1 font-semibold text-navy">{{ form.email }}</dd></div>
                                        <div><dt class="text-slate-500">Orientation</dt><dd class="mt-1 font-semibold text-navy">{{ selectedLevel?.code }} · {{ selectedMention?.name }} · {{ selectedParcours?.name }}</dd></div>
                                        <div><dt class="text-slate-500">Formation associée</dt><dd class="mt-1 font-semibold text-navy">{{ selectedProgram }}</dd></div>
                                        <div><dt class="text-slate-500">Dernier diplôme</dt><dd class="mt-1 font-semibold text-navy">{{ form.last_diploma }} ({{ form.graduation_year }})</dd></div>
                                        <div><dt class="text-slate-500">Pièces jointes</dt><dd class="mt-1 font-semibold text-navy">{{ attachedDocuments }}</dd></div>
                                    </dl>
                                </div>

                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-soft p-4 sm:col-span-2">
                                    <input v-model="form.privacy_accepted" type="checkbox" name="privacy_accepted" required class="mt-1 size-4 flex-none accent-edsp-green" :aria-invalid="Boolean(fieldError('privacy_accepted'))">
                                    <span class="text-sm leading-6 text-slate-700">J’accepte le traitement de mes données pour l’étude de mon dossier et j’ai lu la <Link href="/politique-de-confidentialite" class="font-semibold text-institutional underline underline-offset-2">politique de confidentialité</Link>. *</span>
                                </label>
                                <p v-if="fieldError('privacy_accepted')" class="-mt-3 text-sm text-red-700 sm:col-span-2">{{ fieldError('privacy_accepted') }}</p>
                            </fieldset>

                            <div class="absolute -left-[9999px] size-px overflow-hidden" aria-hidden="true"><label for="website">Site web</label><input id="website" v-model="form.website" type="text" name="website" tabindex="-1" autocomplete="off"></div>

                            <div v-if="Object.keys(form.errors).length" class="mt-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                                <AlertCircle :size="20" class="mt-0.5 flex-none" aria-hidden="true" />
                                <p>Certains champs doivent être corrigés. Vous avez été redirigé vers l’étape concernée.</p>
                            </div>

                            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                                <button v-if="currentStep > 1" type="button" class="button-secondary justify-center" @click="previousStep"><ArrowLeft :size="17" aria-hidden="true" /> Étape précédente</button>
                                <span v-else />
                                <button v-if="currentStep < steps.length" type="button" class="button-primary justify-center" @click="nextStep">Continuer <ArrowRight :size="17" aria-hidden="true" /></button>
                                <button v-else type="submit" :disabled="form.processing" class="button-primary justify-center px-6 py-3.5 disabled:cursor-wait disabled:opacity-60"><Send :size="18" aria-hidden="true" /> {{ form.processing ? 'Envoi en cours…' : 'Envoyer mon inscription' }}</button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

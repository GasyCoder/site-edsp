<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowRight,
    CalendarClock,
    CheckCircle2,
    ChevronRight,
    ClipboardList,
    FileText,
    GraduationCap,
    Send,
} from 'lucide-vue-next';
import PublicLayout from '../layouts/PublicLayout.vue';
import SeoHead from '../components/public/SeoHead.vue';
import type { RequiredDocument, SeoData } from '../types';

type CampaignProgram = {
    id: number;
    level?: string | null;
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
};

const props = defineProps<{ campaign: AdmissionCampaign | null; seo?: SeoData }>();

const form = useForm({
    admission_campaign_id: props.campaign?.id ?? 0,
    program_id: 0,
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    birth_date: '',
    address: '',
    academic_background: '',
    documents: {} as Record<string, File | null>,
    privacy_accepted: false,
    website: '',
});

const currentDate = new Date();
currentDate.setDate(currentDate.getDate() - 1);
const maxBirthDate = [
    currentDate.getFullYear(),
    String(currentDate.getMonth() + 1).padStart(2, '0'),
    String(currentDate.getDate()).padStart(2, '0'),
].join('-');
const reviewing = ref(false);
const selectedProgram = computed(() => props.campaign?.programs.find((program) => program.id === form.program_id)?.title ?? 'Non sélectionnée');
const attachedDocuments = computed(() => Object.values(form.documents).filter((file) => file instanceof File).length);

const formatDate = (date: string): string => {
    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return date;
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(parsed);
};

const documentError = (key: string): string | undefined => (
    form.errors as Record<string, string | undefined>
)[`documents.${key}`];

const setDocument = (key: string, event: Event): void => {
    const input = event.target as HTMLInputElement;

    form.documents[key] = input.files?.[0] ?? null;
};

const submit = (): void => {
    if (!reviewing.value) {
        reviewing.value = true;
        return;
    }

    form.post('/preinscription', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            reviewing.value = false;
            form.reset(
                'program_id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'birth_date',
                'address',
                'academic_background',
                'documents',
                'privacy_accepted',
                'website',
            );
        },
        onError: () => {
            reviewing.value = false;
        },
    });
};
</script>

<template>
    <SeoHead
        :title="seo?.title || 'Préinscription | EDSP'"
        :description="seo?.description || 'Déposez votre demande de préinscription auprès de l’École de Droit et Science Politique de l’Université de Mahajanga.'"
        :canonical-url="seo?.canonical"
        :open-graph-title="seo?.og_title"
        :open-graph-description="seo?.og_description"
        :structured-data="seo?.schema"
        :no-index="!campaign"
    />

    <PublicLayout>
        <header class="relative isolate overflow-hidden bg-soft">
            <div
                class="absolute inset-y-0 right-0 -z-10 hidden w-[32%] bg-navy lg:block"
                aria-hidden="true"
            />
            <div
                class="absolute -left-24 -top-32 -z-10 h-80 w-80 rounded-full bg-edsp-green/10 blur-3xl"
                aria-hidden="true"
            />

            <div class="mx-auto max-w-7xl px-6 py-14 sm:py-16 lg:py-20">
                <nav aria-label="Fil d’Ariane" class="mb-8">
                    <ol class="flex items-center gap-2 text-sm text-gray-500">
                        <li><Link href="/" class="transition hover:text-edsp-green">Accueil</Link></li>
                        <li aria-hidden="true"><ChevronRight :size="15" /></li>
                        <li class="font-semibold text-navy" aria-current="page">Préinscription</li>
                    </ol>
                </nav>

                <div class="max-w-3xl">
                    <p class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-edsp-green">
                        Rejoindre l’EDSP
                    </p>
                    <h1 class="text-3xl font-extrabold leading-tight text-navy sm:text-4xl lg:text-5xl">
                        Préinscription
                    </h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-gray-600 sm:text-lg">
                        Transmettez votre demande en ligne pendant la période d’ouverture d’une campagne officielle.
                    </p>
                </div>
            </div>
        </header>

        <div class="bg-white">
            <div class="mx-auto max-w-7xl px-6 py-16 sm:py-20">
                <div
                    v-if="!campaign"
                    class="mx-auto max-w-3xl rounded-xl border border-amber-200 bg-amber-50 px-6 py-10 text-center sm:px-10"
                    role="status"
                >
                    <CalendarClock :size="40" class="mx-auto text-[#8A6410]" aria-hidden="true" />
                    <h2 class="mt-5 text-2xl font-bold text-navy">Campagne actuellement fermée</h2>
                    <p class="mx-auto mt-3 max-w-xl leading-7 text-amber-950">
                        Aucune campagne de préinscription n’est ouverte pour le moment. Les prochaines dates seront communiquées sur le site.
                    </p>
                    <Link
                        href="/actualites"
                        class="mt-7 inline-flex items-center gap-2 rounded-md bg-navy px-5 py-3 font-heading text-sm font-semibold text-white transition hover:bg-institutional"
                    >
                        Consulter les actualités
                        <ArrowRight :size="17" aria-hidden="true" />
                    </Link>
                </div>

                <div v-else class="grid items-start gap-10 lg:grid-cols-[20rem_minmax(0,1fr)] lg:gap-14">
                    <aside class="rounded-xl bg-navy p-6 text-white lg:sticky lg:top-28 sm:p-7" aria-labelledby="campaign-title">
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-white/10 text-gold">
                            <ClipboardList :size="23" aria-hidden="true" />
                        </div>
                        <p class="mt-5 text-xs font-bold uppercase tracking-[0.15em] text-gold">
                            Campagne ouverte
                        </p>
                        <h2 id="campaign-title" class="mt-2 text-xl font-bold leading-snug">
                            {{ campaign.title }}
                        </h2>

                        <dl class="mt-6 divide-y divide-white/15 text-sm">
                            <div class="py-4 first:pt-0">
                                <dt class="text-blue-200">Année universitaire</dt>
                                <dd class="mt-1 font-semibold text-white">{{ campaign.academic_year }}</dd>
                            </div>
                            <div class="py-4">
                                <dt class="text-blue-200">Clôture des demandes</dt>
                                <dd class="mt-1 font-semibold text-white">{{ formatDate(campaign.closes_at) }}</dd>
                            </div>
                        </dl>

                        <div v-if="campaign.instructions" class="border-t border-white/15 pt-5">
                            <h3 class="text-sm font-bold text-white">Instructions</h3>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-blue-100">
                                {{ campaign.instructions }}
                            </p>
                        </div>

                        <div v-if="campaign.required_documents?.length" class="mt-6 border-t border-white/15 pt-5">
                            <h3 class="flex items-center gap-2 text-sm font-bold text-white">
                                <FileText :size="17" class="text-gold" aria-hidden="true" />
                                Pièces demandées
                            </h3>
                            <ul class="mt-3 space-y-2 text-sm leading-6 text-blue-100">
                                <li
                                    v-for="document in campaign.required_documents"
                                    :key="document.key"
                                    class="flex gap-2"
                                >
                                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-gold" aria-hidden="true" />
                                    {{ document.label }}<template v-if="!document.required"> (facultatif)</template>
                                </li>
                            </ul>
                        </div>
                    </aside>

                    <section aria-labelledby="application-form-title">
                        <div class="mb-8">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-edsp-green">
                                Votre demande
                            </p>
                            <h2 id="application-form-title" class="mt-2 text-2xl font-bold text-navy sm:text-3xl">
                                Formulaire de préinscription
                            </h2>
                            <p class="mt-3 leading-7 text-gray-600">
                                Tous les champs signalés par un astérisque sont obligatoires.
                            </p>
                        </div>

                        <form class="grid gap-x-6 gap-y-5 sm:grid-cols-2" @submit.prevent="submit">
                            <div>
                                <label for="first-name" class="block text-sm font-semibold text-navy">Prénom <span aria-hidden="true">*</span></label>
                                <input
                                    id="first-name"
                                    v-model="form.first_name"
                                    type="text"
                                    name="first_name"
                                    autocomplete="given-name"
                                    required
                                    maxlength="100"
                                    :aria-invalid="Boolean(form.errors.first_name)"
                                    :aria-describedby="form.errors.first_name ? 'first-name-error' : undefined"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition placeholder:text-gray-400 hover:border-gray-400"
                                >
                                <p v-if="form.errors.first_name" id="first-name-error" class="mt-1.5 text-sm text-red-700">{{ form.errors.first_name }}</p>
                            </div>

                            <div>
                                <label for="last-name" class="block text-sm font-semibold text-navy">Nom <span aria-hidden="true">*</span></label>
                                <input
                                    id="last-name"
                                    v-model="form.last_name"
                                    type="text"
                                    name="last_name"
                                    autocomplete="family-name"
                                    required
                                    maxlength="100"
                                    :aria-invalid="Boolean(form.errors.last_name)"
                                    :aria-describedby="form.errors.last_name ? 'last-name-error' : undefined"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                >
                                <p v-if="form.errors.last_name" id="last-name-error" class="mt-1.5 text-sm text-red-700">{{ form.errors.last_name }}</p>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-semibold text-navy">Adresse email <span aria-hidden="true">*</span></label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    name="email"
                                    autocomplete="email"
                                    required
                                    maxlength="255"
                                    :aria-invalid="Boolean(form.errors.email)"
                                    :aria-describedby="form.errors.email ? 'email-error' : undefined"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                >
                                <p v-if="form.errors.email" id="email-error" class="mt-1.5 text-sm text-red-700">{{ form.errors.email }}</p>
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-semibold text-navy">Téléphone <span aria-hidden="true">*</span></label>
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    name="phone"
                                    autocomplete="tel"
                                    inputmode="tel"
                                    required
                                    maxlength="40"
                                    :aria-invalid="Boolean(form.errors.phone)"
                                    :aria-describedby="form.errors.phone ? 'phone-error' : undefined"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                >
                                <p v-if="form.errors.phone" id="phone-error" class="mt-1.5 text-sm text-red-700">{{ form.errors.phone }}</p>
                            </div>

                            <div>
                                <label for="birth-date" class="block text-sm font-semibold text-navy">Date de naissance <span aria-hidden="true">*</span></label>
                                <input
                                    id="birth-date"
                                    v-model="form.birth_date"
                                    type="date"
                                    name="birth_date"
                                    autocomplete="bday"
                                    required
                                    :max="maxBirthDate"
                                    :aria-invalid="Boolean(form.errors.birth_date)"
                                    :aria-describedby="form.errors.birth_date ? 'birth-date-error' : undefined"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                >
                                <p v-if="form.errors.birth_date" id="birth-date-error" class="mt-1.5 text-sm text-red-700">{{ form.errors.birth_date }}</p>
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-semibold text-navy">Adresse <span aria-hidden="true">*</span></label>
                                <input
                                    id="address"
                                    v-model="form.address"
                                    type="text"
                                    name="address"
                                    autocomplete="street-address"
                                    required
                                    maxlength="500"
                                    :aria-invalid="Boolean(form.errors.address)"
                                    :aria-describedby="form.errors.address ? 'address-error' : undefined"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                >
                                <p v-if="form.errors.address" id="address-error" class="mt-1.5 text-sm text-red-700">{{ form.errors.address }}</p>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="program" class="block text-sm font-semibold text-navy">Formation souhaitée <span aria-hidden="true">*</span></label>
                                <div class="relative mt-2">
                                    <GraduationCap :size="19" class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                                    <select
                                        id="program"
                                        v-model.number="form.program_id"
                                        name="program_id"
                                        required
                                        :aria-invalid="Boolean(form.errors.program_id)"
                                        :aria-describedby="form.errors.program_id ? 'program-error' : undefined"
                                        class="w-full appearance-none rounded-md border border-gray-300 bg-white py-3 pl-12 pr-10 text-gray-900 transition hover:border-gray-400"
                                    >
                                        <option :value="0" disabled>Sélectionnez une formation</option>
                                        <option v-for="program in campaign.programs" :key="program.id" :value="program.id">
                                            {{ program.title }}<template v-if="program.level"> — {{ program.level }}</template>
                                        </option>
                                    </select>
                                </div>
                                <p v-if="form.errors.program_id" id="program-error" class="mt-1.5 text-sm text-red-700">{{ form.errors.program_id }}</p>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="academic-background" class="block text-sm font-semibold text-navy">Parcours académique <span aria-hidden="true">*</span></label>
                                <textarea
                                    id="academic-background"
                                    v-model="form.academic_background"
                                    name="academic_background"
                                    rows="6"
                                    required
                                    maxlength="3000"
                                    :aria-invalid="Boolean(form.errors.academic_background)"
                                    :aria-describedby="form.errors.academic_background ? 'academic-background-error' : 'academic-background-help'"
                                    class="mt-2 w-full resize-y rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-900 transition hover:border-gray-400"
                                />
                                <p id="academic-background-help" class="mt-1.5 text-sm text-gray-500">
                                    Indiquez vos diplômes obtenus et votre formation actuelle.
                                </p>
                                <p v-if="form.errors.academic_background" id="academic-background-error" class="mt-1.5 text-sm text-red-700">{{ form.errors.academic_background }}</p>
                            </div>

                            <fieldset
                                v-if="campaign.required_documents?.length"
                                class="grid gap-5 rounded-xl border border-gray-200 bg-soft p-5 sm:col-span-2 sm:grid-cols-2"
                            >
                                <legend class="px-2 font-heading text-base font-bold text-navy">
                                    Pièces justificatives
                                </legend>
                                <p class="-mt-1 text-sm leading-6 text-gray-600 sm:col-span-2">
                                    Formats acceptés : JPG, PNG, WebP, PDF, DOC et DOCX. Taille maximale : 5 Mo par fichier.
                                </p>

                                <div
                                    v-for="document in campaign.required_documents"
                                    :key="document.key"
                                >
                                    <label
                                        :for="`document-${document.key}`"
                                        class="block text-sm font-semibold text-navy"
                                    >
                                        {{ document.label }} <span v-if="document.required" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        :id="`document-${document.key}`"
                                        type="file"
                                        :name="`documents[${document.key}]`"
                                        accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx"
                                        :required="document.required"
                                        :aria-invalid="Boolean(documentError(document.key))"
                                        :aria-describedby="documentError(document.key) ? `document-${document.key}-error` : undefined"
                                        class="mt-2 block w-full rounded-md border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:border-0 file:bg-navy file:px-4 file:py-3 file:font-semibold file:text-white hover:border-gray-400"
                                        @change="setDocument(document.key, $event)"
                                    >
                                    <p
                                        v-if="documentError(document.key)"
                                        :id="`document-${document.key}-error`"
                                        class="mt-1.5 text-sm text-red-700"
                                    >
                                        {{ documentError(document.key) }}
                                    </p>
                                </div>
                            </fieldset>

                            <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                                <label for="website">Site web</label>
                                <input id="website" v-model="form.website" type="text" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-soft p-4">
                                    <input
                                        v-model="form.privacy_accepted"
                                        type="checkbox"
                                        name="privacy_accepted"
                                        required
                                        :aria-invalid="Boolean(form.errors.privacy_accepted)"
                                        :aria-describedby="form.errors.privacy_accepted ? 'privacy-error' : undefined"
                                        class="mt-1 h-4 w-4 shrink-0 accent-edsp-green"
                                    >
                                    <span class="text-sm leading-6 text-gray-700">
                                        J’accepte le traitement de mes données pour l’étude de ma demande et j’ai pris connaissance de la
                                        <Link href="/politique-de-confidentialite" class="font-semibold text-institutional underline underline-offset-2">
                                            politique de confidentialité
                                        </Link>.
                                        <span aria-hidden="true">*</span>
                                    </span>
                                </label>
                                <p v-if="form.errors.privacy_accepted" id="privacy-error" class="mt-1.5 text-sm text-red-700">{{ form.errors.privacy_accepted }}</p>
                            </div>

                            <div
                                v-if="Object.keys(form.errors).length"
                                class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 sm:col-span-2"
                                role="alert"
                            >
                                <AlertCircle :size="20" class="mt-0.5 shrink-0" aria-hidden="true" />
                                <p>Certains champs doivent être corrigés avant l’envoi du formulaire.</p>
                            </div>

                            <div
                                v-if="reviewing"
                                class="rounded-xl border border-blue-200 bg-blue-50 p-5 text-sm text-blue-950 sm:col-span-2"
                                role="region"
                                aria-labelledby="application-review-title"
                            >
                                <div class="flex items-center gap-2">
                                    <CheckCircle2 :size="20" class="text-edsp-green" aria-hidden="true" />
                                    <h3 id="application-review-title" class="font-bold text-navy">Vérifiez avant l’envoi définitif</h3>
                                </div>
                                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div><dt class="text-gray-500">Candidat</dt><dd class="font-semibold">{{ form.first_name }} {{ form.last_name }}</dd></div>
                                    <div><dt class="text-gray-500">Email</dt><dd class="font-semibold">{{ form.email }}</dd></div>
                                    <div><dt class="text-gray-500">Formation</dt><dd class="font-semibold">{{ selectedProgram }}</dd></div>
                                    <div><dt class="text-gray-500">Pièces jointes</dt><dd class="font-semibold">{{ attachedDocuments }}</dd></div>
                                </dl>
                                <button type="button" class="mt-4 font-semibold text-institutional underline underline-offset-2" @click="reviewing = false">
                                    Corriger mes informations
                                </button>
                            </div>

                            <div class="sm:col-span-2">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-edsp-green px-6 py-4 font-heading text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-wait disabled:opacity-60 sm:w-auto"
                                >
                                    <Send :size="18" aria-hidden="true" />
                                    {{ form.processing ? 'Envoi en cours…' : reviewing ? 'Confirmer et envoyer' : 'Vérifier mon dossier' }}
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Award,
    BookOpen,
    BriefcaseBusiness,
    CheckCircle2,
    ChevronRight,
    Clock3,
    Download,
    FileText,
    GraduationCap,
    Landmark,
    Layers3,
    Target,
    UserPlus,
    UserRound,
} from 'lucide-vue-next';
import { computed } from 'vue';
import PublicLayout from '../../layouts/PublicLayout.vue';
import MediaPlaceholder from '../../components/public/MediaPlaceholder.vue';
import SeoHead from '../../components/public/SeoHead.vue';
import RichText from '../../components/public/RichText.vue';
import { mediaUrl, safePublicUrl } from '../../lib/public-content';
import type { Program, PublicDocument, SeoData } from '../../types';
import { useI18n } from '../../lib/i18n';
import { pathwayLevels, programLevelLabel, programPathwayLabel, programPathways } from '../../lib/academic-offer';

type ProgramDetails = Program & {
    admission_requirements?: string | null;
    careers?: string | null;
    curriculum?: string | null;
    domain?: string | null;
    manager?: string | null;
    mention?: string | null;
    meta_description?: string | null;
    meta_title?: string | null;
    objectives?: string | null;
    skills?: string | null;
    track?: string | null;
};

const props = defineProps<{ program: ProgramDetails; seo?: SeoData }>();
const { locale, tr } = useI18n();

const programImage = computed(() => props.program.image_url || mediaUrl(props.program.image));
const programImageAlt = computed(
    () => props.program.image?.alt_text || tr(`Illustration de la formation : ${props.program.title}`, `Programme illustration: ${props.program.title}`),
);
const documentUrl = (document: PublicDocument): string | null => safePublicUrl(document.download_url);
const levelLabel = computed(() => programLevelLabel(props.program));
const mentionLabel = computed(() => props.program.mention_record?.nom || props.program.mention || props.program.domain);
const pathwayLabel = computed(() => programPathwayLabel(props.program));
const pathways = computed(() => programPathways(props.program));

const formatFileSize = (bytes?: number | null): string | null => {
    if (!bytes || bytes < 1) {
        return null;
    }

    if (bytes < 1024 * 1024) {
        return `${Math.ceil(bytes / 1024)} ${locale.value === 'en' ? 'KB' : 'Ko'}`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1).replace('.', locale.value === 'en' ? '.' : ',')} ${locale.value === 'en' ? 'MB' : 'Mo'}`;
};
</script>

<template>
    <SeoHead
        :title="seo?.title || program.meta_title || `${program.title} | EDSP`"
        :description="seo?.description || program.meta_description || program.description"
        :canonical-url="seo?.canonical"
        :image-url="seo?.og_image || program.image_url"
        :keywords="seo?.keywords"
        :open-graph-title="seo?.og_title"
        :open-graph-description="seo?.og_description"
        :structured-data="seo?.schema"
        :no-index="seo?.robots?.includes('noindex')"
        :no-follow="seo?.robots?.includes('nofollow')"
    />

    <PublicLayout>
        <header class="public-page-hero bg-navy text-white">
            <div class="mx-auto max-w-7xl">
                <nav :aria-label="tr('Fil d’Ariane', 'Breadcrumb')" class="mb-7">
                    <ol class="flex flex-wrap items-center gap-2 text-sm text-blue-100/80">
                        <li><Link href="/" class="transition hover:text-white">{{ tr('Accueil', 'Home') }}</Link></li>
                        <li aria-hidden="true"><ChevronRight :size="15" /></li>
                        <li><Link href="/formations" class="transition hover:text-white">{{ tr('Formations', 'Programmes') }}</Link></li>
                        <li aria-hidden="true"><ChevronRight :size="15" /></li>
                        <li class="font-semibold text-white" aria-current="page">{{ program.title }}</li>
                    </ol>
                </nav>

                <div class="max-w-4xl">
                    <span class="inline-flex rounded-md bg-gold px-3 py-1 text-xs font-bold text-navy">
                        {{ levelLabel }}
                    </span>
                    <h1 class="page-title mt-4">
                        {{ program.title }}
                    </h1>
                    <p class="mt-5 max-w-3xl text-base leading-7 text-blue-100">
                        {{ program.description }}
                    </p>
                </div>
            </div>
        </header>

        <div class="bg-white">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-14">
                <article class="min-w-0">
                    <figure
                        v-if="programImage"
                        class="mb-10 overflow-hidden rounded-xl border border-slate-200 bg-soft"
                    >
                        <div class="h-72 sm:h-[26rem]">
                            <MediaPlaceholder
                                :image-url="programImage"
                                :alt="programImageAlt"
                                :label="programImageAlt"
                            />
                        </div>
                        <figcaption v-if="program.image?.caption" class="px-5 py-3 text-sm text-gray-600">
                            {{ program.image.caption }}
                        </figcaption>
                    </figure>

                    <section v-if="program.objectives" aria-labelledby="objectives-title" class="border-b border-gray-200 pb-12">
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-edsp-green/10 text-edsp-green">
                                <Target :size="22" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.15em] text-edsp-green">{{ tr('La formation', 'The programme') }}</p>
                                <h2 id="objectives-title" class="mt-1 text-2xl font-bold text-navy sm:text-3xl">{{ tr('Objectifs', 'Objectives') }}</h2>
                            </div>
                        </div>
                        <RichText :html="program.objectives" class="mt-6 text-gray-600" />
                    </section>

                    <section v-if="pathways.length" aria-labelledby="pathways-title" class="border-b border-gray-200 py-12">
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-institutional/10 text-institutional">
                                <Layers3 :size="22" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.15em] text-edsp-green">{{ tr('Organisation des études', 'Programme structure') }}</p>
                                <h2 id="pathways-title" class="mt-1 text-2xl font-bold text-navy sm:text-3xl">{{ tr('Parcours selon le niveau', 'Pathways by level') }}</h2>
                            </div>
                        </div>

                        <div class="mt-7 grid gap-4 sm:grid-cols-2">
                            <article v-for="pathway in pathways" :key="pathway.id" class="rounded-xl border border-gray-200 bg-soft p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-bold text-navy">{{ pathway.nom }}</h3>
                                    <span class="rounded bg-white px-2 py-1 text-xs font-bold text-slate-500">{{ pathway.code }}</span>
                                </div>
                                <p v-if="pathway.description" class="mt-2 text-sm leading-6 text-gray-600">{{ pathway.description }}</p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span
                                        v-for="link in pathwayLevels(pathway)"
                                        :key="link.id"
                                        class="rounded-full border px-3 py-1 text-xs font-bold"
                                        :class="link.is_common_core ? 'border-gold/50 bg-gold/15 text-[#795707]' : 'border-edsp-green/20 bg-edsp-green/10 text-edsp-green'"
                                    >
                                        {{ link.level?.code }}<template v-if="link.is_common_core"> · {{ tr('Tronc commun', 'Common core') }}</template>
                                    </span>
                                </div>
                            </article>
                        </div>
                    </section>

                    <section
                        v-if="program.curriculum"
                        aria-labelledby="curriculum-title"
                        class="border-b border-gray-200 py-12"
                    >
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-institutional/10 text-institutional">
                                <BookOpen :size="22" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.15em] text-edsp-green">{{ tr('Enseignements', 'Teaching') }}</p>
                                <h2 id="curriculum-title" class="mt-1 text-2xl font-bold text-navy sm:text-3xl">{{ tr('Programme', 'Curriculum') }}</h2>
                            </div>
                        </div>
                        <RichText :html="program.curriculum" class="mt-6 text-gray-600" />
                    </section>

                    <section
                        v-if="program.skills"
                        aria-labelledby="skills-title"
                        class="border-b border-gray-200 py-12"
                    >
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gold/20 text-[#8A6410]">
                                <Award :size="22" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.15em] text-edsp-green">{{ tr('Savoir-faire', 'Skills') }}</p>
                                <h2 id="skills-title" class="mt-1 text-2xl font-bold text-navy sm:text-3xl">{{ tr('Compétences visées', 'Skills developed') }}</h2>
                            </div>
                        </div>
                        <RichText :html="program.skills" class="mt-6 text-gray-600" />
                    </section>

                    <section
                        v-if="program.admission_requirements"
                        aria-labelledby="admission-title"
                        class="border-b border-gray-200 py-12"
                    >
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-edsp-green/10 text-edsp-green">
                                <CheckCircle2 :size="22" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.15em] text-edsp-green">{{ tr('Candidature', 'Applications') }}</p>
                                <h2 id="admission-title" class="mt-1 text-2xl font-bold text-navy sm:text-3xl">{{ tr('Conditions d’admission', 'Entry requirements') }}</h2>
                            </div>
                        </div>
                        <RichText :html="program.admission_requirements" class="mt-6 text-gray-600" />
                    </section>

                    <section v-if="program.careers" aria-labelledby="careers-title" class="pt-12">
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-institutional/10 text-institutional">
                                <BriefcaseBusiness :size="22" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.15em] text-edsp-green">{{ tr('Après la formation', 'After graduation') }}</p>
                                <h2 id="careers-title" class="mt-1 text-2xl font-bold text-navy sm:text-3xl">{{ tr('Débouchés', 'Career opportunities') }}</h2>
                            </div>
                        </div>
                        <RichText :html="program.careers" class="mt-6 text-gray-600" />
                    </section>

                    <section
                        v-if="program.documents?.length"
                        aria-labelledby="program-documents-title"
                        class="mt-12 border-t border-gray-200 pt-10"
                    >
                        <div class="flex items-center gap-3">
                            <FileText :size="23" class="text-edsp-green" aria-hidden="true" />
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.15em] text-edsp-green">
                                    {{ tr('Ressources', 'Resources') }}
                                </p>
                                <h2 id="program-documents-title" class="mt-1 text-2xl font-bold text-navy">
                                    {{ tr('Documents de la formation', 'Programme documents') }}
                                </h2>
                            </div>
                        </div>
                        <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                            <li
                                v-for="document in program.documents"
                                :key="document.id"
                                class="rounded-xl border border-gray-200 bg-soft p-4"
                            >
                                <a
                                    v-if="documentUrl(document)"
                                    :href="documentUrl(document) ?? undefined"
                                    class="group flex h-full items-start justify-between gap-4"
                                >
                                    <span>
                                        <span class="block font-semibold text-navy group-hover:text-institutional">
                                            {{ document.title }}
                                        </span>
                                        <span v-if="document.description" class="mt-1 block text-sm leading-6 text-gray-600">
                                            {{ document.description }}
                                        </span>
                                        <span
                                            v-if="document.category || formatFileSize(document.size)"
                                            class="mt-2 block text-xs font-semibold uppercase tracking-wide text-gray-500"
                                        >
                                            {{ [document.category, formatFileSize(document.size)].filter(Boolean).join(' · ') }}
                                        </span>
                                    </span>
                                    <Download :size="20" class="mt-0.5 shrink-0 text-edsp-green" aria-hidden="true" />
                                </a>
                            </li>
                        </ul>
                    </section>

                    <div
                        v-if="!program.objectives && !program.curriculum && !program.skills && !program.admission_requirements && !program.careers && !program.documents?.length"
                        class="rounded-xl border border-dashed border-gray-300 bg-soft p-8 text-center"
                    >
                        <GraduationCap :size="34" class="mx-auto text-institutional" aria-hidden="true" />
                        <h2 class="mt-4 text-xl font-bold text-navy">{{ tr('Informations complémentaires à venir', 'More information coming soon') }}</h2>
                        <p class="mt-2 leading-7 text-gray-600">
                            {{ tr('Les détails de cette formation seront publiés prochainement.', 'Further details about this programme will be published soon.') }}
                        </p>
                    </div>
                </article>

                <aside class="lg:order-last" :aria-label="tr('Informations pratiques', 'Practical information')">
                    <div class="rounded-xl border border-gray-200 bg-soft p-6 lg:sticky lg:top-28">
                        <h2 class="text-lg font-bold text-navy">{{ tr('En bref', 'At a glance') }}</h2>
                        <dl class="mt-5 divide-y divide-gray-200">
                            <div class="flex gap-3 py-4 first:pt-0">
                                <GraduationCap :size="19" class="mt-0.5 shrink-0 text-edsp-green" aria-hidden="true" />
                                <div>
                                    <dt class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ tr('Niveau', 'Level') }}</dt>
                                    <dd class="mt-1 font-semibold text-navy">{{ levelLabel }}</dd>
                                </div>
                            </div>
                            <div v-if="program.duration" class="flex gap-3 py-4">
                                <Clock3 :size="19" class="mt-0.5 shrink-0 text-edsp-green" aria-hidden="true" />
                                <div>
                                    <dt class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ tr('Durée', 'Duration') }}</dt>
                                    <dd class="mt-1 font-semibold text-navy">{{ program.duration }}</dd>
                                </div>
                            </div>
                            <div v-if="mentionLabel" class="flex gap-3 py-4">
                                <Landmark :size="19" class="mt-0.5 shrink-0 text-edsp-green" aria-hidden="true" />
                                <div>
                                    <dt class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ tr('Mention', 'Subject area') }}</dt>
                                    <dd class="mt-1 font-semibold leading-6 text-navy">{{ mentionLabel }}</dd>
                                </div>
                            </div>
                            <div v-if="pathwayLabel" class="flex gap-3 py-4">
                                <BookOpen :size="19" class="mt-0.5 shrink-0 text-edsp-green" aria-hidden="true" />
                                <div>
                                    <dt class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ tr('Parcours', 'Pathways') }}</dt>
                                    <dd class="mt-1 font-semibold leading-6 text-navy">{{ pathwayLabel }}</dd>
                                </div>
                            </div>
                            <div v-if="program.manager" class="flex gap-3 py-4">
                                <UserRound :size="19" class="mt-0.5 shrink-0 text-edsp-green" aria-hidden="true" />
                                <div>
                                    <dt class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ tr('Responsable', 'Programme leader') }}</dt>
                                    <dd class="mt-1 font-semibold leading-6 text-navy">{{ program.manager }}</dd>
                                </div>
                            </div>
                        </dl>

                        <Link
                            href="/inscription"
                            class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-md bg-edsp-green px-5 py-3.5 font-heading text-sm font-semibold text-white transition hover:bg-green-700"
                        >
                            <UserPlus :size="17" aria-hidden="true" />
                            {{ tr('S’inscrire', 'Apply now') }}
                        </Link>
                    </div>
                </aside>
            </div>

            <div class="border-t border-gray-200 bg-soft">
                <div class="mx-auto max-w-7xl px-6 py-8">
                    <Link
                        href="/formations"
                        class="inline-flex items-center gap-2 font-semibold text-institutional transition hover:text-navy"
                    >
                        <ArrowLeft :size="18" aria-hidden="true" />
                        {{ tr('Toutes les formations', 'All programmes') }}
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

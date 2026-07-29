<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, BookOpen, ChevronRight, GraduationCap, Layers3 } from 'lucide-vue-next';
import { computed } from 'vue';
import PublicLayout from '../../layouts/PublicLayout.vue';
import SeoHead from '../../components/public/SeoHead.vue';
import type { AcademicMention, AcademicPathway, AcademicPathwayLevel, Program, SeoData } from '../../types';
import { pathwayLevels } from '../../lib/academic-offer';
import { useI18n } from '../../lib/i18n';

const props = defineProps<{ mentions: AcademicMention[]; seo?: SeoData }>();
const { tr } = useI18n();

const pathwayCount = computed(() => props.mentions.reduce((total, mention) => total + (mention.parcours?.length ?? 0), 0));
const pageForMention = (mention: AcademicMention): Program | undefined => mention.programs?.[0];
const levels = (pathway: AcademicPathway): AcademicPathwayLevel[] => pathwayLevels(pathway);
const mentionAnchor = (mention: AcademicMention): string => `mention-${mention.code.toLowerCase()}`;
</script>

<template>
    <SeoHead
        :title="seo?.title || tr('Formations | EDSP', 'Degree programmes | EDSP')"
        :description="seo?.description || tr('Découvrez les deux mentions et les parcours proposés par l’EDSP, de la L1 au M2.', 'Explore EDSP’s two subject areas and their pathways from L1 to M2.')"
        :canonical-url="seo?.canonical"
        :structured-data="seo?.schema"
    />

    <PublicLayout>
        <header class="public-page-hero bg-soft">
            <div class="mx-auto max-w-7xl">
                <nav :aria-label="tr('Fil d’Ariane', 'Breadcrumb')" class="mb-6">
                    <ol class="flex items-center gap-2 text-sm text-gray-500">
                        <li><Link href="/" class="transition hover:text-edsp-green">{{ tr('Accueil', 'Home') }}</Link></li>
                        <li aria-hidden="true"><ChevronRight :size="15" /></li>
                        <li class="font-semibold text-navy" aria-current="page">{{ tr('Formations', 'Programmes') }}</li>
                    </ol>
                </nav>

                <div class="max-w-3xl">
                    <p class="section-eyebrow mb-3">
                        {{ tr('Offre académique', 'Academic offering') }}
                    </p>
                    <h1 class="page-title text-navy">
                        {{ tr('Deux mentions, des parcours adaptés à chaque niveau', 'Two subject areas, pathways tailored to every level') }}
                    </h1>
                    <p class="section-description max-w-2xl">
                        {{ tr('Le parcours évolue progressivement de la L1 au M2. Les affectations ci-dessous reprennent directement le référentiel officiel de la scolarité.', 'Pathways progress from L1 to M2. The structure below comes directly from EDSP’s official academic records.') }}
                    </p>

                    <dl class="mt-7 flex flex-wrap gap-3 text-sm">
                        <div class="rounded-md border border-slate-200 bg-white px-4 py-2 text-slate-700">
                            <dt class="sr-only">{{ tr('Mentions', 'Subject areas') }}</dt>
                            <dd><strong class="text-navy">{{ mentions.length }}</strong> {{ tr('mentions', 'subject areas') }}</dd>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-white px-4 py-2 text-slate-700">
                            <dt class="sr-only">{{ tr('Parcours', 'Pathways') }}</dt>
                            <dd><strong class="text-navy">{{ pathwayCount }}</strong> {{ tr('parcours', 'pathways') }}</dd>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-white px-4 py-2 font-semibold text-navy">L1 → M2</div>
                    </dl>
                </div>
            </div>
        </header>

        <main class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16">
                <nav v-if="mentions.length" :aria-label="tr('Accès rapide aux mentions', 'Subject-area shortcuts')" class="mb-10 flex flex-wrap gap-3">
                    <a
                        v-for="mention in mentions"
                        :key="`nav-${mention.id}`"
                        :href="`#${mentionAnchor(mention)}`"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-soft px-4 py-2.5 text-sm font-semibold text-navy transition hover:border-edsp-green hover:text-edsp-green"
                    >
                        <GraduationCap :size="17" aria-hidden="true" />
                        {{ mention.nom }}
                    </a>
                </nav>

                <div v-if="mentions.length" class="space-y-8">
                    <section
                        v-for="(mention, mentionIndex) in mentions"
                        :id="mentionAnchor(mention)"
                        :key="mention.id"
                        class="scroll-mt-28 overflow-hidden rounded-xl border border-slate-200 bg-white"
                        :aria-labelledby="`mention-title-${mention.id}`"
                    >
                        <div class="grid gap-6 border-b border-slate-200 bg-soft px-6 py-7 sm:px-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                            <div class="flex items-start gap-4">
                                <span
                                    class="grid size-12 shrink-0 place-items-center rounded-xl text-lg font-extrabold"
                                    :class="mentionIndex % 2 === 0 ? 'bg-edsp-green text-white' : 'bg-institutional text-white'"
                                >
                                    {{ String(mentionIndex + 1).padStart(2, '0') }}
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-edsp-green">
                                        {{ tr('Mention', 'Subject area') }} · {{ mention.code }}
                                    </p>
                                    <h2 :id="`mention-title-${mention.id}`" class="mt-1 text-2xl font-bold text-navy sm:text-3xl">
                                        {{ mention.nom }}
                                    </h2>
                                    <p v-if="mention.description" class="mt-2 max-w-3xl leading-7 text-slate-600">
                                        {{ mention.description }}
                                    </p>
                                </div>
                            </div>

                            <Link
                                v-if="pageForMention(mention)"
                                :href="`/formations/${pageForMention(mention)?.slug}`"
                                class="inline-flex w-fit items-center gap-2 rounded-md border border-navy/20 bg-white px-4 py-2.5 text-sm font-semibold text-navy transition hover:border-edsp-green hover:text-edsp-green"
                            >
                                {{ tr('Voir la fiche complète', 'View full details') }}
                                <ArrowRight :size="16" aria-hidden="true" />
                            </Link>
                        </div>

                        <div class="grid gap-5 p-6 sm:p-8 lg:grid-cols-3">
                            <article
                                v-for="pathway in mention.parcours"
                                :key="pathway.id"
                                class="flex min-w-0 flex-col rounded-lg border border-slate-200 p-5 transition hover:border-edsp-green/50"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-edsp-green/10 text-edsp-green">
                                        <Layers3 :size="20" aria-hidden="true" />
                                    </span>
                                    <span class="rounded bg-soft px-2 py-1 text-xs font-bold tracking-wide text-slate-500">{{ pathway.code }}</span>
                                </div>
                                <h3 class="mt-5 text-lg font-bold text-navy">{{ pathway.nom }}</h3>
                                <p v-if="pathway.description" class="mt-2 flex-1 text-sm leading-6 text-slate-600">
                                    {{ pathway.description }}
                                </p>

                                <div class="mt-5 border-t border-slate-100 pt-4">
                                    <p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">
                                        {{ tr('Niveaux concernés', 'Available levels') }}
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="link in levels(pathway)"
                                            :key="link.id"
                                            class="inline-flex items-center gap-1.5 rounded-md border px-3 py-1 text-xs font-bold"
                                            :class="link.is_common_core ? 'border-gold/50 bg-gold/15 text-[#795707]' : 'border-edsp-green/20 bg-edsp-green/8 text-edsp-green'"
                                        >
                                            {{ link.level?.code }}
                                            <span v-if="link.is_common_core">· {{ tr('Tronc commun', 'Common core') }}</span>
                                        </span>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </section>
                </div>

                <div v-else class="rounded-xl border border-dashed border-gray-300 bg-soft px-6 py-16 text-center">
                    <BookOpen :size="36" class="mx-auto text-institutional" aria-hidden="true" />
                    <h2 class="mt-5 text-xl font-bold text-navy">{{ tr('Aucune offre académique publiée', 'No academic offering published') }}</h2>
                    <p class="mx-auto mt-2 max-w-lg leading-7 text-gray-600">
                        {{ tr('Les mentions et parcours seront publiés prochainement.', 'Subject areas and pathways will be published soon.') }}
                    </p>
                </div>
            </div>
        </main>
    </PublicLayout>
</template>

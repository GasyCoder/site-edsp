<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    BookOpen,
    ChevronRight,
    Clock3,
    GraduationCap,
} from 'lucide-vue-next';
import PublicLayout from '../../layouts/PublicLayout.vue';
import SeoHead from '../../components/public/SeoHead.vue';
import type { SeoData } from '../../types';
import type { Program } from '../../types';

type ProgramListItem = Program & {
    domain?: string | null;
    mention?: string | null;
    track?: string | null;
};

type Paginator<T> = {
    current_page: number;
    data: T[];
    from: number | null;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
    to: number | null;
    total: number;
};

defineProps<{ programs: Paginator<ProgramListItem>; seo?: SeoData }>();
</script>

<template>
    <SeoHead
        :title="seo?.title || 'Formations | EDSP'"
        :description="seo?.description || 'Découvrez les parcours de formation proposés par l’École de Droit et Science Politique de l’Université de Mahajanga.'"
        :canonical-url="seo?.canonical"
        :structured-data="seo?.schema"
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
                        <li class="font-semibold text-navy" aria-current="page">Formations</li>
                    </ol>
                </nav>

                <div class="max-w-3xl">
                    <p class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-edsp-green">
                        Formations
                    </p>
                    <h1 class="text-3xl font-extrabold leading-tight text-navy sm:text-4xl lg:text-5xl">
                        Nos parcours de formation
                    </h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-gray-600 sm:text-lg">
                        Explorez les formations publiées par l’EDSP et trouvez le parcours qui correspond à votre projet universitaire.
                    </p>
                </div>
            </div>
        </header>

        <div class="bg-white">
            <section class="mx-auto max-w-7xl px-6 py-16 sm:py-20" aria-labelledby="programs-heading">
                <div class="mb-9 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-edsp-green">
                            Catalogue
                        </p>
                        <h2 id="programs-heading" class="mt-2 text-2xl font-bold text-navy sm:text-3xl">
                            Formations disponibles
                        </h2>
                    </div>
                    <p v-if="programs.total" class="text-sm text-gray-500">
                        {{ programs.total }} formation{{ programs.total > 1 ? 's' : '' }} publiée{{ programs.total > 1 ? 's' : '' }}
                    </p>
                </div>

                <div v-if="programs.data.length" class="grid gap-6 md:grid-cols-2 xl:gap-8">
                    <article
                        v-for="program in programs.data"
                        :key="program.id"
                        class="group relative flex min-w-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-edsp-green/30 hover:shadow-xl sm:p-8"
                    >
                        <div class="absolute inset-y-0 left-0 w-1 bg-edsp-green" aria-hidden="true" />

                        <div class="flex items-start justify-between gap-5">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-institutional/10 text-institutional"
                                aria-hidden="true"
                            >
                                <GraduationCap :size="24" />
                            </div>
                            <span class="rounded-full bg-edsp-green/10 px-3 py-1 text-xs font-bold text-edsp-green">
                                {{ program.level }}
                            </span>
                        </div>

                        <div class="mt-6 flex-1">
                            <p
                                v-if="program.domain || program.mention"
                                class="mb-2 text-xs font-semibold uppercase tracking-[0.12em] text-gray-500"
                            >
                                {{ program.domain || program.mention }}
                            </p>
                            <h3 class="text-xl font-bold leading-snug text-navy sm:text-2xl">
                                <Link
                                    :href="`/formations/${program.slug}`"
                                    class="after:absolute after:inset-0 focus-visible:rounded"
                                >
                                    {{ program.title }}
                                </Link>
                            </h3>
                            <p class="mt-4 line-clamp-4 leading-7 text-gray-600">
                                {{ program.description }}
                            </p>
                        </div>

                        <div
                            v-if="program.duration || program.track"
                            class="mt-6 flex flex-wrap gap-x-5 gap-y-2 border-t border-gray-100 pt-5 text-sm text-gray-600"
                        >
                            <span v-if="program.duration" class="inline-flex items-center gap-2">
                                <Clock3 :size="16" class="text-edsp-green" aria-hidden="true" />
                                {{ program.duration }}
                            </span>
                            <span v-if="program.track" class="inline-flex items-center gap-2">
                                <BookOpen :size="16" class="text-edsp-green" aria-hidden="true" />
                                {{ program.track }}
                            </span>
                        </div>

                        <span class="mt-6 inline-flex items-center gap-2 font-heading text-sm font-semibold text-institutional">
                            Découvrir la formation
                            <ArrowRight
                                :size="17"
                                class="transition-transform group-hover:translate-x-1"
                                aria-hidden="true"
                            />
                        </span>
                    </article>
                </div>

                <div v-else class="rounded-xl border border-dashed border-gray-300 bg-soft px-6 py-16 text-center">
                    <BookOpen :size="36" class="mx-auto text-institutional" aria-hidden="true" />
                    <h3 class="mt-5 text-xl font-bold text-navy">Aucune formation publiée</h3>
                    <p class="mx-auto mt-2 max-w-lg leading-7 text-gray-600">
                        Le catalogue des formations sera mis à jour prochainement.
                    </p>
                </div>

                <nav
                    v-if="programs.last_page > 1"
                    class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-gray-200 pt-7 sm:flex-row"
                    aria-label="Pagination des formations"
                >
                    <component
                        :is="programs.prev_page_url ? Link : 'span'"
                        :href="programs.prev_page_url || undefined"
                        preserve-scroll
                        :aria-disabled="!programs.prev_page_url"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2.5 text-sm font-semibold text-navy transition"
                        :class="programs.prev_page_url ? 'hover:border-institutional hover:text-institutional' : 'cursor-not-allowed opacity-40'"
                    >
                        <ArrowLeft :size="16" aria-hidden="true" />
                        Précédent
                    </component>

                    <p class="text-sm text-gray-600" aria-live="polite">
                        Page <strong class="text-navy">{{ programs.current_page }}</strong> sur {{ programs.last_page }}
                    </p>

                    <component
                        :is="programs.next_page_url ? Link : 'span'"
                        :href="programs.next_page_url || undefined"
                        preserve-scroll
                        :aria-disabled="!programs.next_page_url"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2.5 text-sm font-semibold text-navy transition"
                        :class="programs.next_page_url ? 'hover:border-institutional hover:text-institutional' : 'cursor-not-allowed opacity-40'"
                    >
                        Suivant
                        <ArrowRight :size="16" aria-hidden="true" />
                    </component>
                </nav>
            </section>
        </div>
    </PublicLayout>
</template>

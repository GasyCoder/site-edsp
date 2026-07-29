<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ChevronDown,
    ChevronRight,
    HelpCircle,
    MessageCircleQuestion,
    Search,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import PublicLayout from '../../layouts/PublicLayout.vue';
import RichText from '../../components/public/RichText.vue';
import SeoHead from '../../components/public/SeoHead.vue';
import type { Faq, SeoData } from '../../types';
import { useI18n } from '../../lib/i18n';

const props = withDefaults(defineProps<{
    faqs?: Faq[];
    seo?: SeoData;
}>(), {
    faqs: () => [],
    seo: () => ({}),
});

const { tr } = useI18n();
const search = ref('');
const selectedCategory = ref('');

const plainText = (value: string): string => value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
const normalize = (value: string): string => value
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLocaleLowerCase();
const fallbackCategory = computed(() => tr('Informations générales', 'General information'));
const categories = computed(() => Array.from(new Set(
    props.faqs
        .map((faq) => faq.category?.trim())
        .filter((category): category is string => Boolean(category)),
)));
const filteredFaqs = computed(() => {
    const query = normalize(search.value.trim());

    return props.faqs.filter((faq) => {
        const matchesCategory = !selectedCategory.value || faq.category === selectedCategory.value;
        const haystack = normalize(`${faq.question} ${plainText(faq.answer)} ${faq.category || ''}`);

        return matchesCategory && (!query || haystack.includes(query));
    });
});
const groupedFaqs = computed(() => {
    const groups = new Map<string, Faq[]>();

    filteredFaqs.value.forEach((faq) => {
        const category = faq.category?.trim() || fallbackCategory.value;
        groups.set(category, [...(groups.get(category) || []), faq]);
    });

    return Array.from(groups, ([category, items]) => ({ category, items }));
});

const clearFilters = (): void => {
    search.value = '';
    selectedCategory.value = '';
};
</script>

<template>
    <SeoHead
        :title="seo.title || tr('Questions fréquentes | EDSP', 'Frequently asked questions | EDSP')"
        :description="seo.description || tr('Retrouvez les réponses aux questions fréquentes sur l’EDSP.', 'Find answers to frequently asked questions about EDSP.')"
        :canonical-url="seo.canonical"
        :structured-data="seo.schema"
    />

    <PublicLayout>
        <header class="public-page-hero bg-soft">
            <div class="mx-auto max-w-7xl">
                <nav :aria-label="tr('Fil d’Ariane', 'Breadcrumb')" class="mb-6">
                    <ol class="flex items-center gap-2 text-sm text-slate-500">
                        <li><Link href="/" class="transition hover:text-edsp-green">{{ tr('Accueil', 'Home') }}</Link></li>
                        <li aria-hidden="true"><ChevronRight :size="15" /></li>
                        <li class="font-semibold text-navy" aria-current="page">FAQ</li>
                    </ol>
                </nav>

                <div class="max-w-3xl">
                    <p class="section-eyebrow mb-3">{{ tr('Aide et informations', 'Help and information') }}</p>
                    <h1 class="page-title text-navy">{{ tr('Questions fréquentes', 'Frequently asked questions') }}</h1>
                    <p class="section-description max-w-2xl">
                        {{ tr(
                            'Des réponses simples aux questions les plus courantes sur les formations, les inscriptions et la vie à l’EDSP.',
                            'Clear answers to common questions about programmes, applications and student life at EDSP.',
                        ) }}
                    </p>
                </div>
            </div>
        </header>

        <section class="bg-white px-4 py-10 sm:px-6 sm:py-14 dark:bg-[#071126]" aria-labelledby="faq-list-title">
            <div class="mx-auto max-w-7xl">
                <div class="grid gap-5 border-b border-slate-200 pb-7 md:grid-cols-[minmax(0,1fr)_auto] md:items-end dark:border-white/10">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-edsp-green">
                            {{ tr('Centre d’aide', 'Help centre') }}
                        </p>
                        <h2 id="faq-list-title" class="mt-2 text-xl font-bold text-navy sm:text-2xl">
                            {{ tr('Trouvez rapidement votre réponse', 'Find your answer quickly') }}
                        </h2>
                    </div>

                    <label class="relative block w-full md:w-80">
                        <span class="sr-only">{{ tr('Rechercher dans la FAQ', 'Search the FAQ') }}</span>
                        <Search :size="17" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                        <input
                            v-model="search"
                            type="search"
                            class="form-control h-10.5 pl-10"
                            :placeholder="tr('Rechercher une question…', 'Search a question…')"
                        >
                    </label>
                </div>

                <div v-if="categories.length > 1" class="mt-5 flex flex-wrap gap-2" :aria-label="tr('Filtrer les questions', 'Filter questions')">
                    <button
                        type="button"
                        class="rounded-full border px-3.5 py-2 text-xs font-semibold transition"
                        :class="selectedCategory === '' ? 'border-edsp-green bg-edsp-green text-white' : 'border-slate-200 text-slate-600 hover:border-edsp-green hover:text-edsp-green dark:border-slate-700 dark:text-slate-300'"
                        @click="selectedCategory = ''"
                    >
                        {{ tr('Toutes', 'All') }}
                    </button>
                    <button
                        v-for="category in categories"
                        :key="category"
                        type="button"
                        class="rounded-full border px-3.5 py-2 text-xs font-semibold transition"
                        :class="selectedCategory === category ? 'border-edsp-green bg-edsp-green text-white' : 'border-slate-200 text-slate-600 hover:border-edsp-green hover:text-edsp-green dark:border-slate-700 dark:text-slate-300'"
                        @click="selectedCategory = category"
                    >
                        {{ category }}
                    </button>
                </div>

                <div v-if="groupedFaqs.length" class="mt-8 space-y-9">
                    <section
                        v-for="(group, groupIndex) in groupedFaqs"
                        :key="group.category"
                        :aria-labelledby="`faq-category-${groupIndex}`"
                    >
                        <div class="mb-3 flex items-center gap-3">
                            <span class="grid size-8 place-items-center rounded-md bg-edsp-green/10 text-edsp-green">
                                <HelpCircle :size="17" aria-hidden="true" />
                            </span>
                            <h2 :id="`faq-category-${groupIndex}`" class="text-base font-bold text-navy sm:text-lg">
                                {{ group.category }}
                            </h2>
                            <span class="text-xs text-slate-400">{{ group.items.length }}</span>
                        </div>

                        <div class="divide-y divide-slate-200 overflow-hidden rounded-lg border border-slate-200 bg-white dark:divide-white/10 dark:border-white/10 dark:bg-slate-900/40">
                            <details
                                v-for="(faq, faqIndex) in group.items"
                                :key="faq.id"
                                class="group"
                            >
                                <summary class="flex cursor-pointer list-none items-start gap-3 px-4 py-4 text-left transition hover:bg-slate-50 sm:px-5 [&::-webkit-details-marker]:hidden dark:hover:bg-white/[0.035]">
                                    <span class="mt-0.5 min-w-7 text-xs font-bold tabular-nums text-edsp-green">
                                        {{ String(faqIndex + 1).padStart(2, '0') }}
                                    </span>
                                    <span class="min-w-0 flex-1 text-sm font-semibold leading-6 text-navy sm:text-base">
                                        {{ faq.question }}
                                    </span>
                                    <ChevronDown
                                        :size="18"
                                        class="mt-0.5 flex-none text-slate-400 transition duration-200 group-open:rotate-180"
                                        aria-hidden="true"
                                    />
                                </summary>
                                <div class="border-t border-slate-100 px-4 py-4 pl-14 text-sm leading-7 text-slate-600 sm:px-5 sm:pl-16 dark:border-white/8 dark:text-slate-300">
                                    <RichText :html="faq.answer" />
                                </div>
                            </details>
                        </div>
                    </section>
                </div>

                <div v-else class="mt-8 rounded-lg border border-dashed border-slate-300 bg-soft px-5 py-10 text-center dark:border-slate-700">
                    <span class="mx-auto grid size-11 place-items-center rounded-lg border border-slate-200 bg-white text-edsp-green dark:border-slate-700 dark:bg-slate-900">
                        <MessageCircleQuestion :size="22" aria-hidden="true" />
                    </span>
                    <h2 class="mt-4 text-lg font-bold text-navy">
                        {{ props.faqs.length ? tr('Aucune réponse trouvée', 'No answer found') : tr('La FAQ sera bientôt disponible', 'The FAQ will be available soon') }}
                    </h2>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-600">
                        {{ props.faqs.length
                            ? tr('Essayez une autre recherche ou réinitialisez les filtres.', 'Try another search or reset the filters.')
                            : tr('Les questions et réponses seront publiées ici par l’administration.', 'Questions and answers will be published here by the administration.')
                        }}
                    </p>
                    <button v-if="search || selectedCategory" type="button" class="button-secondary mt-5 justify-center" @click="clearFilters">
                        <X :size="16" aria-hidden="true" />
                        {{ tr('Réinitialiser', 'Reset') }}
                    </button>
                </div>

                <aside class="mt-10 flex flex-col gap-4 rounded-lg border border-slate-200 bg-soft px-5 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                    <div>
                        <p class="font-heading text-sm font-bold text-navy">{{ tr('Vous ne trouvez pas votre réponse ?', 'Could not find your answer?') }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ tr('Écrivez-nous, l’équipe de l’EDSP vous orientera.', 'Contact us and the EDSP team will guide you.') }}</p>
                    </div>
                    <Link href="/contact" class="button-primary flex-none justify-center">
                        {{ tr('Nous contacter', 'Contact us') }}
                        <ChevronRight :size="16" aria-hidden="true" />
                    </Link>
                </aside>
            </div>
        </section>
    </PublicLayout>
</template>

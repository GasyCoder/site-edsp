<script setup lang="ts">
import { ArrowRight, Check, GraduationCap } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Section } from '../../types';
import { sectionAlignment, sectionContainerClass, sectionSetting } from './section-theme';
import SmartLink from './SmartLink.vue';
import { useI18n } from '../../lib/i18n';

const props = withDefaults(
    defineProps<{
        section?: Section | null;
    }>(),
    {
        section: null,
    },
);
const { tr } = useI18n();

const title = computed(() => props.section?.title || tr("Prêt à construire votre parcours à l’EDSP ?", 'Ready to start your journey at EDSP?'));
const content = computed(
    () =>
        props.section?.content ||
        tr("Explorez nos formations en droit et en science politique, puis préparez votre candidature avec toutes les informations utiles.", 'Explore our Law and Political Science programmes, then prepare your application with all the information you need.'),
);
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section, 'left'));
const secondaryButtonText = computed(() => sectionSetting(props.section, 'secondary_button_text', tr('Poser une question', 'Ask a question')));
const secondaryButtonUrl = computed(() => sectionSetting(props.section, 'secondary_button_url', '/contact'));
</script>

<template>
    <section class="relative overflow-hidden border-t border-slate-200/80 bg-white px-4 py-12 sm:px-6 sm:py-16">
        <div class="pointer-events-none absolute left-1/2 top-0 h-56 w-2/3 -translate-x-1/2 rounded-full bg-edsp-green/8 blur-3xl" aria-hidden="true" />
        <div
            :class="[
                container,
                'border-slate-200 bg-white text-navy shadow-[0_20px_50px_rgba(11,31,85,0.12)]',
            ]"
            class="relative mx-auto overflow-hidden rounded-3xl border px-6 py-9 sm:px-10 sm:py-11 lg:px-12"
        >
            <div
                class="pointer-events-none absolute -right-20 -top-24 size-72 rounded-full bg-edsp-green/8"
                aria-hidden="true"
            />

            <div class="relative grid items-center gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:gap-12">
                <div :class="alignment === 'center' ? 'text-center lg:text-left' : 'text-left'">
                    <div
                        class="mb-5 inline-flex size-11 items-center justify-center rounded-xl bg-edsp-green/10 text-edsp-green ring-1 ring-edsp-green/10"
                    >
                        <GraduationCap :size="23" aria-hidden="true" />
                    </div>
                    <h2 class="max-w-3xl text-balance text-2xl font-bold leading-tight sm:text-3xl lg:text-[2rem]">{{ title }}</h2>
                    <p class="mt-4 max-w-2xl text-pretty text-sm leading-7 text-slate-600 sm:text-base">
                        {{ content }}
                    </p>
                    <ul class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-600">
                        <li class="inline-flex items-center gap-2">
                            <Check :size="16" class="text-edsp-green" aria-hidden="true" />
                            {{ tr('Licence et Master', 'Bachelor’s and Master’s') }}
                        </li>
                        <li class="inline-flex items-center gap-2">
                            <Check :size="16" class="text-edsp-green" aria-hidden="true" />
                            {{ tr('Candidature accompagnée', 'Application support') }}
                        </li>
                    </ul>
                </div>

                <div class="flex min-w-56 flex-col gap-3 sm:flex-row lg:flex-col">
                    <SmartLink :href="section?.button_url || '/formations'" class="button-primary justify-center px-6">
                        {{ section?.button_text || tr('Voir les formations', 'View programmes') }}
                        <ArrowRight :size="17" aria-hidden="true" />
                    </SmartLink>
                    <SmartLink
                        :href="secondaryButtonUrl"
                        class="inline-flex items-center justify-center rounded-md border border-navy/20 px-6 py-3 font-heading text-sm font-semibold text-navy transition hover:border-edsp-green hover:text-edsp-green"
                    >
                        {{ secondaryButtonText }}
                    </SmartLink>
                </div>
            </div>
        </div>
    </section>
</template>

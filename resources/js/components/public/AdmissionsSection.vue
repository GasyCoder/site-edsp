<script setup lang="ts">
import { ArrowRight, Info, UserPlus } from 'lucide-vue-next';
import { computed } from 'vue';
import type { AdmissionCampaign, Section } from '../../types';
import { formatPublicDate } from '../../lib/public-content';
import SectionHeading from './SectionHeading.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass, sectionSetting } from './section-theme';
import SmartLink from './SmartLink.vue';
import { useI18n } from '../../lib/i18n';

const props = withDefaults(
    defineProps<{
        campaign?: AdmissionCampaign | null;
        section?: Section | null;
    }>(),
    {
        campaign: null,
        section: null,
    },
);
const { languageTag, tr } = useI18n();

const title = computed(() => props.section?.title || tr('Admissions et inscriptions', 'Admissions and applications'));
const eyebrow = computed(() => props.section?.subtitle || tr("Rejoindre l'EDSP", 'Join EDSP'));
const content = computed(
    () =>
        props.section?.content ||
        tr("Un processus simple, en quatre étapes, encadré par la Scolarité centrale de l'Université de Mahajanga.", 'A straightforward four-step process supported by the University of Mahajanga admissions office.'),
);
const closeDate = computed(() => formatPublicDate(props.campaign?.closes_at, languageTag.value));
const background = computed(() => sectionBackgroundClass(props.section, 'white'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section, 'center'));
const dark = computed(() => isDarkSection(props.section));
const steps = computed(() => [
    {
        description: sectionSetting(props.section, 'step_1_description', tr("Vérifiez les conditions d'accès au parcours visé dans l'avis officiel en vigueur.", 'Check the entry requirements for your chosen programme in the current official notice.')),
        title: sectionSetting(props.section, 'step_1_title', tr('Consulter les conditions', 'Check the requirements')),
    },
    {
        description: sectionSetting(props.section, 'step_2_description', tr('Rassemblez les documents indiqués pour la campagne et la formation choisies.', 'Gather the documents required for the admission round and your chosen programme.')),
        title: sectionSetting(props.section, 'step_2_title', tr('Préparer les pièces demandées', 'Prepare your documents')),
    },
    {
        description: sectionSetting(props.section, 'step_3_description', tr('Complétez soigneusement le formulaire d’inscription et vérifiez vos informations.', 'Complete the application form carefully and review your information.')),
        title: sectionSetting(props.section, 'step_3_title', tr('Déposer le dossier', 'Submit your application')),
    },
    {
        description: sectionSetting(props.section, 'step_4_description', tr('Conservez votre numéro de dossier et suivez les prochaines étapes communiquées.', 'Keep your application number and follow the next steps sent to you.')),
        title: sectionSetting(props.section, 'step_4_title', tr('Recevoir la confirmation', 'Receive confirmation')),
    },
]);
const infoText = computed(() => sectionSetting(props.section, 'info_text', tr('Les informations relatives aux inscriptions, calendriers et pièces à fournir sont publiées régulièrement sur le site.', 'Application dates, schedules and required documents are updated regularly on this website.')));
const campaignFallback = computed(() => sectionSetting(props.section, 'campaign_fallback_text', tr("Consultez l'avis officiel d’inscription en cours.", 'View the current official application notice.')));
const campaignLinkText = computed(() => sectionSetting(props.section, 'campaign_link_text', tr('Consulter les avis', 'View notices')));
const campaignLinkUrl = computed(() => sectionSetting(props.section, 'campaign_link_url', '/admissions'));
const secondaryButtonText = computed(() => sectionSetting(props.section, 'secondary_button_text', tr("Voir les conditions d'admission", 'View admission requirements')));
const secondaryButtonUrl = computed(() => sectionSetting(props.section, 'secondary_button_url', '/admissions'));
</script>

<template>
    <section id="admissions" :class="background" class="px-4 py-16 sm:px-6 sm:py-20">
        <div :class="container" class="mx-auto">
            <SectionHeading :eyebrow="eyebrow" :title="title" :description="content" :align="alignment" :dark="dark" />

            <ol class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <li v-for="(step, index) in steps" :key="index" class="admission-step">
                    <template v-if="index === 0">
                    <span class="admission-step-number bg-navy text-white">1</span>
                    </template>
                    <template v-else-if="index === 1">
                    <span class="admission-step-number bg-institutional text-white">2</span>
                    </template>
                    <template v-else-if="index === 2">
                    <span class="admission-step-number bg-edsp-green text-white">3</span>
                    </template>
                    <template v-else>
                    <span class="admission-step-number bg-gold text-navy">4</span>
                    </template>
                    <h3 class="mt-4 font-semibold text-navy">{{ step.title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ step.description }}</p>
                </li>
            </ol>

            <div class="mt-8 flex items-start gap-3 rounded-lg bg-soft px-5 py-4 text-sm leading-6 text-slate-700">
                <Info :size="18" class="mt-0.5 flex-none text-edsp-green" aria-hidden="true" />
                <p>{{ infoText }}</p>
            </div>

            <div
                class="mt-5 flex flex-col gap-4 rounded-lg bg-navy px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between sm:px-7"
            >
                <div class="flex items-start gap-3">
                    <span
                        :class="[
                            'mt-1.5 size-2.5 flex-none rounded-full ring-4',
                            campaign ? 'bg-edsp-green ring-edsp-green/20' : 'bg-gold ring-gold/20',
                        ]"
                        aria-hidden="true"
                    />
                    <div>
                        <p class="font-heading text-sm font-semibold sm:text-base">
                            {{ campaign ? campaign.title : campaignFallback }}
                        </p>
                        <p v-if="closeDate" class="mt-1 text-xs text-[#C9D4EE]">
                            {{ tr('Clôture prévue le', 'Closing date:') }} {{ closeDate }}<span v-if="languageTag === 'fr-FR'">.</span>
                        </p>
                    </div>
                </div>
                <SmartLink
                    :href="campaignLinkUrl"
                    class="inline-flex w-fit items-center gap-2 text-sm font-semibold text-gold transition hover:text-white"
                >
                    {{ campaignLinkText }}
                    <ArrowRight :size="16" aria-hidden="true" />
                </SmartLink>
            </div>

            <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row sm:flex-wrap">
                <SmartLink :href="secondaryButtonUrl" class="button-secondary justify-center">{{ secondaryButtonText }}</SmartLink>
                <SmartLink :href="section?.button_url || '/inscription'" class="button-primary justify-center">
                    <UserPlus :size="18" aria-hidden="true" />
                    {{ section?.button_text || tr('Commencer l’inscription', 'Start your application') }}
                </SmartLink>
            </div>
        </div>
    </section>
</template>

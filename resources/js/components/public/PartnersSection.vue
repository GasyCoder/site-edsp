<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Partner, Section } from '../../types';
import { mediaThumbnailUrl } from '../../lib/public-content';
import MediaPlaceholder from './MediaPlaceholder.vue';
import SectionHeading from './SectionHeading.vue';
import { isDarkSection, sectionAlignment, sectionBackgroundClass, sectionContainerClass, sectionSetting } from './section-theme';
import SmartLink from './SmartLink.vue';
import { useI18n } from '../../lib/i18n';

const props = defineProps<{
    partners: Partner[];
    section: Section;
}>();
const { tr } = useI18n();

const background = computed(() => sectionBackgroundClass(props.section, 'white'));
const container = computed(() => sectionContainerClass(props.section));
const alignment = computed(() => sectionAlignment(props.section, 'center'));
const dark = computed(() => isDarkSection(props.section));
const partnerLinkText = computed(() => sectionSetting(props.section, 'partner_link_text', tr('Découvrir le partenaire', 'Discover this partner')));
</script>

<template>
    <section :class="background" class="px-4 py-16 sm:px-6 sm:py-20">
        <div :class="container" class="mx-auto">
            <SectionHeading
                :eyebrow="section.subtitle"
                :title="section.title || tr('Nos partenaires', 'Our partners')"
                :description="section.content"
                :align="alignment"
                :dark="dark"
            />

            <div v-if="partners.length" class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <article v-for="partner in partners" :key="partner.id" class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="h-24 overflow-hidden rounded-lg border border-slate-100">
                        <MediaPlaceholder
                            :image-url="mediaThumbnailUrl(partner.logo) || partner.logo_url"
                            :alt="partner.logo?.alt_text || tr(`Logo ${partner.name}`, `${partner.name} logo`)"
                            :label="tr(`Logo ${partner.name}`, `${partner.name} logo`)"
                        />
                    </div>
                    <h3 class="mt-4 font-bold text-navy">{{ partner.name }}</h3>
                    <p v-if="partner.description" class="mt-2 text-sm leading-6 text-slate-600">{{ partner.description }}</p>
                    <SmartLink v-if="partner.url" :href="partner.url" class="mt-3 inline-flex text-sm font-semibold text-institutional hover:underline">
                        {{ partnerLinkText }}
                    </SmartLink>
                </article>
            </div>
            <div v-if="section.button_text && section.button_url" class="mt-8 flex" :class="alignment === 'center' ? 'justify-center' : 'justify-start'">
                <SmartLink :href="section.button_url" class="button-primary">
                    {{ section.button_text }}
                    <ArrowRight :size="17" aria-hidden="true" />
                </SmartLink>
            </div>
        </div>
    </section>
</template>

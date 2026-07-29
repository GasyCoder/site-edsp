<script setup lang="ts">
import { Camera, Quote } from 'lucide-vue-next';
import { computed, inject } from 'vue';
import { mediaUrl } from '../../lib/public-content';
import { useI18n } from '../../lib/i18n';
import type { Section } from '../../types';
import MediaPlaceholder from './MediaPlaceholder.vue';
import RichText from './RichText.vue';
import { sectionSetting } from './section-theme';
import { editSectionContextKey } from './edit-section-context';

const props = defineProps<{
    section: Section;
}>();

const { tr } = useI18n();
const editContext = inject(editSectionContextKey, null);
const editing = computed(() => editContext?.editing.value === true);
function numericSetting(key: string, fallback: number, minimum: number, maximum: number): number {
    const value = Number(props.section.settings?.[key] ?? fallback);

    return Number.isFinite(value) ? Math.min(maximum, Math.max(minimum, value)) : fallback;
}

const imageZoom = computed(() => numericSetting('image_zoom', 100, 50, 200));
const imagePosition = computed(() => `${numericSetting('image_position_x', 50, 0, 100)}% ${numericSetting('image_position_y', 50, 0, 100)}%`);
const image = computed(() => mediaUrl(props.section));
const position = computed(() => sectionSetting(props.section, 'director_position', tr('Directeur de l’EDSP', 'Director of EDSP')));
const signature = computed(() => sectionSetting(props.section, 'director_signature', tr('Avec tous mes encouragements,', 'With my very best wishes,')));
const imageAlt = computed(() => sectionSetting(props.section, 'alt_text', tr('Portrait du directeur de l’EDSP', 'Portrait of the Director of EDSP')));
</script>

<template>
    <section class="public-section border-b border-slate-200 bg-white" :aria-labelledby="`section-${section.id}`">
        <div class="mx-auto grid max-w-7xl items-start gap-9 lg:grid-cols-[21rem_minmax(0,1fr)] lg:gap-14 xl:grid-cols-[23rem_minmax(0,1fr)]">
            <aside class="relative mx-auto w-full max-w-md pb-6 pr-5 lg:sticky lg:top-32">
                <div class="pointer-events-none absolute inset-4 bottom-0 left-4 rounded-xl border border-dashed border-edsp-green/25 bg-edsp-green/[0.025] dark:border-emerald-400/30 dark:bg-emerald-400/5" aria-hidden="true" />
                <div class="relative h-[22rem] overflow-hidden rounded-xl border border-slate-200 bg-soft shadow-[0_10px_28px_rgba(11,31,85,0.09)] sm:h-[27rem] dark:border-slate-700">
                    <MediaPlaceholder
                        :image-url="image"
                        :alt="imageAlt"
                        fit="contain"
                        :label="imageAlt"
                        :object-position="imagePosition"
                        :scale="imageZoom"
                    />
                    <button
                        v-if="editing"
                        type="button"
                        class="absolute right-3 top-3 z-20 inline-flex items-center gap-2 rounded-lg border border-white/80 bg-white/95 px-3 py-2 text-xs font-bold text-navy shadow-lg transition hover:bg-navy hover:text-white"
                        aria-label="Changer le portrait du directeur"
                        title="Changer le portrait du directeur"
                        @click.stop="editContext?.openEditor('image_id')"
                    >
                        <Camera :size="16" aria-hidden="true" />
                        <span class="hidden sm:inline">Changer la photo</span>
                    </button>
                </div>
                <div class="absolute bottom-0 left-4 right-8 rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-md sm:left-6 dark:border-slate-700 dark:bg-slate-900">
                    <p class="font-heading text-base font-semibold text-navy">{{ section.title }}</p>
                    <p class="mt-1 text-sm font-semibold text-edsp-green dark:text-emerald-300">{{ position }}</p>
                </div>
            </aside>

            <article class="min-w-0 pt-1 lg:pt-4">
                <div class="flex size-12 items-center justify-center rounded-xl bg-gold/20 text-[#966807] dark:text-gold">
                    <Quote :size="24" aria-hidden="true" />
                </div>
                <p class="section-eyebrow mt-5 text-edsp-green dark:text-emerald-300">
                    {{ section.subtitle || tr('Mot du directeur', "Director's message") }}
                </p>
                <h2 :id="`section-${section.id}`" class="section-title mt-2 text-navy">
                    {{ section.title || 'Pr. Liva Jackson Raharinaivo' }}
                </h2>
                <p class="mt-2 font-heading text-sm font-semibold text-edsp-green dark:text-emerald-300">{{ position }}</p>
                <div class="mt-6 border-l-2 border-edsp-green/40 pl-5 sm:pl-6 dark:border-emerald-400/40">
                    <RichText v-if="section.content" :html="section.content" class="director-message text-[0.96rem] leading-7 text-slate-600 sm:text-base" />
                </div>

                <footer class="mt-8 border-t border-slate-200 pt-6 dark:border-slate-700">
                    <p class="text-sm italic text-slate-600">{{ signature }}</p>
                    <p class="mt-3 font-heading text-base font-semibold text-navy">{{ section.title }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ position }}</p>
                </footer>
            </article>
        </div>
    </section>
</template>

<style scoped>
.director-message :deep(p + p) {
    margin-top: 1.25rem;
}

.director-message :deep(p:first-child) {
    color: var(--color-navy);
    font-family: var(--font-heading);
    font-weight: 600;
}

:global(html.dark) .director-message :deep(p:first-child) {
    color: #f1f5f9;
}
</style>

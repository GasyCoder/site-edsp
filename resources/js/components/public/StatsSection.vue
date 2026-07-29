<script setup lang="ts">
import { Building2, UsersRound } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Program, Section } from '../../types';
import { sectionBackgroundClass, sectionContainerClass, sectionSetting } from './section-theme';
import { useI18n } from '../../lib/i18n';

const props = defineProps<{
    programs: Program[];
    section: Section;
}>();
const { tr } = useI18n();

const programCount = computed(() => props.programs.length || '—');
const levels = computed(() => {
    const values = [...new Set(props.programs.map((program) => program.level).filter(Boolean))];
    return values.length ? values.join(' · ') : tr('Licence · Master', 'Bachelor’s · Master’s');
});
const background = computed(() => {
    const configuredBackground = sectionBackgroundClass(props.section, 'white');

    return configuredBackground === 'bg-navy' ? 'bg-soft' : configuredBackground;
});
const container = computed(() => sectionContainerClass(props.section));
const labels = computed(() => [
    sectionSetting(props.section, 'stat_1_label', tr('Parcours de formation', 'Degree programmes')),
    sectionSetting(props.section, 'stat_2_label', tr('Niveaux universitaires', 'Degree levels')),
    sectionSetting(props.section, 'stat_3_label', tr('Une communauté universitaire dynamique', 'A vibrant university community')),
    sectionSetting(props.section, 'stat_4_label', tr('Université publique de Mahajanga', 'Public University of Mahajanga')),
]);
</script>

<template>
    <section
        :class="background"
        class="border-y border-slate-200 px-4 py-8 text-center sm:px-6 sm:py-10"
        :aria-label="tr('Chiffres clés', 'Key figures')"
    >
        <div
            class="mx-auto grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-slate-200 bg-slate-200 shadow-[0_8px_24px_rgba(11,31,85,0.05)] lg:grid-cols-4"
            :class="container"
        >
            <div class="flex min-h-28 flex-col items-center justify-center bg-white px-3 py-5 sm:min-h-32 sm:px-4">
                <p class="font-heading text-2xl font-bold text-navy sm:text-3xl">{{ programCount }}</p>
                <span class="mt-2 h-px w-6 bg-edsp-green/50" aria-hidden="true" />
                <p class="mt-2 text-xs leading-5 text-slate-600 sm:text-sm">{{ labels[0] }}</p>
            </div>
            <div class="flex min-h-28 flex-col items-center justify-center bg-white px-3 py-5 sm:min-h-32 sm:px-4">
                <p class="text-balance font-heading text-base font-bold text-navy sm:text-xl">{{ levels }}</p>
                <span class="mt-2 h-px w-6 bg-edsp-green/50" aria-hidden="true" />
                <p class="mt-2 text-xs leading-5 text-slate-600 sm:text-sm">{{ labels[1] }}</p>
            </div>
            <div class="flex min-h-28 flex-col items-center justify-center bg-white px-3 py-5 sm:min-h-32 sm:px-4">
                <span class="grid size-9 place-items-center rounded-lg bg-edsp-green/8 text-edsp-green">
                    <UsersRound :size="20" :stroke-width="1.8" aria-hidden="true" />
                </span>
                <p class="mt-2.5 text-xs leading-5 text-slate-600 sm:text-sm">{{ labels[2] }}</p>
            </div>
            <div class="flex min-h-28 flex-col items-center justify-center bg-white px-3 py-5 sm:min-h-32 sm:px-4">
                <span class="grid size-9 place-items-center rounded-lg bg-edsp-green/8 text-edsp-green">
                    <Building2 :size="20" :stroke-width="1.8" aria-hidden="true" />
                </span>
                <p class="mt-2.5 text-xs leading-5 text-slate-600 sm:text-sm">{{ labels[3] }}</p>
            </div>
        </div>
    </section>
</template>

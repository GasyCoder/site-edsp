<script setup lang="ts">
import { Building2, UsersRound } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Program, Section } from '../../types';
import { isDarkSection, sectionBackgroundClass, sectionContainerClass, sectionSetting } from './section-theme';
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
const background = computed(() => sectionBackgroundClass(props.section, 'blue'));
const container = computed(() => sectionContainerClass(props.section));
const dark = computed(() => isDarkSection(props.section, 'blue'));
const labels = computed(() => [
    sectionSetting(props.section, 'stat_1_label', tr('Parcours de formation', 'Degree programmes')),
    sectionSetting(props.section, 'stat_2_label', tr('Niveaux universitaires', 'Degree levels')),
    sectionSetting(props.section, 'stat_3_label', tr('Une communauté universitaire dynamique', 'A vibrant university community')),
    sectionSetting(props.section, 'stat_4_label', tr('Université publique de Mahajanga', 'Public University of Mahajanga')),
]);
</script>

<template>
    <section :class="background" class="px-4 py-12 text-center sm:px-6 sm:py-14" :aria-label="tr('Chiffres clés', 'Key figures')">
        <div
            class="mx-auto grid gap-px overflow-hidden rounded-lg border sm:grid-cols-2 lg:grid-cols-4"
            :class="[container, dark ? 'border-white/15 bg-white/15' : 'border-slate-200 bg-slate-200']"
        >
            <div class="px-4 py-6" :class="dark ? 'bg-navy' : 'bg-white'">
                <p class="font-heading text-3xl font-bold" :class="dark ? 'text-gold' : 'text-navy'">{{ programCount }}</p>
                <p class="mt-2 text-sm" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ labels[0] }}</p>
            </div>
            <div class="px-4 py-6" :class="dark ? 'bg-navy' : 'bg-white'">
                <p class="text-balance font-heading text-xl font-bold" :class="dark ? 'text-gold' : 'text-navy'">{{ levels }}</p>
                <p class="mt-2 text-sm" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ labels[1] }}</p>
            </div>
            <div class="px-4 py-6" :class="dark ? 'bg-navy' : 'bg-white'">
                <UsersRound class="mx-auto" :class="dark ? 'text-gold' : 'text-edsp-green'" :size="32" :stroke-width="1.7" aria-hidden="true" />
                <p class="mt-3 text-sm" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ labels[2] }}</p>
            </div>
            <div class="px-4 py-6" :class="dark ? 'bg-navy' : 'bg-white'">
                <Building2 class="mx-auto" :class="dark ? 'text-gold' : 'text-edsp-green'" :size="32" :stroke-width="1.7" aria-hidden="true" />
                <p class="mt-3 text-sm" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ labels[3] }}</p>
            </div>
        </div>
    </section>
</template>

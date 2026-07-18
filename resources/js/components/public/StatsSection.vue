<script setup lang="ts">
import { Building2, UsersRound } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Program, Section } from '../../types';
import { isDarkSection, sectionBackgroundClass, sectionContainerClass, sectionSetting } from './section-theme';

const props = defineProps<{
    programs: Program[];
    section: Section;
}>();

const programCount = computed(() => props.programs.length || '—');
const levels = computed(() => {
    const values = [...new Set(props.programs.map((program) => program.level).filter(Boolean))];
    return values.length ? values.join(' · ') : 'Licence · Master';
});
const background = computed(() => sectionBackgroundClass(props.section, 'blue'));
const container = computed(() => sectionContainerClass(props.section));
const dark = computed(() => isDarkSection(props.section, 'blue'));
const labels = computed(() => [
    sectionSetting(props.section, 'stat_1_label', 'Parcours de formation'),
    sectionSetting(props.section, 'stat_2_label', 'Niveaux universitaires'),
    sectionSetting(props.section, 'stat_3_label', 'Une communauté universitaire dynamique'),
    sectionSetting(props.section, 'stat_4_label', 'Université publique de Mahajanga'),
]);
</script>

<template>
    <section :class="background" class="px-4 py-12 text-center sm:px-6 sm:py-14" aria-label="Chiffres clés">
        <div :class="container" class="mx-auto grid gap-9 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="font-heading text-4xl font-extrabold" :class="dark ? 'text-gold' : 'text-navy'">{{ programCount }}</p>
                <p class="mt-2 text-sm" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ labels[0] }}</p>
            </div>
            <div>
                <p class="text-balance font-heading text-2xl font-extrabold" :class="dark ? 'text-gold' : 'text-navy'">{{ levels }}</p>
                <p class="mt-2 text-sm" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ labels[1] }}</p>
            </div>
            <div>
                <UsersRound class="mx-auto" :class="dark ? 'text-gold' : 'text-edsp-green'" :size="40" :stroke-width="1.7" aria-hidden="true" />
                <p class="mt-3 text-sm" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ labels[2] }}</p>
            </div>
            <div>
                <Building2 class="mx-auto" :class="dark ? 'text-gold' : 'text-edsp-green'" :size="40" :stroke-width="1.7" aria-hidden="true" />
                <p class="mt-3 text-sm" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">{{ labels[3] }}</p>
            </div>
        </div>
    </section>
</template>

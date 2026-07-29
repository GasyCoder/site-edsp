<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, GraduationCap, Landmark, Scale } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Program } from '../../types';
import { useI18n } from '../../lib/i18n';
import { programLevelLabel } from '../../lib/academic-offer';

const props = defineProps<{
    program: Program;
}>();
const { tr } = useI18n();

const levelLabel = computed(() => programLevelLabel(props.program));

const mentionLabel = computed(() => props.program.mention_record?.nom || props.program.domain || props.program.mention);

const icon = computed(() => {
    const subject = `${props.program.slug} ${props.program.domain ?? ''}`.toLocaleLowerCase('fr');

    if (subject.includes('droit')) {
        return Scale;
    }

    if (subject.includes('polit')) {
        return Landmark;
    }

    return GraduationCap;
});
</script>

<template>
    <article
        class="group surface-card flex h-full flex-col p-5 transition-colors hover:border-edsp-green/35 sm:p-7"
    >
        <span class="mb-4 grid size-11 place-items-center rounded-lg bg-institutional/10 text-institutional">
            <component :is="icon" :size="23" :stroke-width="1.8" aria-hidden="true" />
        </span>
        <p class="text-sm font-bold uppercase tracking-wide text-edsp-green">{{ levelLabel }}</p>
        <h3 class="mt-2 text-xl font-bold text-navy sm:text-[1.35rem]">{{ program.title }}</h3>
        <p class="mt-3 flex-1 text-[0.95rem] leading-7 text-slate-600">{{ program.description }}</p>
        <dl v-if="program.duration || mentionLabel" class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm">
            <div v-if="program.duration">
                <dt class="sr-only">{{ tr('Durée', 'Duration') }}</dt>
                <dd class="font-semibold text-slate-700">{{ program.duration }}</dd>
            </div>
            <div v-if="mentionLabel">
                <dt class="sr-only">{{ tr('Mention', 'Subject area') }}</dt>
                <dd class="text-slate-500">{{ mentionLabel }}</dd>
            </div>
        </dl>
        <Link
            :href="`/formations/${program.slug}`"
            class="mt-7 inline-flex w-fit items-center gap-2 font-heading text-sm font-semibold text-institutional transition group-hover:text-edsp-green"
        >
            {{ tr('Découvrir le parcours', 'Explore this programme') }}
            <ArrowRight :size="17" aria-hidden="true" />
        </Link>
    </article>
</template>

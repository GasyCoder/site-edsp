<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, GraduationCap, Landmark, Scale } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Program } from '../../types';

const props = defineProps<{
    program: Program;
}>();

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
        class="group flex h-full flex-col rounded-xl border border-slate-200 bg-white p-7 shadow-[0_3px_12px_rgba(11,31,85,0.04)] transition hover:-translate-y-1 hover:shadow-[0_16px_36px_rgba(11,31,85,0.1)] sm:p-9"
    >
        <span class="mb-5 grid size-14 place-items-center rounded-xl bg-institutional/10 text-institutional">
            <component :is="icon" :size="28" :stroke-width="1.8" aria-hidden="true" />
        </span>
        <p class="text-sm font-bold uppercase tracking-wide text-edsp-green">{{ program.level }}</p>
        <h3 class="mt-2 text-2xl font-bold text-navy">{{ program.title }}</h3>
        <p class="mt-4 flex-1 leading-7 text-slate-600">{{ program.description }}</p>
        <dl v-if="program.duration || program.domain" class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm">
            <div v-if="program.duration">
                <dt class="sr-only">Durée</dt>
                <dd class="font-semibold text-slate-700">{{ program.duration }}</dd>
            </div>
            <div v-if="program.domain">
                <dt class="sr-only">Domaine</dt>
                <dd class="text-slate-500">{{ program.domain }}</dd>
            </div>
        </dl>
        <Link
            :href="`/formations/${program.slug}`"
            class="mt-7 inline-flex w-fit items-center gap-2 font-heading text-sm font-semibold text-institutional transition group-hover:text-edsp-green"
        >
            Découvrir le parcours
            <ArrowRight :size="17" aria-hidden="true" />
        </Link>
    </article>
</template>

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
const showMention = computed(() => {
    const mention = mentionLabel.value?.trim().toLocaleLowerCase('fr');
    const title = props.program.title.trim().toLocaleLowerCase('fr');

    return Boolean(mention && mention !== title);
});

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
        class="group surface-card flex h-full flex-col p-4 transition-[border-color,box-shadow] hover:border-edsp-green/30 hover:shadow-[0_10px_22px_rgba(11,31,85,0.06)] sm:p-5"
    >
        <div class="flex items-start gap-3.5">
            <span class="grid size-10 flex-none place-items-center rounded-lg bg-institutional/8 text-institutional">
                <component :is="icon" :size="20" :stroke-width="1.8" aria-hidden="true" />
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-edsp-green sm:text-xs">{{ levelLabel }}</p>
                <h3 class="mt-1 text-lg font-bold leading-tight text-navy sm:text-xl">{{ program.title }}</h3>
            </div>
        </div>

        <p class="mt-3 line-clamp-3 flex-1 text-sm leading-6 text-slate-600 sm:line-clamp-2">{{ program.description }}</p>

        <div class="mt-4 flex flex-wrap items-end justify-between gap-x-5 gap-y-3 border-t border-slate-200 pt-3.5">
            <dl v-if="program.duration || showMention" class="flex flex-wrap gap-x-5 gap-y-1 text-xs sm:text-sm">
                <div v-if="program.duration">
                    <dt class="sr-only">{{ tr('Durée', 'Duration') }}</dt>
                    <dd class="font-semibold text-slate-700">{{ program.duration }}</dd>
                </div>
                <div v-if="showMention">
                    <dt class="sr-only">{{ tr('Mention', 'Subject area') }}</dt>
                    <dd class="text-slate-500">{{ mentionLabel }}</dd>
                </div>
            </dl>
            <Link
                :href="`/formations/${program.slug}`"
                class="inline-flex w-fit items-center gap-1.5 font-heading text-xs font-semibold text-institutional transition group-hover:text-edsp-green sm:text-sm"
            >
                {{ tr('Découvrir le parcours', 'Explore this programme') }}
                <ArrowRight :size="15" aria-hidden="true" />
            </Link>
        </div>
    </article>
</template>

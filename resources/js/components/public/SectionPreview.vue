<script setup lang="ts">
import { computed } from 'vue';
import type { EditableSectionPayload } from './SectionFormRenderer.vue';

const props = defineProps<{
    modelValue: EditableSectionPayload;
}>();

const widthLabel = computed(() => ({
    default: 'Standard',
    narrow: 'Étroite',
    wide: 'Large',
}[props.modelValue.settings.container || 'default']));
</script>

<template>
    <div
        :class="[
            modelValue.settings.background === 'blue' ? 'bg-navy text-white' : modelValue.settings.background === 'white' ? 'bg-white' : 'bg-soft',
            modelValue.settings.alignment === 'center' ? 'text-center' : 'text-left',
        ]"
        class="rounded-xl border border-slate-200 p-5"
    >
        <p class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500">Aperçu du contenu</p>
        <p v-if="modelValue.subtitle" class="text-xs font-bold uppercase tracking-wider text-edsp-green">
            {{ modelValue.subtitle }}
        </p>
        <h3 v-if="modelValue.title" class="mt-1 text-xl font-bold" :class="modelValue.settings.background === 'blue' ? 'text-white' : 'text-navy'">{{ modelValue.title }}</h3>
        <p v-if="modelValue.content" class="mt-3 line-clamp-4 whitespace-pre-line text-sm leading-6" :class="modelValue.settings.background === 'blue' ? 'text-blue-100' : 'text-slate-600'">
            {{ modelValue.content }}
        </p>
        <span
            v-if="modelValue.button_text"
            class="mt-4 inline-flex rounded-md bg-edsp-green px-4 py-2 text-xs font-semibold text-white"
        >
            {{ modelValue.button_text }}
        </span>
        <div class="mt-4 flex flex-wrap gap-2 text-xs opacity-70">
            <span>Position : {{ modelValue.position }}</span>
            <span aria-hidden="true">·</span>
            <span>Largeur : {{ widthLabel }}</span>
        </div>
    </div>
</template>

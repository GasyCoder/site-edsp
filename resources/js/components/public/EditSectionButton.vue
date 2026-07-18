<script setup lang="ts">
import { Pencil } from 'lucide-vue-next';

defineProps<{
    label: string;
}>();

defineEmits<{
    click: [];
}>();
</script>

<template>
    <button
        type="button"
        class="absolute right-3 top-3 z-30 grid size-11 place-items-center rounded-full border border-navy/10 bg-white text-navy shadow-[0_8px_24px_rgba(11,31,85,0.16)] ring-2 ring-white transition hover:scale-105 hover:bg-navy hover:text-white"
        :aria-label="label"
        @click="$emit('click')"
    >
        <Pencil :size="17" aria-hidden="true" />
    </button>
</template>

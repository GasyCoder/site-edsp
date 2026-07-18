<script setup lang="ts">
import { EyeOff, Pencil } from 'lucide-vue-next';

defineProps<{
    active: boolean;
}>();

defineEmits<{
    toggle: [];
}>();
</script>

<template>
    <button
        type="button"
        class="fixed bottom-[calc(5.25rem+env(safe-area-inset-bottom))] left-4 z-40 inline-flex max-w-[calc(100vw-5.5rem)] items-center gap-2 rounded-full bg-navy px-4 py-3 font-heading text-xs font-semibold text-white shadow-[0_12px_36px_rgba(11,31,85,0.32)] transition hover:bg-institutional min-[1280px]:bottom-5 min-[1280px]:left-6 min-[1280px]:max-w-none min-[1280px]:px-5 min-[1280px]:text-sm"
        :aria-pressed="active"
        @click="$emit('toggle')"
    >
        <EyeOff v-if="active" :size="18" aria-hidden="true" />
        <Pencil v-else :size="18" aria-hidden="true" />
        {{ active ? 'Quitter le mode édition' : 'Activer le mode édition' }}
    </button>
</template>

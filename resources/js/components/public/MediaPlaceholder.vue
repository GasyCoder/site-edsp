<script setup lang="ts">
import { ImageIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        alt?: string | null;
        eager?: boolean;
        fit?: 'contain' | 'cover';
        imageUrl?: string | null;
        label: string;
        objectPosition?: string;
        scale?: number;
    }>(),
    {
        alt: null,
        eager: false,
        fit: 'cover',
        imageUrl: null,
        objectPosition: 'center',
        scale: 100,
    },
);

const failed = ref(false);

watch(
    () => props.imageUrl,
    () => {
        failed.value = false;
    },
);
</script>

<template>
    <img
        v-if="imageUrl && !failed"
        :src="imageUrl"
        :alt="alt || label"
        class="h-full w-full bg-slate-100 dark:bg-slate-900"
        :class="fit === 'contain' ? 'object-contain' : 'object-cover'"
        :style="{
            objectPosition,
            transform: `scale(${Math.min(200, Math.max(50, scale)) / 100})`,
            transformOrigin: objectPosition,
        }"
        :loading="eager ? 'eager' : 'lazy'"
        :fetchpriority="eager ? 'high' : 'auto'"
        decoding="async"
        @error="failed = true"
    />
    <div
        v-else
        class="flex h-full w-full flex-col items-center justify-center gap-3 bg-gradient-to-br from-slate-50 to-slate-200 px-4 text-center text-sm text-slate-500 dark:from-slate-800 dark:to-slate-900 dark:text-slate-300"
        role="img"
        :aria-label="label"
    >
        <span class="grid size-12 place-items-center rounded-full bg-white/80 text-institutional shadow-sm">
            <ImageIcon :size="22" aria-hidden="true" />
        </span>
        <span>{{ label }}</span>
    </div>
</template>

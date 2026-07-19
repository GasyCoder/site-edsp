<script setup lang="ts">
import { ImageIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        alt?: string | null;
        eager?: boolean;
        imageUrl?: string | null;
        label: string;
        objectPosition?: string;
    }>(),
    {
        alt: null,
        eager: false,
        imageUrl: null,
        objectPosition: 'center',
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
        class="h-full w-full object-cover"
        :style="{ objectPosition }"
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

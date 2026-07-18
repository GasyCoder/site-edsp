<script setup lang="ts">
withDefaults(
    defineProps<{
        align?: 'center' | 'left';
        dark?: boolean;
        description?: string | null;
        eyebrow?: string | null;
        title: string;
    }>(),
    {
        align: 'center',
        dark: false,
        description: null,
        eyebrow: null,
    },
);
</script>

<template>
    <div :class="align === 'center' ? 'mx-auto max-w-3xl text-center' : 'max-w-3xl text-left'">
        <p
            v-if="eyebrow"
            class="mb-2 text-xs font-bold uppercase tracking-[0.13em] sm:text-sm"
            :class="dark ? 'text-gold' : 'text-edsp-green'"
        >
            {{ eyebrow }}
        </p>
        <h2 class="text-balance text-3xl font-bold leading-tight sm:text-4xl" :class="dark ? 'text-white' : 'text-navy'">
            {{ title }}
        </h2>
        <p v-if="description" class="mt-4 text-pretty leading-7" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">
            {{ description }}
        </p>
        <slot />
    </div>
</template>

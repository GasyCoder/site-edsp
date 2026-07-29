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
            class="section-eyebrow mb-2"
            :class="dark ? 'text-gold' : 'text-edsp-green'"
        >
            {{ eyebrow }}
        </p>
        <h2 class="section-title" :class="dark ? 'text-white' : 'text-navy'">
            {{ title }}
        </h2>
        <p v-if="description" class="section-description" :class="dark ? 'text-[#C9D4EE]' : 'text-slate-600'">
            {{ description }}
        </p>
        <slot />
    </div>
</template>

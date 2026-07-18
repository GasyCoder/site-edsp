<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { safePublicUrl } from '../../lib/public-content';

const props = defineProps<{
    href: string;
}>();

const safeHref = computed(() => safePublicUrl(props.href));
const external = computed(() => /^https?:\/\//i.test(safeHref.value ?? ''));
</script>

<template>
    <span v-if="!safeHref">
        <slot />
    </span>
    <a v-else-if="external" :href="safeHref" target="_blank" rel="noopener noreferrer">
        <slot />
    </a>
    <Link v-else :href="safeHref">
        <slot />
    </Link>
</template>

<script setup lang="ts">
defineProps<{
    html: string;
}>();
</script>

<template>
    <!-- All rich text is sanitized by Laravel before persistence and again at public serialization. -->
    <div class="rich-text" v-html="html" />
</template>

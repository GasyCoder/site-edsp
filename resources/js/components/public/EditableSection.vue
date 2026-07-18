<script setup lang="ts">
import { computed, ref } from 'vue';
import type { Section } from '../../types';
import EditSectionButton from './EditSectionButton.vue';
import EditSectionDrawer from './EditSectionDrawer.vue';

const props = withDefaults(
    defineProps<{
        editing: boolean;
        section?: Section | null;
    }>(),
    {
        section: null,
    },
);

const drawerOpen = ref(false);
const shouldRender = computed(
    () => !props.section || props.section.is_visible || props.editing,
);
const isHidden = computed(() => Boolean(props.section && !props.section.is_visible));
</script>

<template>
    <div
        v-if="shouldRender"
        class="relative"
        :class="editing ? (isHidden ? 'outline-2 outline-dashed outline-offset-[-2px] outline-amber-500' : 'outline outline-1 outline-offset-[-1px] outline-edsp-green/20') : ''"
    >
        <EditSectionButton
            v-if="editing && section"
            :label="`Modifier la section ${section.title || section.section_key}`"
            @click="drawerOpen = true"
        />
        <span
            v-if="isHidden && editing"
            class="pointer-events-none absolute left-3 top-3 z-20 rounded bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900"
        >
            Section masquée
        </span>
        <slot />
        <EditSectionDrawer
            v-if="section"
            :open="drawerOpen"
            :section="section"
            @close="drawerOpen = false"
        />
    </div>
</template>

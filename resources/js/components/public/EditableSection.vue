<script setup lang="ts">
import { computed, provide, ref } from 'vue';
import type { Section } from '../../types';
import EditSectionButton from './EditSectionButton.vue';
import EditSectionDrawer from './EditSectionDrawer.vue';
import { editSectionContextKey } from './edit-section-context';

const props = withDefaults(
    defineProps<{
        editing: boolean;
        relatedSection?: Section | null;
        section?: Section | null;
    }>(),
    {
        relatedSection: null,
        section: null,
    },
);

const drawerOpen = ref(false);
const relatedDrawerOpen = ref(false);
const focusedField = ref<string | null>(null);
const editingState = computed(() => props.editing);
const shouldRender = computed(
    () => !props.section || props.section.is_visible || props.editing,
);
const isHidden = computed(() => Boolean(props.section && !props.section.is_visible));

function openEditor(fieldKey?: string): void {
    focusedField.value = fieldKey ?? null;
    drawerOpen.value = true;
}

function openRelatedEditor(fieldKey?: string): void {
    if (!props.relatedSection) {
        return;
    }

    focusedField.value = fieldKey ?? null;
    relatedDrawerOpen.value = true;
}

provide(editSectionContextKey, {
    editing: editingState,
    openEditor,
    openRelatedEditor,
});
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
            @click="openEditor()"
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
            :focus-field="focusedField"
            @close="drawerOpen = false"
        />
        <EditSectionDrawer
            v-if="relatedSection"
            :open="relatedDrawerOpen"
            :section="relatedSection"
            :focus-field="focusedField"
            @close="relatedDrawerOpen = false"
        />
    </div>
</template>

<script setup lang="ts">
import { Bold, Italic, List, ListOrdered, Redo2, RemoveFormatting, Undo2 } from 'lucide-vue-next';
import { nextTick, onMounted, ref, watch } from 'vue';

const props = withDefaults(defineProps<{
    modelValue?: string | null;
}>(), {
    modelValue: null,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | null];
}>();

const editor = ref<HTMLDivElement | null>(null);
const focused = ref(false);

function synchronize(): void {
    if (!editor.value || focused.value) return;

    const value = props.modelValue ?? '';
    if (editor.value.innerHTML !== value) editor.value.innerHTML = value;
}

function emitContent(): void {
    const value = editor.value?.innerHTML.trim() ?? '';
    emit('update:modelValue', value === '' || value === '<br>' ? null : value);
}

function execute(command: string): void {
    editor.value?.focus();
    document.execCommand(command, false);
    emitContent();
}

watch(() => props.modelValue, () => nextTick(synchronize));
onMounted(synchronize);
</script>

<template>
    <div class="mt-1.5 overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm focus-within:border-institutional focus-within:ring-2 focus-within:ring-institutional/10">
        <div class="flex flex-wrap items-center gap-1 border-b border-slate-200 bg-slate-50 px-2 py-2" role="toolbar" aria-label="Mise en forme du message">
            <button type="button" class="editor-tool" title="Gras" aria-label="Gras" @mousedown.prevent @click="execute('bold')"><Bold :size="17" aria-hidden="true" /></button>
            <button type="button" class="editor-tool" title="Italique" aria-label="Italique" @mousedown.prevent @click="execute('italic')"><Italic :size="17" aria-hidden="true" /></button>
            <span class="mx-1 h-6 w-px bg-slate-200" aria-hidden="true" />
            <button type="button" class="editor-tool" title="Liste à puces" aria-label="Liste à puces" @mousedown.prevent @click="execute('insertUnorderedList')"><List :size="18" aria-hidden="true" /></button>
            <button type="button" class="editor-tool" title="Liste numérotée" aria-label="Liste numérotée" @mousedown.prevent @click="execute('insertOrderedList')"><ListOrdered :size="18" aria-hidden="true" /></button>
            <span class="mx-1 h-6 w-px bg-slate-200" aria-hidden="true" />
            <button type="button" class="editor-tool" title="Annuler" aria-label="Annuler la dernière modification" @mousedown.prevent @click="execute('undo')"><Undo2 :size="17" aria-hidden="true" /></button>
            <button type="button" class="editor-tool" title="Rétablir" aria-label="Rétablir la modification" @mousedown.prevent @click="execute('redo')"><Redo2 :size="17" aria-hidden="true" /></button>
            <button type="button" class="editor-tool" title="Retirer la mise en forme" aria-label="Retirer la mise en forme" @mousedown.prevent @click="execute('removeFormat')"><RemoveFormatting :size="17" aria-hidden="true" /></button>
        </div>
        <div
            ref="editor"
            contenteditable="true"
            role="textbox"
            aria-multiline="true"
            class="rich-text-input min-h-72 px-4 py-3 text-base leading-7 text-slate-700 outline-none"
            @focus="focused = true"
            @blur="focused = false; emitContent()"
            @input="emitContent"
        />
    </div>
</template>

<style scoped>
.editor-tool {
    display: grid;
    width: 2.25rem;
    height: 2.25rem;
    place-items: center;
    border-radius: 0.375rem;
    color: #475569;
    transition: background-color 150ms ease, color 150ms ease;
}

.editor-tool:hover,
.editor-tool:focus-visible {
    background: white;
    color: var(--color-institutional);
    outline: none;
    box-shadow: 0 0 0 2px rgb(21 58 138 / 10%);
}

.rich-text-input :deep(p + p),
.rich-text-input :deep(ul + p),
.rich-text-input :deep(ol + p) {
    margin-top: 0.85rem;
}

.rich-text-input :deep(ul) {
    list-style: disc;
    padding-left: 1.5rem;
}

.rich-text-input :deep(ol) {
    list-style: decimal;
    padding-left: 1.5rem;
}
</style>

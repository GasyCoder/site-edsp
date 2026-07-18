<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { LoaderCircle, Save, X } from 'lucide-vue-next';
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import type { Section } from '../../types';
import SectionFormRenderer, { type EditableSectionPayload } from './SectionFormRenderer.vue';
import SectionPreview from './SectionPreview.vue';

const props = defineProps<{
    open: boolean;
    section: Section;
}>();

const emit = defineEmits<{
    close: [];
    saved: [];
}>();

const drawer = ref<HTMLElement | null>(null);
const closeButton = ref<HTMLButtonElement | null>(null);
const errors = ref<Record<string, string>>({});
const processing = ref(false);
const previousOverflow = ref('');
const returnFocus = ref<HTMLElement | null>(null);

function payloadFromSection(section: Section): EditableSectionPayload {
    return {
        button_text: section.button_text,
        button_url: section.button_url,
        content: section.content,
        image_id: section.image_id ?? null,
        is_visible: section.is_visible,
        position: section.position ?? 0,
        settings: { ...(section.settings ?? {}) },
        subtitle: section.subtitle,
        title: section.title,
    };
}

const draft = ref<EditableSectionPayload>(payloadFromSection(props.section));

watch(
    () => [props.open, props.section] as const,
    async ([open]) => {
        if (open) {
            returnFocus.value = document.activeElement as HTMLElement | null;
            draft.value = payloadFromSection(props.section);
            errors.value = {};
            previousOverflow.value = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
            await nextTick();
            closeButton.value?.focus();
        } else {
            document.body.style.overflow = previousOverflow.value;
            await nextTick();
            returnFocus.value?.focus();
        }
    },
    { deep: true },
);

function close() {
    if (!processing.value) {
        emit('close');
    }
}

function save() {
    processing.value = true;
    errors.value = {};

    router.patch(`/edition/sections/${props.section.id}`, draft.value, {
        onError: (validationErrors) => {
            errors.value = validationErrors;
        },
        onFinish: () => {
            processing.value = false;
        },
        onSuccess: () => {
            emit('saved');
            emit('close');
        },
        preserveScroll: true,
    });
}

function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        event.preventDefault();
        close();
        return;
    }

    if (event.key !== 'Tab' || !drawer.value) {
        return;
    }

    const focusable = Array.from(
        drawer.value.querySelectorAll<HTMLElement>(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
        ),
    );

    if (focusable.length === 0) {
        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}

onBeforeUnmount(() => {
    document.body.style.overflow = previousOverflow.value;
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-[90] bg-navy/55 backdrop-blur-[1px]"
                @mousedown.self="close"
            >
                <aside
                    ref="drawer"
                    class="ml-auto flex h-full w-full max-w-2xl flex-col bg-soft shadow-2xl"
                    role="dialog"
                    aria-modal="true"
                    :aria-labelledby="`edit-section-title-${section.id}`"
                    @keydown="handleKeydown"
                >
                    <div class="flex items-start justify-between gap-5 border-b border-slate-200 bg-white px-5 py-5 sm:px-7">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-edsp-green">Édition visuelle</p>
                            <h2 :id="`edit-section-title-${section.id}`" class="mt-1 text-xl font-bold text-navy">
                                {{ section.title || section.section_key }}
                            </h2>
                            <p class="mt-1 text-xs text-slate-500">Type : {{ section.section_type }}</p>
                        </div>
                        <button
                            ref="closeButton"
                            type="button"
                            class="grid size-10 flex-none place-items-center rounded-full text-slate-600 transition hover:bg-soft hover:text-navy"
                            aria-label="Fermer le panneau d'édition"
                            @click="close"
                        >
                            <X :size="22" aria-hidden="true" />
                        </button>
                    </div>

                    <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="save">
                        <div class="flex-1 space-y-7 overflow-y-auto px-5 py-6 sm:px-7">
                            <SectionPreview :model-value="draft" />
                            <SectionFormRenderer
                                v-model="draft"
                                :section="section"
                                :errors="errors"
                            />
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:px-7">
                            <p class="hidden text-xs text-slate-500 sm:block">Les modifications sont publiées après enregistrement.</p>
                            <div class="flex flex-wrap justify-end gap-3">
                            <button type="button" class="button-secondary" :disabled="processing" @click="close">
                                Annuler
                            </button>
                            <button type="submit" class="button-primary" :disabled="processing">
                                <LoaderCircle
                                    v-if="processing"
                                    :size="18"
                                    class="animate-spin"
                                    aria-hidden="true"
                                />
                                <Save v-else :size="18" aria-hidden="true" />
                                {{ processing ? 'Enregistrement…' : 'Enregistrer' }}
                            </button>
                            </div>
                        </div>
                    </form>
                </aside>
            </div>
        </Transition>
    </Teleport>
</template>

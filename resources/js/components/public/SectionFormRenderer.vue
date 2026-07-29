<script setup lang="ts">
import { Check, Minus, Plus, RotateCcw } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Section, SectionSettings } from '../../types';
import MediaPicker from './MediaPicker.vue';
import RichTextEditor from './RichTextEditor.vue';
import { fieldsForSection, type SectionField, type SectionFieldGroup } from './section-fields';

export interface EditableSectionPayload {
    button_text: string | null;
    button_url: string | null;
    content: string | null;
    image_id: number | null;
    is_visible: boolean;
    position: number;
    settings: SectionSettings;
    subtitle: string | null;
    title: string | null;
}

const props = defineProps<{
    errors?: Record<string, string>;
    modelValue: EditableSectionPayload;
    section: Section;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: EditableSectionPayload];
}>();

const fields = computed(() => fieldsForSection(`${props.section.section_key} ${props.section.section_type}`));
const fieldGroups: Array<{ description: string; key: SectionFieldGroup; label: string }> = [
    {
        key: 'title-style',
        label: 'Style du titre du hero',
        description: 'Taille, expressions soulignées et couleurs associées au titre principal.',
    },
    { key: 'content', label: 'Contenu principal', description: 'Textes, boutons et image principale de la section.' },
    { key: 'details', label: 'Contenus complémentaires', description: 'Libellés propres au design de cette section.' },
    { key: 'appearance', label: 'Apparence et affichage', description: 'Fond, largeur, alignement, ordre et visibilité.' },
];

function fieldsInGroup(group: SectionFieldGroup) {
    return fields.value.filter((field) => field.group === group);
}

function valueFor(key: string): string | boolean | number | null {
    if (key.startsWith('settings.')) {
        const settingKey = key.slice('settings.'.length);
        const value = props.modelValue.settings[settingKey];

        return typeof value === 'string' || typeof value === 'boolean' || typeof value === 'number'
            ? value
            : null;
    }

    const value = props.modelValue[key as keyof EditableSectionPayload];

    return typeof value === 'string' || typeof value === 'boolean' || typeof value === 'number'
        ? value
        : null;
}

function resolvedValueFor(field: SectionField): string | boolean | number | null {
    return valueFor(field.key) ?? field.defaultValue ?? null;
}

function updateValue(key: string, value: string | boolean | number | null) {
    if (key.startsWith('settings.')) {
        const settingKey = key.slice('settings.'.length);
        emit('update:modelValue', {
            ...props.modelValue,
            settings: {
                ...props.modelValue.settings,
                [settingKey]: value,
            },
        });
        return;
    }

    emit('update:modelValue', {
        ...props.modelValue,
        [key]: value,
    });
}

function stringFromEvent(event: Event): string {
    return (event.target as HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement).value;
}

function booleanFromEvent(event: Event): boolean {
    return (event.target as HTMLInputElement).checked;
}

function numberFromEvent(event: Event): number {
    const value = Number.parseInt((event.target as HTMLInputElement).value, 10);

    return Number.isFinite(value) && value >= 0 ? value : 0;
}

function adjustRange(field: SectionField, direction: -1 | 1): void {
    const minimum = field.min ?? 0;
    const maximum = field.max ?? 100;
    const step = field.step ?? 1;
    const current = Number(valueFor(field.key) ?? field.defaultValue ?? minimum);
    const next = Math.min(maximum, Math.max(minimum, current + (step * direction)));

    updateValue(field.key, next);
}

function resetImageCrop(): void {
    emit('update:modelValue', {
        ...props.modelValue,
        settings: {
            ...props.modelValue.settings,
            image_position_x: 50,
            image_position_y: 50,
            image_zoom: 100,
        },
    });
}

function fieldId(key: string): string {
    return `section-${props.section.id}-${key.replace('.', '-')}`;
}

function errorFor(key: string): string | null {
    return props.errors?.[key] || null;
}

function mediaIdFor(key: string): number | null {
    const value = key === 'image_id' ? props.modelValue.image_id : valueFor(key);
    const mediaId = Number(value);

    return Number.isInteger(mediaId) && mediaId > 0 ? mediaId : null;
}

function mediaPreviewFor(key: string): string | null | undefined {
    if (key === 'settings.secondary_media_id') {
        return props.section.secondary_image_url;
    }

    if (key === 'settings.tertiary_media_id') {
        return props.section.tertiary_image_url;
    }

    return props.section.image_url || props.section.image?.image_url || props.section.image?.url;
}
</script>

<template>
    <div class="space-y-6">
        <section
            v-for="group in fieldGroups"
            v-show="fieldsInGroup(group.key).length"
            :key="group.key"
            class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
        >
            <div class="mb-5 border-b border-slate-100 pb-4">
                <h3 class="font-heading text-base font-semibold text-navy">{{ group.label }}</h3>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ group.description }}</p>
            </div>
            <div class="space-y-5">
        <template v-for="field in fieldsInGroup(group.key)" :key="field.key">
            <div v-if="field.type === 'boolean'" class="rounded-lg border border-slate-200 p-4">
                <label :for="fieldId(field.key)" class="flex cursor-pointer items-start gap-3">
                    <input
                        :id="fieldId(field.key)"
                        type="checkbox"
                        class="mt-0.5 size-5 rounded border-slate-300 text-edsp-green"
                        :checked="Boolean(valueFor(field.key))"
                        @change="updateValue(field.key, booleanFromEvent($event))"
                    />
                    <span>
                        <span class="block text-sm font-semibold text-slate-800">{{ field.label }}</span>
                        <span class="mt-1 block text-xs text-slate-500">
                            Une section masquée reste accessible aux éditeurs en mode édition.
                        </span>
                    </span>
                </label>
            </div>

            <div v-else-if="field.type === 'media'">
                <label class="mb-1.5 block text-sm font-semibold text-slate-800">{{ field.label }}</label>
                <MediaPicker
                    :input-id="fieldId(field.key)"
                    :model-value="mediaIdFor(field.key)"
                    :preview-url="mediaPreviewFor(field.key)"
                    :alt="section.title"
                    :zoom="Number(modelValue.settings.image_zoom ?? 100)"
                    :position-x="Number(modelValue.settings.image_position_x ?? 50)"
                    :position-y="Number(modelValue.settings.image_position_y ?? 50)"
                    @update:model-value="updateValue(field.key, $event)"
                />
                <p v-if="field.help" class="mt-1.5 text-xs text-slate-500">{{ field.help }}</p>
                <p v-if="errorFor(field.key)" class="mt-1.5 text-sm text-red-700">{{ errorFor(field.key) }}</p>
            </div>

            <fieldset
                v-else-if="field.type === 'color-choice'"
                :id="fieldId(field.key)"
                class="rounded-lg border border-slate-200 bg-slate-50/70 p-4"
                tabindex="-1"
            >
                <legend class="text-sm font-semibold text-slate-800">{{ field.label }}</legend>
                <div class="mt-2 grid gap-2 sm:grid-cols-3">
                    <label
                        v-for="option in field.options"
                        :key="option.value"
                        class="relative flex cursor-pointer items-center gap-3 rounded-lg border bg-white px-3 py-3 transition hover:border-slate-400"
                        :class="resolvedValueFor(field) === option.value ? 'border-institutional ring-2 ring-institutional/10' : 'border-slate-200'"
                    >
                        <input
                            :name="fieldId(field.key)"
                            type="radio"
                            class="sr-only"
                            :value="option.value"
                            :checked="resolvedValueFor(field) === option.value"
                            @change="updateValue(field.key, option.value)"
                        />
                        <span
                            class="h-2.5 w-10 flex-none rounded-full shadow-[inset_0_0_0_1px_rgba(11,31,85,0.08)]"
                            :class="option.swatchClass"
                            aria-hidden="true"
                        />
                        <span class="min-w-0 text-xs font-semibold leading-5 text-slate-700">{{ option.label }}</span>
                        <span
                            v-if="resolvedValueFor(field) === option.value"
                            class="ml-auto grid size-5 flex-none place-items-center rounded-full bg-institutional text-white"
                            aria-hidden="true"
                        >
                            <Check :size="13" :stroke-width="3" />
                        </span>
                    </label>
                </div>
                <p v-if="field.help" class="mt-2 text-xs leading-5 text-slate-500">{{ field.help }}</p>
                <p v-if="errorFor(field.key)" class="mt-1.5 text-sm text-red-700">{{ errorFor(field.key) }}</p>
            </fieldset>

            <div v-else-if="field.type === 'range'">
                <div class="flex items-center justify-between gap-4">
                    <label :for="fieldId(field.key)" class="text-sm font-semibold text-slate-800">
                        {{ field.label }}
                    </label>
                    <output
                        :for="fieldId(field.key)"
                        class="min-w-16 rounded-md bg-institutional/8 px-2.5 py-1 text-center text-sm font-bold text-institutional"
                    >
                        {{ valueFor(field.key) ?? field.defaultValue ?? field.min }}{{ field.unit }}
                    </output>
                </div>
                <div class="mt-3 flex items-center gap-3">
                    <button
                        v-if="field.key === 'settings.image_zoom'"
                        type="button"
                        class="grid size-9 flex-none place-items-center rounded-full border border-slate-300 bg-white text-navy transition hover:border-institutional hover:text-institutional"
                        aria-label="Réduire le zoom"
                        title="Réduire le zoom"
                        @click="adjustRange(field, -1)"
                    >
                        <Minus :size="17" :stroke-width="2.5" aria-hidden="true" />
                    </button>
                    <input
                        :id="fieldId(field.key)"
                        type="range"
                        class="h-2 w-full cursor-pointer accent-institutional"
                        :min="field.min"
                        :max="field.max"
                        :step="field.step || 1"
                        :value="Number(valueFor(field.key) ?? field.defaultValue ?? field.min)"
                        @input="updateValue(field.key, numberFromEvent($event))"
                    />
                    <button
                        v-if="field.key === 'settings.image_zoom'"
                        type="button"
                        class="grid size-9 flex-none place-items-center rounded-full border border-slate-300 bg-white text-navy transition hover:border-institutional hover:text-institutional"
                        aria-label="Augmenter le zoom"
                        title="Augmenter le zoom"
                        @click="adjustRange(field, 1)"
                    >
                        <Plus :size="17" :stroke-width="2.5" aria-hidden="true" />
                    </button>
                </div>
                <div class="mt-1.5 flex justify-between text-[11px] font-medium text-slate-400" aria-hidden="true">
                    <span>Plus petit</span>
                    <span>Plus grand</span>
                </div>
                <p v-if="field.help" class="mt-2 text-xs leading-5 text-slate-500">{{ field.help }}</p>
                <button
                    v-if="field.key === 'settings.image_zoom'"
                    type="button"
                    class="mt-3 inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-institutional hover:text-institutional"
                    @click="resetImageCrop"
                >
                    <RotateCcw :size="15" aria-hidden="true" />
                    Réinitialiser le cadrage
                </button>
                <p v-if="errorFor(field.key)" class="mt-1.5 text-sm text-red-700">{{ errorFor(field.key) }}</p>
            </div>

            <div v-else>
                <label :for="fieldId(field.key)" class="block text-sm font-semibold text-slate-800">
                    {{ field.label }}
                </label>
                <textarea
                    v-if="field.type === 'textarea'"
                    :id="fieldId(field.key)"
                    :value="String(valueFor(field.key) ?? '')"
                    :rows="field.rows || 5"
                    class="form-control mt-1.5 resize-y"
                    :aria-invalid="Boolean(errorFor(field.key))"
                    :aria-describedby="errorFor(field.key) ? `${fieldId(field.key)}-error` : undefined"
                    @input="updateValue(field.key, stringFromEvent($event) || null)"
                />
                <RichTextEditor
                    v-else-if="field.type === 'richtext'"
                    :model-value="String(valueFor(field.key) ?? '')"
                    @update:model-value="updateValue(field.key, $event)"
                />
                <select
                    v-else-if="field.type === 'select'"
                    :id="fieldId(field.key)"
                    :value="String(valueFor(field.key) ?? '')"
                    class="form-control mt-1.5"
                    @change="updateValue(field.key, stringFromEvent($event) || null)"
                >
                    <option value="">Valeur par défaut</option>
                    <option v-for="option in field.options" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <input
                    v-else
                    :id="fieldId(field.key)"
                    :type="field.type === 'number' ? 'number' : 'text'"
                    :min="field.type === 'number' ? 0 : undefined"
                    :inputmode="field.type === 'url' ? 'url' : 'text'"
                    :value="String(valueFor(field.key) ?? '')"
                    class="form-control mt-1.5"
                    :aria-invalid="Boolean(errorFor(field.key))"
                    :aria-describedby="errorFor(field.key) ? `${fieldId(field.key)}-error` : undefined"
                    @input="updateValue(field.key, field.type === 'number' ? numberFromEvent($event) : stringFromEvent($event) || null)"
                />
                <p v-if="field.help" class="mt-1.5 text-xs text-slate-500">{{ field.help }}</p>
                <p
                    v-if="errorFor(field.key)"
                    :id="`${fieldId(field.key)}-error`"
                    class="mt-1.5 text-sm text-red-700"
                >
                    {{ errorFor(field.key) }}
                </p>
            </div>
        </template>
            </div>
        </section>
    </div>
</template>

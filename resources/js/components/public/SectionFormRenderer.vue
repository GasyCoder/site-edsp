<script setup lang="ts">
import { computed } from 'vue';
import type { Section, SectionSettings } from '../../types';
import MediaPicker from './MediaPicker.vue';
import { fieldsForSection, type SectionFieldGroup } from './section-fields';

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

const fields = computed(() => fieldsForSection(props.section.section_type));
const fieldGroups: Array<{ description: string; key: SectionFieldGroup; label: string }> = [
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

function fieldId(key: string): string {
    return `section-${props.section.id}-${key.replace('.', '-')}`;
}

function errorFor(key: string): string | null {
    return props.errors?.[key] || null;
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
                    :model-value="modelValue.image_id"
                    :preview-url="section.image_url || section.image?.image_url || section.image?.url"
                    :alt="section.title"
                    @update:model-value="updateValue(field.key, $event)"
                />
                <p v-if="field.help" class="mt-1.5 text-xs text-slate-500">{{ field.help }}</p>
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

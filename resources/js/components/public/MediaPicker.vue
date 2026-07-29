<script setup lang="ts">
import { ImagePlus, RefreshCw, Search, X } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import MediaPlaceholder from './MediaPlaceholder.vue';

type MediaOption = {
    alt_text?: string | null;
    id: number;
    image_url?: string | null;
    original_name: string;
    thumbnail_url?: string | null;
    url?: string | null;
};

const props = withDefaults(defineProps<{
    alt?: string | null;
    inputId?: string;
    modelValue: number | null;
    positionX?: number;
    positionY?: number;
    previewUrl?: string | null;
    zoom?: number;
}>(), {
    alt: null,
    inputId: 'section-media',
    positionX: 50,
    positionY: 50,
    previewUrl: null,
    zoom: 100,
});

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
}>();

const media = ref<MediaOption[]>([]);
const search = ref('');
const loading = ref(false);
const loadError = ref<string | null>(null);
const selected = computed(() => media.value.find((item) => item.id === props.modelValue) ?? null);
const resolvedPreview = computed(() => selected.value?.thumbnail_url || selected.value?.image_url || selected.value?.url || props.previewUrl);
const resolvedAlt = computed(() => selected.value?.alt_text || props.alt);

async function loadMedia(): Promise<void> {
    loading.value = true;
    loadError.value = null;

    try {
        const parameters = new URLSearchParams({ type: 'image' });
        if (search.value.trim()) parameters.set('search', search.value.trim());

        const response = await fetch(`/administration/medias?${parameters.toString()}`, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) throw new Error('La médiathèque est indisponible.');

        const payload = await response.json() as { data?: MediaOption[] };
        media.value = payload.data ?? [];
    } catch (error) {
        loadError.value = error instanceof Error ? error.message : 'La médiathèque est indisponible.';
    } finally {
        loading.value = false;
    }
}

function selectMedia(event: Event): void {
    const value = Number.parseInt((event.target as HTMLSelectElement).value, 10);
    emit('update:modelValue', Number.isFinite(value) && value > 0 ? value : null);
}

onMounted(loadMedia);
</script>

<template>
    <div class="rounded-lg border border-slate-200 bg-soft p-4">
        <div class="mb-4 h-40 overflow-hidden rounded-md border border-slate-200 bg-white">
            <MediaPlaceholder
                :image-url="resolvedPreview"
                :alt="resolvedAlt"
                fit="contain"
                label="Aperçu du média sélectionné"
                :object-position="`${positionX}% ${positionY}%`"
                :scale="zoom"
            />
        </div>

        <form class="flex gap-2" role="search" @submit.prevent="loadMedia">
            <label :for="`${inputId}-search`" class="sr-only">Rechercher dans la médiathèque</label>
            <span class="relative flex-1">
                <Search :size="17" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                <input :id="`${inputId}-search`" v-model="search" type="search" class="form-control pl-10" placeholder="Nom ou texte alternatif">
            </span>
            <button type="submit" class="button-secondary px-3" :disabled="loading" aria-label="Rechercher">
                <RefreshCw :size="17" :class="loading && 'animate-spin'" aria-hidden="true" />
            </button>
        </form>

        <label class="mt-4 block text-sm font-semibold text-slate-800" :for="`${inputId}-select`">
            Image de la médiathèque
        </label>
        <div class="mt-1.5 flex gap-2">
            <span class="relative flex-1">
                <ImagePlus :size="18" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                <select :id="`${inputId}-select`" :value="modelValue ?? ''" class="form-control pl-10" @change="selectMedia">
                    <option value="">Aucune image</option>
                    <option v-for="item in media" :key="item.id" :value="item.id">
                        {{ item.original_name }}{{ item.alt_text ? ` · ${item.alt_text}` : '' }}
                    </option>
                </select>
            </span>
            <button
                v-if="modelValue"
                type="button"
                class="grid size-11 place-items-center rounded-md border border-slate-300 bg-white text-slate-600 transition hover:border-red-300 hover:text-red-700"
                aria-label="Retirer l'image"
                @click="emit('update:modelValue', null)"
            >
                <X :size="18" aria-hidden="true" />
            </button>
        </div>
        <p v-if="loadError" class="mt-2 text-sm text-red-700" role="alert">{{ loadError }}</p>
        <a href="/admin/media" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex text-xs font-semibold text-institutional hover:underline">
            Ouvrir la médiathèque complète
        </a>
    </div>
</template>

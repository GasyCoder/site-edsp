<script setup lang="ts">
import { ChevronLeft, ChevronRight, Expand, Images, X } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { GalleryImage, PublicGallery } from '../../types';
import { mediaUrl } from '../../lib/public-content';
import { useI18n } from '../../lib/i18n';
import MediaPlaceholder from './MediaPlaceholder.vue';

const props = withDefaults(defineProps<{
    galleries?: PublicGallery[];
}>(), {
    galleries: () => [],
});

type GalleryFilter = 'all' | number;
type LightboxItem = {
    gallery: PublicGallery;
    image: GalleryImage;
};

const { tr } = useI18n();
const activeGallery = ref<GalleryFilter>('all');
const activeImageIndex = ref<number | null>(null);
const closeButton = ref<HTMLButtonElement | null>(null);
let previousOverflow = '';

const publishedGalleries = computed(() => props.galleries.filter((gallery) => (gallery.images?.length ?? 0) > 0));
const totalImages = computed(() => publishedGalleries.value.reduce((total, gallery) => total + (gallery.images?.length ?? 0), 0));
const visibleGalleries = computed(() => activeGallery.value === 'all'
    ? publishedGalleries.value
    : publishedGalleries.value.filter((gallery) => gallery.id === activeGallery.value));
const lightboxItems = computed<LightboxItem[]>(() => visibleGalleries.value.flatMap((gallery) =>
    (gallery.images ?? []).map((image) => ({ gallery, image })),
));
const activeItem = computed(() => activeImageIndex.value === null ? null : lightboxItems.value[activeImageIndex.value] ?? null);
const lightboxOpen = computed(() => activeImageIndex.value !== null);

function imageLabel(gallery: PublicGallery, image: GalleryImage): string {
    return image.title || image.caption || image.alt_text || image.media?.alt_text || gallery.title;
}

function tileClass(index: number): string {
    if (index % 7 === 0) return 'sm:col-span-2 sm:row-span-2';
    if (index % 7 === 4) return 'lg:col-span-2';

    return '';
}

function selectGallery(gallery: GalleryFilter): void {
    activeGallery.value = gallery;
    activeImageIndex.value = null;
}

function openImage(gallery: PublicGallery, image: GalleryImage): void {
    const index = lightboxItems.value.findIndex((item) => item.gallery.id === gallery.id && item.image.id === image.id);

    if (index >= 0) activeImageIndex.value = index;
}

function closeLightbox(): void {
    activeImageIndex.value = null;
}

function previousImage(): void {
    if (activeImageIndex.value === null || lightboxItems.value.length < 2) return;
    activeImageIndex.value = (activeImageIndex.value - 1 + lightboxItems.value.length) % lightboxItems.value.length;
}

function nextImage(): void {
    if (activeImageIndex.value === null || lightboxItems.value.length < 2) return;
    activeImageIndex.value = (activeImageIndex.value + 1) % lightboxItems.value.length;
}

function handleKeydown(event: KeyboardEvent): void {
    if (activeImageIndex.value === null) return;

    if (event.key === 'Escape') closeLightbox();
    if (event.key === 'ArrowLeft') previousImage();
    if (event.key === 'ArrowRight') nextImage();
}

watch(lightboxOpen, async (open) => {
    if (open) {
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        await nextTick();
        closeButton.value?.focus();
        return;
    }

    document.body.style.overflow = previousOverflow;
});

onMounted(() => window.addEventListener('keydown', handleKeydown));
onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = previousOverflow;
});
</script>

<template>
    <section class="border-t border-slate-200 bg-white px-4 py-14 dark:border-slate-800 dark:bg-slate-950 sm:px-6 sm:py-20" aria-labelledby="gallery-list-title">
        <div class="mx-auto max-w-7xl">
            <div class="grid items-end gap-7 border-b border-slate-200 pb-8 dark:border-slate-800 lg:grid-cols-[minmax(0,1fr)_auto]">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-edsp-green">
                        {{ tr('La vie de l’école en images', 'Life at the School in pictures') }}
                    </p>
                    <h2 id="gallery-list-title" class="mt-2 max-w-3xl text-balance text-2xl font-bold leading-tight text-navy dark:text-white sm:text-3xl">
                        {{ tr('Découvrez les temps forts de l’EDSP', 'Discover the highlights of EDSP') }}
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300 sm:text-base">
                        {{ tr('Conférences, activités académiques et moments de la vie étudiante réunis dans nos albums.', 'Conferences, academic activities and student life brought together in our albums.') }}
                    </p>
                </div>

                <div class="flex w-fit items-center gap-5 rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-700 dark:bg-slate-900">
                    <Images :size="24" class="text-edsp-green" aria-hidden="true" />
                    <div>
                        <p class="font-heading text-xl font-bold leading-none text-navy dark:text-white">{{ totalImages }}</p>
                        <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">
                            {{ tr(totalImages > 1 ? 'photos publiées' : 'photo publiée', totalImages > 1 ? 'published photos' : 'published photo') }}
                        </p>
                    </div>
                </div>
            </div>

            <nav v-if="publishedGalleries.length > 1" :aria-label="tr('Filtrer les galeries', 'Filter galleries')" class="mt-7 overflow-x-auto pb-2">
                <div class="flex min-w-max gap-2">
                    <button
                        type="button"
                        class="rounded-full border px-4 py-2 text-sm font-semibold transition"
                        :class="activeGallery === 'all'
                            ? 'border-edsp-green bg-edsp-green text-white shadow-sm'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-edsp-green hover:text-edsp-green dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
                        :aria-pressed="activeGallery === 'all'"
                        @click="selectGallery('all')"
                    >
                        {{ tr('Toutes les galeries', 'All galleries') }}
                    </button>
                    <button
                        v-for="gallery in publishedGalleries"
                        :key="gallery.id"
                        type="button"
                        class="rounded-full border px-4 py-2 text-sm font-semibold transition"
                        :class="activeGallery === gallery.id
                            ? 'border-edsp-green bg-edsp-green text-white shadow-sm'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-edsp-green hover:text-edsp-green dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
                        :aria-pressed="activeGallery === gallery.id"
                        @click="selectGallery(gallery.id)"
                    >
                        {{ gallery.title }}
                        <span class="ml-1 opacity-70">{{ gallery.images?.length ?? 0 }}</span>
                    </button>
                </div>
            </nav>

            <div v-if="visibleGalleries.length" class="divide-y divide-slate-200 dark:divide-slate-800">
                <article v-for="gallery in visibleGalleries" :key="gallery.id" class="py-10 first:pt-9 sm:py-14">
                    <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div class="max-w-3xl">
                            <h3 class="text-xl font-bold text-navy dark:text-white sm:text-2xl">{{ gallery.title }}</h3>
                            <p v-if="gallery.description" class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base">
                                {{ gallery.description }}
                            </p>
                        </div>
                        <p class="flex-none text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            {{ gallery.images?.length ?? 0 }} {{ tr((gallery.images?.length ?? 0) > 1 ? 'photos' : 'photo', (gallery.images?.length ?? 0) > 1 ? 'photos' : 'photo') }}
                        </p>
                    </header>

                    <div class="grid auto-rows-[15rem] grid-cols-1 gap-3 sm:grid-cols-2 sm:auto-rows-[12rem] lg:grid-cols-4">
                        <figure
                            v-for="(image, index) in gallery.images"
                            :key="image.id"
                            class="group relative min-h-60 overflow-hidden rounded-xl bg-slate-100 shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10 sm:min-h-0"
                            :class="tileClass(index)"
                        >
                            <button
                                type="button"
                                class="relative h-full w-full overflow-hidden text-left focus:outline-none focus-visible:ring-4 focus-visible:ring-edsp-green/50"
                                :aria-label="tr(`Agrandir : ${imageLabel(gallery, image)}`, `Enlarge: ${imageLabel(gallery, image)}`)"
                                @click="openImage(gallery, image)"
                            >
                                <span class="absolute inset-0 transition duration-500 ease-out group-hover:scale-[1.035]">
                                    <MediaPlaceholder
                                        :image-url="mediaUrl(image.media)"
                                        :alt="image.alt_text || image.media?.alt_text || imageLabel(gallery, image)"
                                        :label="imageLabel(gallery, image)"
                                    />
                                </span>
                                <span class="pointer-events-none absolute inset-0 bg-gradient-to-t from-navy/85 via-navy/5 to-transparent opacity-80 transition group-hover:opacity-100" aria-hidden="true" />
                                <span class="pointer-events-none absolute right-3 top-3 grid size-9 place-items-center rounded-full bg-white/90 text-navy opacity-0 shadow-md transition group-hover:opacity-100 group-focus-within:opacity-100" aria-hidden="true">
                                    <Expand :size="17" />
                                </span>
                                <span v-if="image.title || image.caption" class="pointer-events-none absolute inset-x-0 bottom-0 p-4 text-white">
                                    <span v-if="image.title" class="block font-heading text-sm font-bold">{{ image.title }}</span>
                                    <span v-if="image.caption" class="mt-1 block line-clamp-2 text-xs leading-5 text-white/80">{{ image.caption }}</span>
                                </span>
                            </button>
                            <figcaption class="sr-only">{{ imageLabel(gallery, image) }}</figcaption>
                        </figure>
                    </div>
                </article>
            </div>

            <div v-else class="mt-9 grid min-h-72 place-items-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 text-center dark:border-slate-700 dark:bg-slate-900">
                <div class="max-w-md">
                    <span class="mx-auto grid size-14 place-items-center rounded-full bg-edsp-green/10 text-edsp-green">
                        <Images :size="26" aria-hidden="true" />
                    </span>
                    <h3 class="mt-4 text-lg font-bold text-navy dark:text-white">{{ tr('La galerie sera bientôt enrichie', 'The gallery will be updated soon') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        {{ tr('Revenez prochainement pour découvrir les activités de l’EDSP en images.', 'Visit again soon to discover EDSP activities in pictures.') }}
                    </p>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" leave-active-class="transition duration-150" leave-to-class="opacity-0">
                <div
                    v-if="activeItem"
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/95 p-3 backdrop-blur-sm sm:p-8"
                    role="dialog"
                    aria-modal="true"
                    :aria-label="tr('Visionneuse de la galerie', 'Gallery viewer')"
                    @click.self="closeLightbox"
                >
                    <button
                        ref="closeButton"
                        type="button"
                        class="absolute right-4 top-4 z-10 grid size-11 place-items-center rounded-full border border-white/20 bg-white/10 text-white transition hover:bg-white hover:text-navy sm:right-7 sm:top-7"
                        :aria-label="tr('Fermer la visionneuse', 'Close viewer')"
                        @click="closeLightbox"
                    >
                        <X :size="22" aria-hidden="true" />
                    </button>

                    <button
                        v-if="lightboxItems.length > 1"
                        type="button"
                        class="absolute left-3 top-1/2 z-10 grid size-11 -translate-y-1/2 place-items-center rounded-full border border-white/20 bg-white/10 text-white transition hover:bg-white hover:text-navy sm:left-7"
                        :aria-label="tr('Photo précédente', 'Previous photo')"
                        @click="previousImage"
                    >
                        <ChevronLeft :size="25" aria-hidden="true" />
                    </button>

                    <figure class="flex max-h-[calc(100vh-2rem)] w-full max-w-6xl flex-col items-center sm:max-h-[calc(100vh-4rem)]">
                        <div class="h-[70vh] min-h-0 w-full flex-1 overflow-hidden rounded-xl bg-black/20 sm:h-[76vh]">
                            <MediaPlaceholder
                                :image-url="mediaUrl(activeItem.image.media)"
                                :alt="activeItem.image.alt_text || activeItem.image.media?.alt_text || imageLabel(activeItem.gallery, activeItem.image)"
                                :label="imageLabel(activeItem.gallery, activeItem.image)"
                                fit="contain"
                                eager
                                class="rounded-xl shadow-2xl"
                            />
                        </div>
                        <figcaption class="mt-4 max-w-3xl text-center text-white">
                            <p class="font-heading text-sm font-bold sm:text-base">{{ activeItem.image.title || activeItem.gallery.title }}</p>
                            <p v-if="activeItem.image.caption" class="mt-1 text-sm leading-6 text-slate-300">{{ activeItem.image.caption }}</p>
                            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                {{ (activeImageIndex ?? 0) + 1 }} / {{ lightboxItems.length }} · {{ activeItem.gallery.title }}
                            </p>
                        </figcaption>
                    </figure>

                    <button
                        v-if="lightboxItems.length > 1"
                        type="button"
                        class="absolute right-3 top-1/2 z-10 grid size-11 -translate-y-1/2 place-items-center rounded-full border border-white/20 bg-white/10 text-white transition hover:bg-white hover:text-navy sm:right-7"
                        :aria-label="tr('Photo suivante', 'Next photo')"
                        @click="nextImage"
                    >
                        <ChevronRight :size="25" aria-hidden="true" />
                    </button>
                </div>
            </Transition>
        </Teleport>
    </section>
</template>

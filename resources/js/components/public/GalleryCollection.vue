<script setup lang="ts">
import { ChevronLeft, ChevronRight, Images, X } from 'lucide-vue-next';
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

type WallItem = {
    gallery: PublicGallery;
    image: GalleryImage;
};

type AlbumGroup = {
    key: string;
    title: string;
    description: string | null;
    items: WallItem[];
};

const { tr } = useI18n();
const activeGroupKey = ref<'all' | string>('all');
const lightboxItems = ref<WallItem[]>([]);
const activeImageIndex = ref<number | null>(null);
const closeButton = ref<HTMLButtonElement | null>(null);
let previousOverflow = '';

const publishedGalleries = computed(() => props.galleries.filter((gallery) => (gallery.images?.length ?? 0) > 0));
const totalImages = computed(() => publishedGalleries.value.reduce((total, gallery) => total + (gallery.images?.length ?? 0), 0));

const albumGroups = computed<AlbumGroup[]>(() => {
    const groups = new Map<string, AlbumGroup>();

    for (const gallery of publishedGalleries.value) {
        const title = gallery.title.trim();
        const key = title.toLocaleLowerCase();
        const items = (gallery.images ?? []).map((image) => ({ gallery, image }));
        const existing = groups.get(key);

        if (existing) {
            existing.items.push(...items);
            existing.description ||= gallery.description ?? null;
        } else {
            groups.set(key, { key, title, description: gallery.description ?? null, items });
        }
    }

    return [...groups.values()];
});

const visibleGroups = computed(() => activeGroupKey.value === 'all'
    ? albumGroups.value
    : albumGroups.value.filter((group) => group.key === activeGroupKey.value));
const activeGroupData = computed(() => activeGroupKey.value === 'all'
    ? null
    : albumGroups.value.find((group) => group.key === activeGroupKey.value) ?? null);
const activeItem = computed(() => activeImageIndex.value === null ? null : lightboxItems.value[activeImageIndex.value] ?? null);
const lightboxOpen = computed(() => activeImageIndex.value !== null);

function imageLabel(item: WallItem): string {
    return item.image.title || item.image.caption || item.image.alt_text || item.image.media?.alt_text || item.gallery.title;
}

function groupCover(group: AlbumGroup): WallItem | null {
    return group.items.find((item) => mediaUrl(item.image.media)) ?? group.items[0] ?? null;
}

function selectGroup(key: 'all' | string): void {
    activeGroupKey.value = key;
    activeImageIndex.value = null;
}

function openGroup(group: AlbumGroup): void {
    if (!group.items.length) return;

    lightboxItems.value = group.items;
    activeImageIndex.value = 0;
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
    <section class="border-t border-slate-200 bg-white pb-16 dark:border-slate-800 dark:bg-slate-950 sm:pb-20" aria-labelledby="gallery-list-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <h2 id="gallery-list-title" class="sr-only">
                {{ tr('La vie de l’école en images', 'Life at the School in pictures') }}
            </h2>

            <nav
                v-if="totalImages"
                :aria-label="tr('Filtrer les albums', 'Filter albums')"
                class="sticky top-[76px] z-20 -mx-4 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90 sm:top-[84px] sm:-mx-6 sm:px-6"
            >
                <div class="mx-auto flex max-w-7xl items-center gap-2 overflow-x-auto">
                    <template v-if="albumGroups.length > 1">
                        <button
                            type="button"
                            class="flex-none rounded-full border px-4 py-2 text-sm font-semibold transition"
                            :class="activeGroupKey === 'all'
                                ? 'border-edsp-green bg-edsp-green text-white shadow-sm'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-edsp-green hover:text-edsp-green dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
                            :aria-pressed="activeGroupKey === 'all'"
                            @click="selectGroup('all')"
                        >
                            {{ tr('Tout voir', 'View all') }}
                            <span class="ml-1 opacity-70">{{ totalImages }}</span>
                        </button>
                        <button
                            v-for="group in albumGroups"
                            :key="group.key"
                            type="button"
                            class="flex-none rounded-full border px-4 py-2 text-sm font-semibold transition"
                            :class="activeGroupKey === group.key
                                ? 'border-edsp-green bg-edsp-green text-white shadow-sm'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-edsp-green hover:text-edsp-green dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
                            :aria-pressed="activeGroupKey === group.key"
                            @click="selectGroup(group.key)"
                        >
                            {{ group.title }}
                            <span class="ml-1 opacity-70">{{ group.items.length }}</span>
                        </button>
                    </template>
                    <p class="ml-auto inline-flex flex-none items-center gap-2 pl-2 text-sm font-semibold text-navy dark:text-white">
                        <Images :size="17" class="text-edsp-green" aria-hidden="true" />
                        {{ totalImages }} {{ tr(totalImages > 1 ? 'photos' : 'photo', totalImages > 1 ? 'photos' : 'photo') }}
                    </p>
                </div>
            </nav>

            <div
                v-if="activeGroupData?.description"
                class="mt-7 rounded-2xl border border-slate-200 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-900"
            >
                <h3 class="text-lg font-bold text-navy dark:text-white sm:text-xl">{{ activeGroupData.title }}</h3>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base">
                    {{ activeGroupData.description }}
                </p>
            </div>

            <div v-if="visibleGroups.length" class="mt-7 grid gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3">
                <article v-for="group in visibleGroups" :key="group.key" class="group">
                    <button
                        type="button"
                        class="relative block h-64 w-full overflow-hidden rounded-2xl bg-slate-100 text-left shadow-sm ring-1 ring-slate-900/5 focus:outline-none focus-visible:ring-4 focus-visible:ring-edsp-green/50 dark:bg-slate-900 dark:ring-white/10 sm:h-72"
                        :aria-label="tr(
                            `Ouvrir l’album ${group.title} (${group.items.length} photos)`,
                            `Open the ${group.title} album (${group.items.length} photos)`,
                        )"
                        @click="openGroup(group)"
                    >
                        <span class="absolute inset-0 transition duration-500 ease-out group-hover:scale-[1.03]">
                            <MediaPlaceholder
                                :image-url="mediaUrl(groupCover(group)?.image.media)"
                                :alt="groupCover(group) ? imageLabel(groupCover(group)!) : group.title"
                                :label="group.title"
                            />
                        </span>
                        <span class="pointer-events-none absolute inset-0 bg-gradient-to-t from-navy/90 via-navy/15 to-transparent" aria-hidden="true" />
                        <span
                            v-if="group.items.length > 1"
                            class="absolute right-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-navy shadow-md"
                        >
                            <Images :size="14" class="text-edsp-green" aria-hidden="true" />
                            +{{ group.items.length }} photos
                        </span>
                        <span class="pointer-events-none absolute inset-x-0 bottom-0 p-5 text-white">
                            <span class="block truncate font-heading text-base font-bold">{{ group.title }}</span>
                            <span class="mt-1 block text-xs font-semibold uppercase tracking-wide text-white/75">
                                {{ group.items.length }} {{ tr(group.items.length > 1 ? 'photos' : 'photo', group.items.length > 1 ? 'photos' : 'photo') }}
                                · {{ tr('Cliquer pour ouvrir', 'Click to open') }}
                            </span>
                        </span>
                    </button>
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
                                :alt="imageLabel(activeItem)"
                                :label="imageLabel(activeItem)"
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

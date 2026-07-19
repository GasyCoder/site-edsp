<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { SharedPageProps, SiteSettings } from '../types';
import BackToTopButton from '../components/public/BackToTopButton.vue';
import MainHeader from '../components/public/MainHeader.vue';
import PageLoadingSkeleton from '../components/public/PageLoadingSkeleton.vue';
import PublicFooter from '../components/public/PublicFooter.vue';
import TopBar from '../components/public/TopBar.vue';
import { loadingSkeletonVariant, navigationLoading } from '../lib/navigation-loading';

const props = withDefaults(
    defineProps<{
        settings?: SiteSettings;
    }>(),
    {
        settings: () => ({}),
    },
);

const page = usePage();
const shared = computed(() => page.props as SharedPageProps);
const resolvedSettings = computed<SiteSettings>(() => ({
    ...(shared.value.settings ?? {}),
    ...props.settings,
}));
const successMessage = computed(() => shared.value.flash?.success || null);
const toastVisible = ref(Boolean(successMessage.value));
let dismissTimer: ReturnType<typeof setTimeout> | null = null;

const clearDismissTimer = (): void => {
    if (dismissTimer) clearTimeout(dismissTimer);
    dismissTimer = null;
};

const scheduleDismiss = (): void => {
    clearDismissTimer();
    dismissTimer = setTimeout(() => {
        toastVisible.value = false;
    }, 7000);
};

const dismissToast = (): void => {
    clearDismissTimer();
    toastVisible.value = false;
};

watch(successMessage, (message) => {
    toastVisible.value = Boolean(message);
    if (message) scheduleDismiss();
});

onMounted(() => {
    if (successMessage.value) scheduleDismiss();
});

onBeforeUnmount(() => {
    clearDismissTimer();
});
</script>

<template>
    <div class="min-h-screen bg-white text-slate-800">
        <a
            href="#main-content"
            class="fixed left-4 top-3 z-[100] -translate-y-24 rounded-md bg-white px-4 py-3 font-semibold text-navy shadow-xl transition focus:translate-y-0"
        >
            Aller au contenu principal
        </a>
        <TopBar :settings="resolvedSettings" />
        <MainHeader :settings="resolvedSettings" />

        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-3 opacity-0 sm:translate-x-5 sm:translate-y-0"
            enter-to-class="translate-x-0 translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="translate-y-2 opacity-0 sm:translate-x-4 sm:translate-y-0"
        >
            <div
                v-if="successMessage && toastVisible"
                class="fixed inset-x-4 top-4 z-[120] mx-auto max-w-md overflow-hidden rounded-xl border border-green-700 bg-edsp-green text-white shadow-[0_18px_55px_rgba(7,139,62,0.35)] sm:inset-x-auto sm:right-6 sm:top-6 sm:w-[26rem]"
                role="status"
                aria-live="polite"
            >
                <div class="h-1 bg-gold" aria-hidden="true" />
                <div class="flex items-start gap-3 p-4">
                    <span class="grid size-10 flex-none place-items-center rounded-full bg-white/20 text-white ring-1 ring-white/40">
                        <CheckCircle2 :size="22" aria-hidden="true" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-heading text-sm font-bold text-white">Opération réussie</p>
                        <p class="mt-1 text-sm font-medium leading-6 text-white">{{ successMessage }}</p>
                    </div>
                    <button type="button" class="grid size-8 flex-none place-items-center rounded-md text-white transition hover:bg-white/20" aria-label="Fermer la notification" @click="dismissToast">
                        <X :size="18" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </Transition>

        <main id="main-content" tabindex="-1" :aria-busy="navigationLoading">
            <Transition
                mode="out-in"
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-100"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <PageLoadingSkeleton v-if="navigationLoading" key="loading" :variant="loadingSkeletonVariant" />
                <div v-else key="content">
                    <slot />
                </div>
            </Transition>
        </main>

        <PublicFooter :settings="resolvedSettings" />
        <BackToTopButton />
    </div>
</template>

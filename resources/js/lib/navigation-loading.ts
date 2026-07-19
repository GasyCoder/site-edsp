import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

export type LoadingSkeletonVariant = 'home' | 'listing' | 'detail' | 'form' | 'page';

export const navigationLoading = ref(false);
export const loadingSkeletonVariant = ref<LoadingSkeletonVariant>('page');

let initialized = false;
let activeVisitId: string | null = null;
let startedAt = 0;
let finishTimer: ReturnType<typeof setTimeout> | null = null;

const skeletonVariantForPath = (path: string): LoadingSkeletonVariant => {
    if (path === '/') return 'home';
    if (path === '/inscription') return 'form';
    if (path === '/formations' || path === '/actualites') return 'listing';
    if (path.startsWith('/formations/') || path.startsWith('/actualites/')) return 'detail';

    return 'page';
};

const clearFinishTimer = (): void => {
    if (!finishTimer) return;

    clearTimeout(finishTimer);
    finishTimer = null;
};

export const startInitialPageLoading = (path: string): void => {
    clearFinishTimer();
    activeVisitId = 'initial-page-load';
    loadingSkeletonVariant.value = skeletonVariantForPath(path);
    startedAt = Date.now();
    navigationLoading.value = true;

    finishTimer = setTimeout(() => {
        navigationLoading.value = false;
        activeVisitId = null;
        finishTimer = null;
    }, 620);
};

export const initializeNavigationLoading = (): void => {
    if (initialized) return;
    initialized = true;

    router.on('start', (event) => {
        if (event.detail.visit.method !== 'get') return;

        clearFinishTimer();

        activeVisitId = event.detail.visit.id;
        loadingSkeletonVariant.value = skeletonVariantForPath(event.detail.visit.url.pathname);
        startedAt = Date.now();
        navigationLoading.value = true;
    });

    router.on('finish', (event) => {
        if (event.detail.visit.id !== activeVisitId || !navigationLoading.value) return;

        const minimumVisibleDuration = 520;
        const remainingDuration = Math.max(0, minimumVisibleDuration - (Date.now() - startedAt));

        finishTimer = setTimeout(() => {
            navigationLoading.value = false;
            activeVisitId = null;
            finishTimer = null;
        }, remainingDuration);
    });
};

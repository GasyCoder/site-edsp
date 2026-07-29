<script setup lang="ts">
import { Download, Share2, Smartphone, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from '../../lib/i18n';

interface BeforeInstallPromptEvent extends Event {
    prompt: () => Promise<void>;
    userChoice: Promise<{
        outcome: 'accepted' | 'dismissed';
        platform: string;
    }>;
}

interface NavigatorWithStandalone extends Navigator {
    standalone?: boolean;
}

const DISMISS_STORAGE_KEY = 'edsp-pwa-install-dismissed-at';
const DISMISS_DURATION = 7 * 24 * 60 * 60 * 1000;
const DISPLAY_DELAY = 3500;

const { tr } = useI18n();
const deferredPrompt = ref<BeforeInstallPromptEvent | null>(null);
const visible = ref(false);
const installing = ref(false);
const installed = ref(false);
const isIos = ref(false);
let displayTimer: ReturnType<typeof setTimeout> | null = null;

const canOfferInstallation = computed(() => Boolean(deferredPrompt.value) || isIos.value);
const actionLabel = computed(() => isIos.value
    ? tr('J’ai compris', 'Got it')
    : tr('Installer', 'Install'));

function isStandalone(): boolean {
    return window.matchMedia('(display-mode: standalone)').matches
        || (navigator as NavigatorWithStandalone).standalone === true;
}

function isAppleMobileDevice(): boolean {
    const platform = navigator.platform || '';
    const isIpadOs = platform === 'MacIntel' && navigator.maxTouchPoints > 1;

    return /iPad|iPhone|iPod/.test(navigator.userAgent) || isIpadOs;
}

function wasRecentlyDismissed(): boolean {
    try {
        const dismissedAt = Number(localStorage.getItem(DISMISS_STORAGE_KEY) || 0);

        return dismissedAt > 0 && Date.now() - dismissedAt < DISMISS_DURATION;
    } catch {
        return false;
    }
}

function scheduleDisplay(): void {
    if (installed.value || wasRecentlyDismissed() || !canOfferInstallation.value || displayTimer) return;

    displayTimer = setTimeout(() => {
        visible.value = true;
        displayTimer = null;
    }, DISPLAY_DELAY);
}

function handleBeforeInstallPrompt(event: Event): void {
    event.preventDefault();
    deferredPrompt.value = event as BeforeInstallPromptEvent;
    scheduleDisplay();
}

function handleInstalled(): void {
    installed.value = true;
    visible.value = false;
    deferredPrompt.value = null;

    try {
        localStorage.removeItem(DISMISS_STORAGE_KEY);
    } catch {
        // L’installation reste fonctionnelle si le stockage local est indisponible.
    }
}

function dismiss(): void {
    visible.value = false;

    try {
        localStorage.setItem(DISMISS_STORAGE_KEY, String(Date.now()));
    } catch {
        // La fermeture visuelle suffit si le stockage local est indisponible.
    }
}

async function install(): Promise<void> {
    if (isIos.value) {
        dismiss();
        return;
    }

    const prompt = deferredPrompt.value;
    if (!prompt || installing.value) return;

    installing.value = true;

    try {
        await prompt.prompt();
        const choice = await prompt.userChoice;

        if (choice.outcome === 'accepted') {
            visible.value = false;
        }
    } finally {
        deferredPrompt.value = null;
        installing.value = false;
    }
}

onMounted(() => {
    installed.value = isStandalone();
    isIos.value = isAppleMobileDevice() && !installed.value;

    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.addEventListener('appinstalled', handleInstalled);

    if (isIos.value) scheduleDisplay();
});

onBeforeUnmount(() => {
    if (displayTimer) clearTimeout(displayTimer);
    window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.removeEventListener('appinstalled', handleInstalled);
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-4 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="translate-y-3 opacity-0"
    >
        <aside
            v-if="visible && canOfferInstallation && !installed"
            class="fixed inset-x-3 bottom-[calc(5.35rem+env(safe-area-inset-bottom))] z-[85] mx-auto max-w-sm rounded-xl border border-slate-200 bg-white p-4 text-navy shadow-[0_16px_45px_rgba(11,31,85,0.18)] dark:border-white/15 dark:bg-[#101c32] dark:text-white sm:inset-x-auto sm:bottom-6 sm:right-24 sm:w-[25rem]"
            role="dialog"
            aria-labelledby="pwa-install-title"
            aria-describedby="pwa-install-description"
        >
            <button
                type="button"
                class="absolute right-2.5 top-2.5 grid size-8 place-items-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-navy focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-edsp-green dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                :aria-label="tr('Fermer la proposition d’installation', 'Dismiss install suggestion')"
                @click="dismiss"
            >
                <X :size="17" aria-hidden="true" />
            </button>

            <div class="flex items-start gap-3 pr-7">
                <span class="grid size-11 flex-none place-items-center rounded-lg bg-edsp-green/10 text-edsp-green ring-1 ring-edsp-green/15 dark:bg-edsp-green/15">
                    <Smartphone :size="22" aria-hidden="true" />
                </span>
                <div class="min-w-0">
                    <p id="pwa-install-title" class="font-heading text-sm font-bold sm:text-base">
                        {{ tr('Gardez l’EDSP à portée de main', 'Keep EDSP within easy reach') }}
                    </p>
                    <p id="pwa-install-description" class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300 sm:text-sm">
                        <template v-if="isIos">
                            {{ tr('Touchez Partager, puis « Sur l’écran d’accueil ».', 'Tap Share, then “Add to Home Screen”.') }}
                        </template>
                        <template v-else>
                            {{ tr('Installez le site pour y accéder rapidement, même avec une connexion instable.', 'Install the site for quick access, even with an unstable connection.') }}
                        </template>
                    </p>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-end gap-2">
                <button
                    type="button"
                    class="rounded-md px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-navy dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                    @click="dismiss"
                >
                    {{ tr('Plus tard', 'Not now') }}
                </button>
                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-md bg-edsp-green px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#057633] disabled:cursor-wait disabled:opacity-70"
                    :disabled="installing"
                    @click="install"
                >
                    <Share2 v-if="isIos" :size="16" aria-hidden="true" />
                    <Download v-else :size="16" aria-hidden="true" />
                    {{ actionLabel }}
                </button>
            </div>
        </aside>
    </Transition>
</template>

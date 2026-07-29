<script setup lang="ts">
import { Cookie, LockKeyhole, ShieldCheck, SlidersHorizontal, X } from 'lucide-vue-next';
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    announceCookieNoticeState,
    hasAcknowledgedCookieNotice,
    OPEN_COOKIE_NOTICE_EVENT,
    rememberCookieNotice,
} from '../../lib/cookie-notice';
import { useI18n } from '../../lib/i18n';

const { tr } = useI18n();
const visible = ref(false);
const detailsVisible = ref(false);
const openedFromFooter = ref(false);
const panel = ref<HTMLElement | null>(null);
let displayTimer: ReturnType<typeof setTimeout> | null = null;

function open(initialPrompt = false): void {
    openedFromFooter.value = !initialPrompt;
    detailsVisible.value = !initialPrompt;
    visible.value = true;
    announceCookieNoticeState(true);

    void nextTick(() => panel.value?.focus());
}

function close(): void {
    visible.value = false;
    detailsVisible.value = false;
    announceCookieNoticeState(false);
}

function acknowledge(): void {
    rememberCookieNotice();
    close();
}

function handleOpenRequest(): void {
    open(false);
}

function handleKeydown(event: KeyboardEvent): void {
    if (event.key !== 'Escape' || !visible.value) return;

    if (openedFromFooter.value) {
        close();
        return;
    }

    acknowledge();
}

onMounted(() => {
    window.addEventListener(OPEN_COOKIE_NOTICE_EVENT, handleOpenRequest);
    window.addEventListener('keydown', handleKeydown);

    if (!hasAcknowledgedCookieNotice()) {
        displayTimer = setTimeout(() => {
            open(true);
            displayTimer = null;
        }, 700);
    }
});

onBeforeUnmount(() => {
    if (displayTimer) clearTimeout(displayTimer);
    window.removeEventListener(OPEN_COOKIE_NOTICE_EVENT, handleOpenRequest);
    window.removeEventListener('keydown', handleKeydown);
    announceCookieNoticeState(false);
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
            v-if="visible"
            ref="panel"
            tabindex="-1"
            class="fixed inset-x-3 bottom-[calc(5.35rem+env(safe-area-inset-bottom))] z-[95] mx-auto max-h-[min(38rem,calc(100dvh-7rem))] max-w-xl overflow-y-auto rounded-xl border border-slate-200 bg-white p-4 text-navy shadow-[0_18px_55px_rgba(11,31,85,0.2)] dark:border-white/15 dark:bg-[#101c32] dark:text-white sm:inset-x-auto sm:bottom-6 sm:left-6 sm:w-[36rem] sm:p-5"
            role="dialog"
            :aria-labelledby="'cookie-notice-title'"
            :aria-describedby="'cookie-notice-description'"
        >
            <button
                v-if="openedFromFooter"
                type="button"
                class="absolute right-2.5 top-2.5 grid size-8 place-items-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-navy dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                :aria-label="tr('Fermer les préférences de confidentialité', 'Close privacy preferences')"
                @click="close"
            >
                <X :size="17" aria-hidden="true" />
            </button>

            <div class="flex items-start gap-3" :class="{ 'pr-8': openedFromFooter }">
                <span class="grid size-10 flex-none place-items-center rounded-lg bg-edsp-green/10 text-edsp-green ring-1 ring-edsp-green/15 dark:bg-edsp-green/15">
                    <Cookie :size="21" aria-hidden="true" />
                </span>
                <div class="min-w-0">
                    <p id="cookie-notice-title" class="font-heading text-base font-bold sm:text-lg">
                        {{ tr('Votre vie privée, simplement', 'Your privacy, simply explained') }}
                    </p>
                    <p id="cookie-notice-description" class="mt-1.5 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        {{
                            tr(
                                'L’EDSP utilise uniquement les traceurs nécessaires au fonctionnement du site et à vos choix d’affichage. Aucun cookie publicitaire ni suivi commercial.',
                                'EDSP only uses trackers required for the website to work and to remember your display choices. No advertising cookies or commercial tracking.',
                            )
                        }}
                    </p>
                </div>
            </div>

            <div v-if="detailsVisible" class="mt-4 space-y-2.5 border-t border-slate-200 pt-4 dark:border-white/10">
                <div class="flex gap-3 rounded-lg bg-slate-50 p-3 dark:bg-white/[0.045]">
                    <LockKeyhole :size="18" class="mt-0.5 flex-none text-edsp-green" aria-hidden="true" />
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-bold">{{ tr('Fonctionnement essentiel', 'Essential operation') }}</p>
                            <span class="rounded-full bg-edsp-green/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-edsp-green">
                                {{ tr('Toujours actif', 'Always active') }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">
                            {{
                                tr(
                                    'Sécurité des formulaires, session, langue choisie et accès à l’administration. Ces éléments sont indispensables au service demandé.',
                                    'Form security, session, selected language and administration access. These items are required to provide the requested service.',
                                )
                            }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 rounded-lg bg-slate-50 p-3 dark:bg-white/[0.045]">
                    <SlidersHorizontal :size="18" class="mt-0.5 flex-none text-institutional dark:text-blue-300" aria-hidden="true" />
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-bold">{{ tr('Préférences locales', 'Local preferences') }}</p>
                            <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-institutional dark:bg-blue-400/10 dark:text-blue-200">
                                {{ tr('Sur cet appareil', 'On this device') }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">
                            {{
                                tr(
                                    'Le thème clair ou sombre, la fermeture de la proposition PWA et ce message sont mémorisés localement. Ils ne servent pas à vous suivre.',
                                    'Your light or dark theme, dismissal of the PWA suggestion and this notice are stored locally. They are not used to track you.',
                                )
                            }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 rounded-lg bg-slate-50 p-3 dark:bg-white/[0.045]">
                    <ShieldCheck :size="18" class="mt-0.5 flex-none text-slate-500 dark:text-slate-300" aria-hidden="true" />
                    <div>
                        <p class="text-sm font-bold">{{ tr('Publicité et mesure d’audience', 'Advertising and analytics') }}</p>
                        <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">
                            {{
                                tr(
                                    'Aucun outil publicitaire, pixel social ou service de mesure d’audience tiers n’est actuellement chargé.',
                                    'No advertising tool, social pixel or third-party analytics service is currently loaded.',
                                )
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link
                    href="/politique-de-confidentialite"
                    class="inline-flex justify-center rounded-md px-3 py-2 text-xs font-semibold text-institutional underline decoration-institutional/25 underline-offset-3 hover:text-edsp-green dark:text-blue-200 dark:hover:text-emerald-300"
                >
                    {{ tr('Politique de confidentialité', 'Privacy policy') }}
                </Link>
                <div class="flex flex-col-reverse gap-2 min-[420px]:flex-row min-[420px]:justify-end">
                    <button
                        v-if="!detailsVisible"
                        type="button"
                        class="rounded-md border border-slate-300 px-3.5 py-2.5 text-xs font-bold text-navy transition hover:border-slate-400 hover:bg-slate-50 dark:border-slate-600 dark:text-white dark:hover:bg-white/10"
                        @click="detailsVisible = true"
                    >
                        {{ tr('Voir les détails', 'View details') }}
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-edsp-green px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#067735]"
                        @click="acknowledge"
                    >
                        {{ openedFromFooter ? tr('Enregistrer et fermer', 'Save and close') : tr('J’ai compris', 'Got it') }}
                    </button>
                </div>
            </div>
        </aside>
    </Transition>
</template>

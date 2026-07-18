<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, X } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const page = usePage();
const panel = ref<HTMLElement | null>(null);
const closeButton = ref<HTMLButtonElement | null>(null);
const currentPath = computed(() => page.url.split('?')[0]);
let previousBodyOverflow = '';

const isCurrent = (path: string): boolean => path === '/'
    ? currentPath.value === '/'
    : currentPath.value.startsWith(path);

function close(): void {
    emit('close');
}

function trapFocus(event: KeyboardEvent): void {
    if (event.key !== 'Tab' || !panel.value) {
        return;
    }

    const focusable = Array.from(
        panel.value.querySelectorAll<HTMLElement>('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'),
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

watch(
    () => props.open,
    async (open) => {
        if (open) {
            previousBodyOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
            await nextTick();
            closeButton.value?.focus();
            return;
        }

        document.body.style.overflow = previousBodyOverflow;
    },
);

onBeforeUnmount(() => {
    document.body.style.overflow = previousBodyOverflow;
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-4 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-4 opacity-0"
    >
        <div
            v-if="open"
            ref="panel"
            id="mobile-navigation"
            class="fixed inset-x-0 top-0 bottom-[calc(4rem+env(safe-area-inset-bottom))] z-[60] flex flex-col bg-white min-[1280px]:hidden"
            role="dialog"
            aria-modal="true"
            aria-labelledby="mobile-navigation-title"
            @keydown.esc.prevent="close"
            @keydown="trapFocus"
        >
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3.5 sm:px-6">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-edsp-green">EDSP</p>
                    <h2 id="mobile-navigation-title" class="mt-0.5 font-heading text-lg font-bold text-navy">Menu principal</h2>
                </div>
                <button
                    ref="closeButton"
                    type="button"
                    class="grid size-11 place-items-center rounded-xl border border-slate-200 text-navy transition hover:border-edsp-green hover:bg-soft hover:text-edsp-green"
                    aria-label="Fermer le menu principal"
                    @click="close"
                >
                    <X :size="22" aria-hidden="true" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto overscroll-contain px-4 py-5 sm:px-6" aria-label="Menu mobile complet">
                <div class="mx-auto max-w-xl pb-6">
                    <Link
                        href="/"
                        class="mobile-nav-link flex items-center justify-between"
                        :class="isCurrent('/') && 'bg-edsp-green/10 text-edsp-green'"
                        :aria-current="isCurrent('/') ? 'page' : undefined"
                        @click="close"
                    >
                        Accueil
                        <ArrowRight :size="16" aria-hidden="true" />
                    </Link>

                    <div class="mt-4 rounded-2xl border border-slate-200 bg-soft/70 p-2">
                        <p class="nav-group-label mt-1">L'École</p>
                        <Link href="/presentation" class="mobile-nav-sublink block" @click="close">Présentation</Link>
                        <Link href="/historique" class="mobile-nav-sublink block" @click="close">Historique</Link>
                        <Link href="/missions-et-valeurs" class="mobile-nav-sublink block" @click="close">Missions et valeurs</Link>
                        <Link href="/equipe" class="mobile-nav-sublink block" @click="close">Direction et équipe</Link>
                    </div>

                    <div class="mt-3 rounded-2xl border border-slate-200 bg-soft/70 p-2">
                        <p class="nav-group-label mt-1">Formations</p>
                        <Link href="/formations" class="mobile-nav-sublink block" @click="close">Nos parcours</Link>
                        <Link href="/admissions" class="mobile-nav-sublink block" @click="close">Admissions</Link>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <Link
                            href="/vie-etudiante"
                            class="mobile-nav-link border border-slate-200 text-center"
                            :class="isCurrent('/vie-etudiante') && 'border-edsp-green/20 bg-edsp-green/10 text-edsp-green'"
                            @click="close"
                        >
                            Vie étudiante
                        </Link>
                        <Link
                            href="/bibliotheque"
                            class="mobile-nav-link border border-slate-200 text-center"
                            :class="isCurrent('/bibliotheque') && 'border-edsp-green/20 bg-edsp-green/10 text-edsp-green'"
                            @click="close"
                        >
                            Bibliothèque
                        </Link>
                        <Link
                            href="/actualites"
                            class="mobile-nav-link border border-slate-200 text-center"
                            :class="isCurrent('/actualites') && 'border-edsp-green/20 bg-edsp-green/10 text-edsp-green'"
                            @click="close"
                        >
                            Actualités
                        </Link>
                        <Link
                            href="/contact"
                            class="mobile-nav-link border border-slate-200 text-center"
                            :class="isCurrent('/contact') && 'border-edsp-green/20 bg-edsp-green/10 text-edsp-green'"
                            @click="close"
                        >
                            Contact
                        </Link>
                    </div>

                    <Link
                        href="/preinscription"
                        class="mt-5 flex items-center justify-center gap-2 rounded-xl bg-edsp-green px-5 py-3.5 text-center font-heading text-sm font-bold text-white shadow-[0_10px_24px_rgba(7,139,62,0.2)] transition hover:bg-[#067735]"
                        @click="close"
                    >
                        Faire une préinscription
                        <ArrowRight :size="17" aria-hidden="true" />
                    </Link>
                </div>
            </nav>
        </div>
    </Transition>
</template>

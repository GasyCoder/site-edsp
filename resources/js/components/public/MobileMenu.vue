<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    ChevronDown,
    FileText,
    GraduationCap,
    LayoutDashboard,
    School,
    UserPlus,
    UsersRound,
    X,
} from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import type { SharedPageProps } from '../../types';
import DisplayPreferences from './DisplayPreferences.vue';
import { useI18n } from '../../lib/i18n';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const page = usePage();
const shared = computed(() => page.props as SharedPageProps);
const canAccessAdmin = computed(() => shared.value.auth?.canAccessAdmin === true);
const brochures = computed(() => shared.value.navigationBrochures ?? []);
const { tr } = useI18n();
const panel = ref<HTMLElement | null>(null);
const closeButton = ref<HTMLButtonElement | null>(null);
const currentPath = computed(() => page.url.split('?')[0]);
type MobileMenuGroup = 'school' | 'programmes' | 'campus';
const openGroup = ref<MobileMenuGroup | null>(null);
let previousBodyOverflow = '';

const isCurrent = (path: string): boolean => path === '/'
    ? currentPath.value === '/'
    : currentPath.value.startsWith(path);

const schoolPaths = ['/presentation', '/historique', '/missions-et-valeurs', '/equipe', '/documents'];
const programmePaths = ['/formations', '/admissions'];
const campusPaths = ['/vie-etudiante', '/bibliotheque', '/actualites', '/contact'];

const isGroupCurrent = (paths: string[]): boolean => paths.some((path) => isCurrent(path));

function toggleGroup(group: MobileMenuGroup): void {
    openGroup.value = openGroup.value === group ? null : group;
}

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
            openGroup.value = isGroupCurrent(schoolPaths)
                ? 'school'
                : isGroupCurrent(programmePaths)
                    ? 'programmes'
                    : isGroupCurrent(campusPaths)
                        ? 'campus'
                        : null;
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
            class="fixed inset-x-0 top-0 bottom-[calc(4rem+env(safe-area-inset-bottom))] z-[60] flex flex-col bg-white dark:bg-slate-950 min-[1280px]:hidden"
            role="dialog"
            aria-modal="true"
            aria-labelledby="mobile-navigation-title"
            @keydown.esc.prevent="close"
            @keydown="trapFocus"
        >
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 sm:px-6 dark:border-slate-800">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-edsp-green">EDSP</p>
                    <h2 id="mobile-navigation-title" class="mt-0.5 font-heading text-base font-bold text-navy">{{ tr('Menu principal', 'Main menu') }}</h2>
                </div>
                <button
                    ref="closeButton"
                    type="button"
                    class="grid size-10 place-items-center rounded-lg border border-slate-200 text-navy transition hover:border-edsp-green hover:bg-soft hover:text-edsp-green dark:border-slate-700 dark:text-slate-100 dark:hover:border-edsp-green dark:hover:bg-slate-800 dark:hover:text-emerald-300"
                    :aria-label="tr('Fermer le menu principal', 'Close main menu')"
                    @click="close"
                >
                    <X :size="20" aria-hidden="true" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto overscroll-contain px-4 py-3 sm:px-6" :aria-label="tr('Menu mobile complet', 'Full mobile menu')">
                <div class="mx-auto max-w-xl pb-4">
                    <Link
                        href="/"
                        class="mobile-nav-link flex items-center justify-between border border-transparent"
                        :class="isCurrent('/') && 'bg-edsp-green/10 text-edsp-green'"
                        :aria-current="isCurrent('/') ? 'page' : undefined"
                        @click="close"
                    >
                        {{ tr('Accueil', 'Home') }}
                        <ArrowRight :size="16" aria-hidden="true" />
                    </Link>

                    <div class="mt-2 space-y-2">
                        <section class="mobile-menu-group" :class="isGroupCurrent(schoolPaths) && 'mobile-menu-group-active'">
                            <button
                                type="button"
                                class="mobile-menu-trigger"
                                :aria-expanded="openGroup === 'school'"
                                aria-controls="mobile-school-links"
                                @click="toggleGroup('school')"
                            >
                                <span class="mobile-menu-trigger-icon"><School :size="17" aria-hidden="true" /></span>
                                <span class="min-w-0 flex-1 text-left">
                                    <span class="block text-sm font-bold">{{ tr("L'École", 'The School') }}</span>
                                    <span class="block truncate text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                        {{ tr('Présentation, direction et documents', 'About, leadership and documents') }}
                                    </span>
                                </span>
                                <ChevronDown :size="17" class="flex-none transition-transform duration-200" :class="openGroup === 'school' && 'rotate-180'" aria-hidden="true" />
                            </button>
                            <Transition name="mobile-accordion">
                                <div v-if="openGroup === 'school'" id="mobile-school-links" class="mobile-menu-panel">
                                    <Link href="/presentation" class="mobile-nav-sublink" :class="isCurrent('/presentation') && 'mobile-nav-sublink-active'" :aria-current="isCurrent('/presentation') ? 'page' : undefined" @click="close">{{ tr('Mot du directeur', "Director's message") }}</Link>
                                    <Link href="/historique" class="mobile-nav-sublink" :class="isCurrent('/historique') && 'mobile-nav-sublink-active'" :aria-current="isCurrent('/historique') ? 'page' : undefined" @click="close">{{ tr('Historique', 'History') }}</Link>
                                    <Link href="/missions-et-valeurs" class="mobile-nav-sublink" :class="isCurrent('/missions-et-valeurs') && 'mobile-nav-sublink-active'" :aria-current="isCurrent('/missions-et-valeurs') ? 'page' : undefined" @click="close">{{ tr('Missions et valeurs', 'Mission and values') }}</Link>
                                    <Link href="/equipe" class="mobile-nav-sublink" :class="isCurrent('/equipe') && 'mobile-nav-sublink-active'" :aria-current="isCurrent('/equipe') ? 'page' : undefined" @click="close">{{ tr('Direction et équipe', 'Leadership and team') }}</Link>
                                    <Link href="/documents" class="mobile-nav-sublink" :class="isCurrent('/documents') && 'mobile-nav-sublink-active'" :aria-current="isCurrent('/documents') ? 'page' : undefined" @click="close">{{ tr('Documents publics', 'Public documents') }}</Link>
                                </div>
                            </Transition>
                        </section>

                        <section class="mobile-menu-group" :class="isGroupCurrent(programmePaths) && 'mobile-menu-group-active'">
                            <button
                                type="button"
                                class="mobile-menu-trigger"
                                :aria-expanded="openGroup === 'programmes'"
                                aria-controls="mobile-programme-links"
                                @click="toggleGroup('programmes')"
                            >
                                <span class="mobile-menu-trigger-icon"><GraduationCap :size="17" aria-hidden="true" /></span>
                                <span class="min-w-0 flex-1 text-left">
                                    <span class="block text-sm font-bold">{{ tr('Formations', 'Programmes') }}</span>
                                    <span class="block truncate text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                        {{ tr('Parcours, admissions et brochures', 'Programmes, admissions and brochures') }}
                                    </span>
                                </span>
                                <ChevronDown :size="17" class="flex-none transition-transform duration-200" :class="openGroup === 'programmes' && 'rotate-180'" aria-hidden="true" />
                            </button>
                            <Transition name="mobile-accordion">
                                <div v-if="openGroup === 'programmes'" id="mobile-programme-links" class="mobile-menu-panel">
                                    <Link href="/formations" class="mobile-nav-sublink" :class="isCurrent('/formations') && 'mobile-nav-sublink-active'" :aria-current="isCurrent('/formations') ? 'page' : undefined" @click="close">{{ tr('Nos parcours', 'Our programmes') }}</Link>
                                    <Link href="/admissions" class="mobile-nav-sublink" :class="isCurrent('/admissions') && 'mobile-nav-sublink-active'" :aria-current="isCurrent('/admissions') ? 'page' : undefined" @click="close">{{ tr('Admissions', 'Admissions') }}</Link>
                                    <template v-if="brochures.length">
                                        <p class="nav-group-label border-t border-slate-200 pt-2 dark:border-slate-700">{{ tr('Brochures', 'Brochures') }}</p>
                                        <a
                                            v-for="brochure in brochures"
                                            :key="brochure.id"
                                            :href="brochure.url"
                                            target="_blank"
                                            rel="noopener"
                                            class="mobile-nav-sublink"
                                            @click="close"
                                        >
                                            <FileText :size="15" class="flex-none text-edsp-green" aria-hidden="true" />
                                            <span class="min-w-0 truncate">{{ brochure.title }}</span>
                                        </a>
                                    </template>
                                </div>
                            </Transition>
                        </section>

                        <section class="mobile-menu-group" :class="isGroupCurrent(campusPaths) && 'mobile-menu-group-active'">
                            <button
                                type="button"
                                class="mobile-menu-trigger"
                                :aria-expanded="openGroup === 'campus'"
                                aria-controls="mobile-campus-links"
                                @click="toggleGroup('campus')"
                            >
                                <span class="mobile-menu-trigger-icon"><UsersRound :size="17" aria-hidden="true" /></span>
                                <span class="min-w-0 flex-1 text-left">
                                    <span class="block text-sm font-bold">{{ tr('Vie du campus', 'Campus life') }}</span>
                                    <span class="block truncate text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                        {{ tr('Vie étudiante, actualités et contact', 'Student life, news and contact') }}
                                    </span>
                                </span>
                                <ChevronDown :size="17" class="flex-none transition-transform duration-200" :class="openGroup === 'campus' && 'rotate-180'" aria-hidden="true" />
                            </button>
                            <Transition name="mobile-accordion">
                                <div v-if="openGroup === 'campus'" id="mobile-campus-links" class="mobile-menu-panel sm:grid sm:grid-cols-2">
                                    <Link href="/vie-etudiante" class="mobile-nav-sublink" :class="isCurrent('/vie-etudiante') && 'mobile-nav-sublink-active'" :aria-current="isCurrent('/vie-etudiante') ? 'page' : undefined" @click="close">{{ tr('Vie étudiante', 'Student life') }}</Link>
                                    <Link href="/bibliotheque" class="mobile-nav-sublink" :class="isCurrent('/bibliotheque') && 'mobile-nav-sublink-active'" :aria-current="isCurrent('/bibliotheque') ? 'page' : undefined" @click="close">{{ tr('Bibliothèque', 'Library') }}</Link>
                                    <Link href="/actualites" class="mobile-nav-sublink" :class="isCurrent('/actualites') && 'mobile-nav-sublink-active'" :aria-current="isCurrent('/actualites') ? 'page' : undefined" @click="close">{{ tr('Actualités', 'News') }}</Link>
                                    <Link href="/contact" class="mobile-nav-sublink" :class="isCurrent('/contact') && 'mobile-nav-sublink-active'" :aria-current="isCurrent('/contact') ? 'page' : undefined" @click="close">{{ tr('Contact', 'Contact') }}</Link>
                                </div>
                            </Transition>
                        </section>
                    </div>

                    <Link
                        href="/inscription"
                        class="mt-3 flex items-center justify-center gap-2 rounded-md bg-edsp-green px-5 py-2.5 text-center font-heading text-sm font-bold text-white transition hover:bg-[#067735]"
                        @click="close"
                    >
                        <UserPlus :size="17" aria-hidden="true" />
                        {{ tr('Faire une inscription', 'Apply now') }}
                    </Link>

                    <a
                        v-if="canAccessAdmin"
                        href="/admin"
                        class="mt-2 flex items-center justify-center gap-2 rounded-md border border-navy/15 bg-navy px-5 py-2.5 text-center font-heading text-sm font-bold text-white transition hover:bg-institutional dark:border-institutional dark:bg-institutional/80 dark:hover:bg-institutional"
                        @click="close"
                    >
                        <LayoutDashboard :size="17" aria-hidden="true" />
                        {{ tr('Accéder à l’administration', 'Open administration') }}
                    </a>

                    <DisplayPreferences class="mt-3 flex justify-end" />
                </div>
            </nav>
        </div>
    </Transition>
</template>

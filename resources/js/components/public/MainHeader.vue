<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, Menu, X } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import type { SiteSettings } from '../../types';
import { setting } from '../../lib/public-content';
import MobileBottomNavigation from './MobileBottomNavigation.vue';
import MobileMenu from './MobileMenu.vue';

const props = defineProps<{
    settings?: SiteSettings;
}>();

const page = usePage();
const header = ref<HTMLElement | null>(null);
const menuButton = ref<HTMLButtonElement | null>(null);
const menuOpen = ref(false);
const activeDropdown = ref<'school' | 'programs' | null>(null);
const logoUrl = computed(() => setting(props.settings, 'logo_url', '/images/logo-edsp.png'));
const siteName = 'EDSP';
const institutionName = 'École de Droit et Sciences Politique';

function toggleDropdown(dropdown: 'school' | 'programs') {
    activeDropdown.value = activeDropdown.value === dropdown ? null : dropdown;
}

function closeMenus() {
    const shouldRestoreFocus = menuOpen.value;
    activeDropdown.value = null;
    menuOpen.value = false;

    if (shouldRestoreFocus) {
        void nextTick(() => menuButton.value?.focus());
    }
}

function setMenuOpen(open: boolean) {
    if (open) {
        menuOpen.value = true;
        return;
    }

    closeMenus();
}

function closeOnOutsideClick(event: MouseEvent) {
    if (header.value && !header.value.contains(event.target as Node)) {
        activeDropdown.value = null;
        menuOpen.value = false;
    }
}

function closeOnEscape(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        closeMenus();
    }
}

function closeDropdownOnFocusOut(event: FocusEvent) {
    const container = event.currentTarget as HTMLElement;
    const nextTarget = event.relatedTarget as Node | null;

    if (!nextTarget || !container.contains(nextTarget)) {
        activeDropdown.value = null;
    }
}

const currentPath = computed(() => page.url.split('?')[0]);
const isCurrent = (path: string) =>
    path === '/' ? currentPath.value === '/' : currentPath.value.startsWith(path);

onMounted(() => {
    document.addEventListener('click', closeOnOutsideClick);
    document.addEventListener('keydown', closeOnEscape);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', closeOnOutsideClick);
    document.removeEventListener('keydown', closeOnEscape);
});
</script>

<template>
    <header
        ref="header"
        class="sticky top-0 z-50 border-b border-slate-200 bg-white shadow-[0_4px_14px_rgba(11,31,85,0.06)]"
    >
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2.5 sm:px-6">
            <Link href="/" class="flex min-w-0 items-center gap-3" aria-label="Accueil de l'EDSP">
                <img :src="logoUrl" :alt="`Logo — ${siteName}`" class="h-14 w-auto flex-none sm:h-16" />
                <span class="min-w-0">
                    <span class="block text-pretty font-heading text-xs font-bold leading-snug text-navy sm:text-sm">
                        {{ siteName }}
                    </span>
                    <span class="mt-0.5 block text-[11px] font-semibold text-edsp-green">
                        {{ institutionName }}
                    </span>
                </span>
            </Link>

            <nav class="hidden items-center gap-5 min-[1280px]:flex" aria-label="Navigation principale">
                <Link href="/" class="desktop-nav-link" :class="{ 'text-edsp-green': isCurrent('/') }">Accueil</Link>

                <div
                    class="relative"
                    @mouseenter="activeDropdown = 'school'"
                    @mouseleave="activeDropdown = null"
                    @focusin="activeDropdown = 'school'"
                    @focusout="closeDropdownOnFocusOut"
                >
                    <button
                        type="button"
                        class="desktop-nav-link inline-flex items-center gap-1"
                        :class="{ 'text-edsp-green': isCurrent('/presentation') || isCurrent('/equipe') }"
                        :aria-expanded="activeDropdown === 'school'"
                        aria-controls="school-navigation"
                        @click.stop="toggleDropdown('school')"
                    >
                        L'École
                        <ChevronDown
                            :size="15"
                            :class="['transition', activeDropdown === 'school' && 'rotate-180']"
                            aria-hidden="true"
                        />
                    </button>
                    <Transition name="dropdown">
                        <div
                            v-if="activeDropdown === 'school'"
                            id="school-navigation"
                            class="nav-dropdown left-[-0.75rem]"
                        >
                            <Link href="/presentation" class="nav-dropdown-link" @click="closeMenus">Présentation</Link>
                            <Link href="/historique" class="nav-dropdown-link" @click="closeMenus">Historique</Link>
                            <Link href="/missions-et-valeurs" class="nav-dropdown-link" @click="closeMenus">
                                Missions et valeurs
                            </Link>
                            <Link href="/equipe" class="nav-dropdown-link" @click="closeMenus">Direction et équipe</Link>
                        </div>
                    </Transition>
                </div>

                <div
                    class="relative"
                    @mouseenter="activeDropdown = 'programs'"
                    @mouseleave="activeDropdown = null"
                    @focusin="activeDropdown = 'programs'"
                    @focusout="closeDropdownOnFocusOut"
                >
                    <button
                        type="button"
                        class="desktop-nav-link inline-flex items-center gap-1"
                        :class="{ 'text-edsp-green': isCurrent('/formations') || isCurrent('/preinscription') }"
                        :aria-expanded="activeDropdown === 'programs'"
                        aria-controls="program-navigation"
                        @click.stop="toggleDropdown('programs')"
                    >
                        Formations
                        <ChevronDown
                            :size="15"
                            :class="['transition', activeDropdown === 'programs' && 'rotate-180']"
                            aria-hidden="true"
                        />
                    </button>
                    <Transition name="dropdown">
                        <div
                            v-if="activeDropdown === 'programs'"
                            id="program-navigation"
                            class="nav-dropdown left-[-0.75rem]"
                        >
                            <Link href="/formations" class="nav-dropdown-link" @click="closeMenus">Nos parcours</Link>
                            <Link href="/admissions" class="nav-dropdown-link" @click="closeMenus">Admissions</Link>
                        </div>
                    </Transition>
                </div>

                <Link
                    href="/vie-etudiante"
                    class="desktop-nav-link"
                    :class="{ 'text-edsp-green': isCurrent('/vie-etudiante') }"
                >
                    Vie étudiante
                </Link>
                <Link
                    href="/bibliotheque"
                    class="desktop-nav-link"
                    :class="{ 'text-edsp-green': isCurrent('/bibliotheque') }"
                >
                    Bibliothèque
                </Link>
                <Link
                    href="/actualites"
                    class="desktop-nav-link"
                    :class="{ 'text-edsp-green': isCurrent('/actualites') }"
                >
                    Actualités
                </Link>
                <Link
                    href="/contact"
                    class="desktop-nav-link"
                    :class="{ 'text-edsp-green': isCurrent('/contact') }"
                >
                    Contact
                </Link>
                <Link
                    href="/preinscription"
                    class="rounded-md bg-edsp-green px-5 py-2.5 font-heading text-sm font-semibold text-white transition hover:bg-[#067735]"
                >
                    Préinscription
                </Link>
            </nav>

            <button
                ref="menuButton"
                type="button"
                class="grid size-11 flex-none place-items-center rounded-md border border-slate-300 text-navy transition hover:border-edsp-green hover:text-edsp-green min-[1280px]:hidden"
                :aria-label="menuOpen ? 'Fermer le menu' : 'Ouvrir le menu'"
                :aria-expanded="menuOpen"
                aria-controls="mobile-navigation"
                @click.stop="setMenuOpen(!menuOpen)"
            >
                <X v-if="menuOpen" :size="22" aria-hidden="true" />
                <Menu v-else :size="22" aria-hidden="true" />
            </button>
        </div>

        <MobileMenu :open="menuOpen" @close="setMenuOpen(false)" />
        <MobileBottomNavigation
            :menu-open="menuOpen"
            @close="setMenuOpen(false)"
            @toggle="setMenuOpen(!menuOpen)"
        />
    </header>
</template>

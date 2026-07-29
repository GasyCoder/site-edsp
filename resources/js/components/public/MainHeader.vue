<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, FileText, LayoutDashboard, Menu, UserPlus, X } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import type { SharedPageProps, SiteSettings } from '../../types';
import { setting } from '../../lib/public-content';
import MobileBottomNavigation from './MobileBottomNavigation.vue';
import MobileMenu from './MobileMenu.vue';
import DisplayPreferences from './DisplayPreferences.vue';
import { useI18n } from '../../lib/i18n';

const props = defineProps<{
    settings?: SiteSettings;
}>();

const page = usePage();
const shared = computed(() => page.props as SharedPageProps);
const canAccessAdmin = computed(() => shared.value.auth?.canAccessAdmin === true);
const brochures = computed(() => shared.value.navigationBrochures ?? []);
const header = ref<HTMLElement | null>(null);
const menuButton = ref<HTMLButtonElement | null>(null);
const menuOpen = ref(false);
const activeDropdown = ref<'school' | 'programs' | null>(null);
const logoUrl = computed(() => setting(props.settings, 'logo_url', '/images/logo-edsp.png'));
const darkLogoUrl = computed(() => setting(props.settings, 'logo_dark_url', '/images/logo-edsp-transparent.png'));
const siteName = 'EDSP';
const { tr } = useI18n();
const institutionName = computed(() => setting(
    props.settings,
    'institution_name',
    tr('École de Droit et Sciences Politique', 'School of Law and Political Science'),
));

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
        class="sticky top-0 z-50 border-b border-slate-200 bg-white/98 dark:border-slate-700 dark:bg-slate-950"
    >
        <div class="px-4 sm:px-6">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 py-2">
            <Link href="/" class="flex min-w-0 items-center gap-3" :aria-label="tr(`Accueil de l'EDSP`, 'EDSP home')">
                <span class="grid size-12 flex-none place-items-center overflow-hidden rounded-lg sm:size-14 dark:bg-white/95 dark:p-1">
                    <img :src="logoUrl" :alt="`Logo ${siteName}`" class="max-h-full max-w-full object-contain dark:hidden" />
                    <img :src="darkLogoUrl" :alt="`Logo ${siteName}`" class="hidden max-h-full max-w-full object-contain dark:block" />
                </span>
                <span class="min-w-0 max-w-48">
                    <span class="block text-pretty font-heading text-xs font-bold leading-snug text-navy sm:text-sm">
                        {{ siteName }}
                    </span>
                    <span class="mt-0.5 block text-[11px] font-semibold text-edsp-green">
                        {{ institutionName }}
                    </span>
                </span>
            </Link>

            <nav class="hidden items-center gap-3 min-[1280px]:flex" :aria-label="tr('Navigation principale', 'Main navigation')">
                <Link href="/" class="desktop-nav-link" :class="{ 'text-edsp-green': isCurrent('/') }">{{ tr('Accueil', 'Home') }}</Link>

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
                        :class="{ 'text-edsp-green': isCurrent('/presentation') || isCurrent('/equipe') || isCurrent('/bibliotheque') || isCurrent('/documents') }"
                        :aria-expanded="activeDropdown === 'school'"
                        aria-controls="school-navigation"
                        @click.stop="toggleDropdown('school')"
                    >
                        {{ tr("L'École", 'The School') }}
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
                            <Link href="/presentation" class="nav-dropdown-link" @click="closeMenus">{{ tr('Mot du directeur', "Director's message") }}</Link>
                            <Link href="/historique" class="nav-dropdown-link" @click="closeMenus">{{ tr('Historique', 'History') }}</Link>
                            <Link href="/missions-et-valeurs" class="nav-dropdown-link" @click="closeMenus">
                                {{ tr('Missions et valeurs', 'Mission and values') }}
                            </Link>
                            <Link href="/equipe" class="nav-dropdown-link" @click="closeMenus">{{ tr('Direction et équipe', 'Leadership and team') }}</Link>
                            <Link href="/bibliotheque" class="nav-dropdown-link" @click="closeMenus">{{ tr('Bibliothèque', 'Library') }}</Link>
                            <Link href="/documents" class="nav-dropdown-link" @click="closeMenus">{{ tr('Documents publics', 'Public documents') }}</Link>
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
                        :class="{ 'text-edsp-green': isCurrent('/formations') || isCurrent('/inscription') }"
                        :aria-expanded="activeDropdown === 'programs'"
                        aria-controls="program-navigation"
                        @click.stop="toggleDropdown('programs')"
                    >
                        {{ tr('Formations', 'Programmes') }}
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
                            <Link href="/formations" class="nav-dropdown-link" @click="closeMenus">{{ tr('Nos parcours', 'Our programmes') }}</Link>
                            <Link href="/admissions" class="nav-dropdown-link" @click="closeMenus">{{ tr('Admissions', 'Admissions') }}</Link>
                            <template v-if="brochures.length">
                                <p class="nav-group-label border-t border-slate-200 pt-3 dark:border-slate-700">{{ tr('Brochures', 'Brochures') }}</p>
                                <a
                                    v-for="brochure in brochures"
                                    :key="brochure.id"
                                    :href="brochure.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="nav-dropdown-link flex items-center gap-2"
                                    @click="closeMenus"
                                >
                                    <FileText :size="16" class="flex-none text-edsp-green" aria-hidden="true" />
                                    <span class="min-w-0 truncate">{{ brochure.title }}</span>
                                </a>
                            </template>
                        </div>
                    </Transition>
                </div>

                <Link
                    href="/vie-etudiante"
                    class="desktop-nav-link"
                    :class="{ 'text-edsp-green': isCurrent('/vie-etudiante') }"
                >
                    {{ tr('Vie étudiante', 'Student life') }}
                </Link>
                <Link
                    href="/actualites"
                    class="desktop-nav-link"
                    :class="{ 'text-edsp-green': isCurrent('/actualites') }"
                >
                    {{ tr('Actualités', 'News') }}
                </Link>
                <Link
                    href="/contact"
                    class="desktop-nav-link"
                    :class="{ 'text-edsp-green': isCurrent('/contact') }"
                >
                    {{ tr('Contact', 'Contact') }}
                </Link>
                <DisplayPreferences />
                <a
                    v-if="canAccessAdmin"
                    href="/admin"
                    class="grid size-10 flex-none place-items-center rounded-md border border-navy/20 text-navy transition hover:border-edsp-green hover:bg-edsp-green/5 hover:text-edsp-green"
                    :title="tr('Accéder au back-office', 'Open the administration area')"
                    :aria-label="tr(`Accéder au back-office d’administration`, 'Open the administration area')"
                >
                    <LayoutDashboard :size="18" aria-hidden="true" />
                </a>
                <Link
                    href="/inscription"
                    class="inline-flex items-center gap-2 rounded-md bg-edsp-green px-5 py-2.5 font-heading text-sm font-semibold text-white transition hover:bg-[#067735]"
                >
                    <UserPlus :size="17" aria-hidden="true" />
                    {{ tr('Inscription', 'Apply') }}
                </Link>
            </nav>

            <button
                ref="menuButton"
                type="button"
                class="grid size-11 flex-none place-items-center rounded-md border border-slate-300 text-navy transition hover:border-edsp-green hover:text-edsp-green min-[1280px]:hidden"
                :aria-label="menuOpen ? tr('Fermer le menu', 'Close menu') : tr('Ouvrir le menu', 'Open menu')"
                :aria-expanded="menuOpen"
                aria-controls="mobile-navigation"
                @click.stop="setMenuOpen(!menuOpen)"
            >
                <X v-if="menuOpen" :size="22" aria-hidden="true" />
                <Menu v-else :size="22" aria-hidden="true" />
            </button>
            </div>
        </div>

        <MobileMenu :open="menuOpen" @close="setMenuOpen(false)" />
        <MobileBottomNavigation
            :menu-open="menuOpen"
            @close="setMenuOpen(false)"
            @toggle="setMenuOpen(!menuOpen)"
        />
    </header>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, GraduationCap, Home, Menu, UsersRound, X } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from '../../lib/i18n';

defineProps<{
    menuOpen: boolean;
}>();

const emit = defineEmits<{
    close: [];
    toggle: [];
}>();

const page = usePage();
const currentPath = computed(() => page.url.split('?')[0]);
const isCurrent = (path: string): boolean => path === '/'
    ? currentPath.value === '/'
    : currentPath.value.startsWith(path);
const { tr } = useI18n();
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-[70] border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] shadow-[0_-8px_30px_rgba(11,31,85,0.12)] backdrop-blur-xl dark:border-slate-700 dark:bg-slate-950/95 min-[1280px]:hidden"
        :aria-label="tr('Navigation rapide mobile', 'Quick mobile navigation')"
    >
        <div class="mx-auto grid max-w-xl grid-cols-5 px-1.5 py-1.5">
            <Link
                href="/"
                class="flex min-w-0 flex-col items-center gap-1 rounded-lg px-1 py-1.5 text-[10px] font-semibold transition"
                :class="isCurrent('/') ? 'bg-edsp-green/10 text-edsp-green dark:bg-edsp-green/15 dark:text-emerald-300' : 'text-slate-500 hover:bg-soft hover:text-navy dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100'"
                :aria-current="isCurrent('/') ? 'page' : undefined"
                @click="emit('close')"
            >
                <Home :size="20" aria-hidden="true" />
                <span>{{ tr('Accueil', 'Home') }}</span>
            </Link>

            <Link
                href="/formations"
                class="flex min-w-0 flex-col items-center gap-1 rounded-lg px-1 py-1.5 text-[10px] font-semibold transition"
                :class="isCurrent('/formations') ? 'bg-edsp-green/10 text-edsp-green dark:bg-edsp-green/15 dark:text-emerald-300' : 'text-slate-500 hover:bg-soft hover:text-navy dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100'"
                :aria-current="isCurrent('/formations') ? 'page' : undefined"
                @click="emit('close')"
            >
                <GraduationCap :size="21" aria-hidden="true" />
                <span>{{ tr('Formations', 'Courses') }}</span>
            </Link>

            <Link
                href="/vie-etudiante"
                class="flex min-w-0 flex-col items-center gap-1 rounded-lg px-1 py-1.5 text-[10px] font-semibold transition"
                :class="isCurrent('/vie-etudiante') ? 'bg-edsp-green/10 text-edsp-green dark:bg-edsp-green/15 dark:text-emerald-300' : 'text-slate-500 hover:bg-soft hover:text-navy dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100'"
                :aria-current="isCurrent('/vie-etudiante') ? 'page' : undefined"
                @click="emit('close')"
            >
                <UsersRound :size="20" aria-hidden="true" />
                <span class="max-w-full truncate">{{ tr('Vie étud.', 'Student life') }}</span>
            </Link>

            <Link
                href="/bibliotheque"
                class="flex min-w-0 flex-col items-center gap-1 rounded-lg px-1 py-1.5 text-[10px] font-semibold transition"
                :class="isCurrent('/bibliotheque') ? 'bg-edsp-green/10 text-edsp-green dark:bg-edsp-green/15 dark:text-emerald-300' : 'text-slate-500 hover:bg-soft hover:text-navy dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100'"
                :aria-current="isCurrent('/bibliotheque') ? 'page' : undefined"
                @click="emit('close')"
            >
                <BookOpen :size="20" aria-hidden="true" />
                <span class="max-w-full truncate">{{ tr('Bibliothèque', 'Library') }}</span>
            </Link>

            <button
                type="button"
                class="flex min-w-0 flex-col items-center gap-1 rounded-lg px-1 py-1.5 text-[10px] font-semibold transition"
                :class="menuOpen ? 'bg-navy text-white dark:bg-institutional' : 'text-slate-500 hover:bg-soft hover:text-navy dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100'"
                :aria-expanded="menuOpen"
                aria-controls="mobile-navigation"
                :aria-label="menuOpen ? tr('Fermer le menu principal', 'Close main menu') : tr('Ouvrir le menu principal', 'Open main menu')"
                @click="emit('toggle')"
            >
                <X v-if="menuOpen" :size="20" aria-hidden="true" />
                <Menu v-else :size="20" aria-hidden="true" />
                <span>{{ tr('Menu', 'Menu') }}</span>
            </button>
        </div>
    </nav>
</template>

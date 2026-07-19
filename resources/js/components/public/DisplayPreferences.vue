<script setup lang="ts">
import { ChevronDown, Languages, Moon, Sun } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useColorMode } from '../../lib/color-mode';
import { useI18n, type PublicLocale } from '../../lib/i18n';

withDefaults(defineProps<{
    expanded?: boolean;
}>(), {
    expanded: false,
});

const { isDark, toggleMode } = useColorMode();
const { locale, setLocale, tr } = useI18n();
const compactRoot = ref<HTMLElement | null>(null);
const compactOpen = ref(false);
const localeFlag = (value: PublicLocale): string => value === 'fr' ? '🇫🇷' : '🇬🇧';

function chooseLocale(nextLocale: PublicLocale): void {
    compactOpen.value = false;
    setLocale(nextLocale);
}

function toggleTheme(): void {
    toggleMode();
    compactOpen.value = false;
}

function closeOnOutsideClick(event: MouseEvent): void {
    if (compactRoot.value && !compactRoot.value.contains(event.target as Node)) {
        compactOpen.value = false;
    }
}

function closeOnEscape(event: KeyboardEvent): void {
    if (event.key === 'Escape') compactOpen.value = false;
}

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
    <div v-if="expanded" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
        <div class="flex items-center justify-between gap-4">
            <span class="inline-flex items-center gap-2 text-sm font-semibold text-navy dark:text-slate-100">
                <Languages :size="17" aria-hidden="true" />
                {{ tr('Langue', 'Language') }}
            </span>
            <div class="inline-flex rounded-lg bg-soft p-1 dark:bg-slate-800" role="group" :aria-label="tr('Choisir la langue', 'Choose language')">
                <button
                    v-for="item in (['fr', 'en'] as const)"
                    :key="item"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-bold transition"
                    :class="locale === item ? 'bg-white text-edsp-green shadow-sm dark:bg-slate-700 dark:text-emerald-300' : 'text-slate-500 hover:text-navy dark:text-slate-400 dark:hover:text-white'"
                    :aria-pressed="locale === item"
                    @click="chooseLocale(item)"
                >
                    <span aria-hidden="true">{{ localeFlag(item) }}</span>
                    {{ item.toUpperCase() }}
                </button>
            </div>
        </div>
        <div class="flex items-center justify-between gap-4">
            <span class="inline-flex items-center gap-2 text-sm font-semibold text-navy dark:text-slate-100">
                <Sun v-if="!isDark" :size="17" aria-hidden="true" />
                <Moon v-else :size="17" aria-hidden="true" />
                {{ tr('Apparence', 'Appearance') }}
            </span>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-navy transition hover:border-edsp-green hover:text-edsp-green dark:border-slate-700 dark:text-slate-100"
                :aria-label="isDark ? tr('Activer le thème clair', 'Use light theme') : tr('Activer le thème sombre', 'Use dark theme')"
                @click="toggleTheme"
            >
                <Sun v-if="isDark" :size="15" aria-hidden="true" />
                <Moon v-else :size="15" aria-hidden="true" />
                {{ isDark ? tr('Mode clair', 'Light mode') : tr('Mode sombre', 'Dark mode') }}
            </button>
        </div>
    </div>

    <div v-else ref="compactRoot" class="relative">
        <button
            type="button"
            class="inline-flex h-10 items-center gap-1.5 rounded-md border border-slate-200 px-2.5 text-xs font-bold text-navy transition hover:border-edsp-green hover:text-edsp-green dark:border-slate-700 dark:text-slate-100"
            :title="tr('Langue et apparence', 'Language and appearance')"
            :aria-label="tr('Ouvrir les préférences d’affichage', 'Open display preferences')"
            :aria-expanded="compactOpen"
            @click.stop="compactOpen = !compactOpen"
        >
            <Languages :size="16" aria-hidden="true" />
            <span aria-hidden="true">{{ localeFlag(locale) }}</span>
            {{ locale.toUpperCase() }}
            <ChevronDown :size="13" :class="['transition', compactOpen && 'rotate-180']" aria-hidden="true" />
        </button>
        <Transition name="dropdown">
            <div v-if="compactOpen" class="absolute right-0 top-12 z-[70] w-64 rounded-xl border border-slate-200 bg-white p-3 shadow-[0_16px_38px_rgba(11,31,85,0.16)] dark:border-slate-700 dark:bg-slate-900">
                <p class="px-1 text-xs font-bold uppercase tracking-wider text-slate-500">{{ tr('Préférences', 'Preferences') }}</p>
                <div class="mt-3 flex items-center justify-between gap-3">
                    <span class="text-sm font-semibold text-navy dark:text-slate-100">{{ tr('Langue', 'Language') }}</span>
                    <div class="inline-flex rounded-lg bg-soft p-1 dark:bg-slate-800" role="group" :aria-label="tr('Choisir la langue', 'Choose language')">
                        <button
                            v-for="item in (['fr', 'en'] as const)"
                            :key="item"
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-bold transition"
                            :class="locale === item ? 'bg-white text-edsp-green shadow-sm dark:bg-slate-700 dark:text-emerald-300' : 'text-slate-500 hover:text-navy dark:text-slate-400 dark:hover:text-white'"
                            :aria-pressed="locale === item"
                            @click="chooseLocale(item)"
                        >
                            <span aria-hidden="true">{{ localeFlag(item) }}</span>
                            {{ item.toUpperCase() }}
                        </button>
                    </div>
                </div>
                <button
                    type="button"
                    class="mt-3 flex w-full items-center justify-between rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-semibold text-navy transition hover:border-edsp-green hover:text-edsp-green dark:border-slate-700 dark:text-slate-100"
                    @click="toggleTheme"
                >
                    <span>{{ isDark ? tr('Passer en mode clair', 'Switch to light mode') : tr('Passer en mode sombre', 'Switch to dark mode') }}</span>
                    <Sun v-if="isDark" :size="17" aria-hidden="true" />
                    <Moon v-else :size="17" aria-hidden="true" />
                </button>
            </div>
        </Transition>
    </div>
</template>

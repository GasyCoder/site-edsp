import { computed, ref } from 'vue';

export type ColorMode = 'light' | 'dark';

const STORAGE_KEY = 'edsp-color-mode';
const mode = ref<ColorMode>('light');
let initialized = false;

function preferredMode(): ColorMode {
    const saved = window.localStorage.getItem(STORAGE_KEY);

    if (saved === 'dark' || saved === 'light') return saved;

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function applyMode(nextMode: ColorMode): void {
    mode.value = nextMode;
    document.documentElement.classList.toggle('dark', nextMode === 'dark');
    document.documentElement.style.colorScheme = nextMode;
    document.querySelector<HTMLMetaElement>('meta[name="theme-color"]')
        ?.setAttribute('content', nextMode === 'dark' ? '#071126' : '#0B1F55');
}

export function initializeColorMode(): void {
    if (initialized || typeof window === 'undefined') return;

    initialized = true;
    applyMode(preferredMode());
}

export function useColorMode() {
    initializeColorMode();

    const isDark = computed(() => mode.value === 'dark');
    const setMode = (nextMode: ColorMode): void => {
        window.localStorage.setItem(STORAGE_KEY, nextMode);
        applyMode(nextMode);
    };
    const toggleMode = (): void => setMode(isDark.value ? 'light' : 'dark');

    return { isDark, mode, setMode, toggleMode };
}

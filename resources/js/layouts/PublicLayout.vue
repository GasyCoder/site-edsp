<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2 } from 'lucide-vue-next';
import { computed } from 'vue';
import type { SharedPageProps, SiteSettings } from '../types';
import BackToTopButton from '../components/public/BackToTopButton.vue';
import MainHeader from '../components/public/MainHeader.vue';
import PublicFooter from '../components/public/PublicFooter.vue';
import TopBar from '../components/public/TopBar.vue';

const props = withDefaults(
    defineProps<{
        settings?: SiteSettings;
    }>(),
    {
        settings: () => ({}),
    },
);

const page = usePage();
const shared = computed(() => page.props as SharedPageProps);
const resolvedSettings = computed<SiteSettings>(() => ({
    ...(shared.value.settings ?? {}),
    ...props.settings,
}));
const successMessage = computed(() => shared.value.flash?.success || null);
</script>

<template>
    <div class="min-h-screen bg-white text-slate-800">
        <a
            href="#main-content"
            class="fixed left-4 top-3 z-[100] -translate-y-24 rounded-md bg-white px-4 py-3 font-semibold text-navy shadow-xl transition focus:translate-y-0"
        >
            Aller au contenu principal
        </a>
        <TopBar :settings="resolvedSettings" />
        <MainHeader :settings="resolvedSettings" />

        <div
            v-if="successMessage"
            class="border-b border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900 sm:px-6"
            role="status"
            aria-live="polite"
        >
            <div class="mx-auto flex max-w-7xl items-center gap-2">
                <CheckCircle2 :size="18" class="flex-none text-edsp-green" aria-hidden="true" />
                {{ successMessage }}
            </div>
        </div>

        <main id="main-content" tabindex="-1">
            <slot />
        </main>

        <PublicFooter :settings="resolvedSettings" />
        <BackToTopButton />
    </div>
</template>

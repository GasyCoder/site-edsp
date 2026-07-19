<script setup lang="ts">
import { ArrowUp } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from '../../lib/i18n';

const visible = ref(false);
const { tr } = useI18n();

function updateVisibility() {
    visible.value = window.scrollY > 500;
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

onMounted(() => {
    updateVisibility();
    window.addEventListener('scroll', updateVisibility, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', updateVisibility);
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-200"
        enter-from-class="translate-y-2 opacity-0"
        leave-active-class="transition duration-150"
        leave-to-class="translate-y-2 opacity-0"
    >
        <button
            v-if="visible"
            type="button"
            class="fixed right-4 bottom-[calc(5.25rem+env(safe-area-inset-bottom))] z-30 grid size-11 place-items-center rounded-full bg-edsp-green text-white shadow-[0_8px_22px_rgba(7,139,62,0.36)] transition hover:bg-navy min-[1280px]:right-7 min-[1280px]:bottom-7 min-[1280px]:size-12"
            :aria-label="tr('Revenir en haut de la page', 'Back to top')"
            @click="scrollToTop"
        >
            <ArrowUp :size="21" :stroke-width="2.5" aria-hidden="true" />
        </button>
    </Transition>
</template>

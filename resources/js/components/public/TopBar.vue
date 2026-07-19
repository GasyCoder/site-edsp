<script setup lang="ts">
import { Mail, MapPin, Phone } from 'lucide-vue-next';
import { computed } from 'vue';
import type { SiteSettings } from '../../types';
import { setting } from '../../lib/public-content';
import { safePublicUrl } from '../../lib/public-content';
import SmartLink from './SmartLink.vue';
import { useI18n } from '../../lib/i18n';

const props = defineProps<{
    settings?: SiteSettings;
}>();

const address = computed(() => setting(props.settings, 'address', 'Ambondrona, Mahajanga'));
const email = computed(() => setting(props.settings, 'email', 'edsp.mahajanga@gmail.com'));
const phone = computed(() => setting(props.settings, 'phone', '+261 32 05 579 90'));
const facebookUrl = computed(() => safePublicUrl(props.settings?.facebook));
const { tr } = useI18n();
</script>

<template>
    <div class="bg-navy px-4 py-2 text-xs text-[#C9D4EE] sm:px-6">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-2 min-[760px]:flex-row min-[760px]:items-center min-[760px]:justify-between"
        >
            <div class="flex items-center">
                <span class="inline-flex items-center gap-1.5">
                    <MapPin :size="13" class="text-gold" aria-hidden="true" />
                    {{ address }}
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 min-[760px]:justify-end">
                <a
                    :href="`tel:${phone.replace(/\s/g, '')}`"
                    class="inline-flex items-center gap-1.5 transition hover:text-white"
                >
                    <Phone :size="13" class="text-gold" aria-hidden="true" />
                    {{ phone }}
                </a>
                <a :href="`mailto:${email}`" class="inline-flex items-center gap-1.5 transition hover:text-white">
                    <Mail :size="13" class="text-gold" aria-hidden="true" />
                    {{ email }}
                </a>
                <SmartLink
                    v-if="facebookUrl"
                    :href="facebookUrl"
                    class="inline-flex w-fit items-center gap-1.5 transition hover:text-white"
                    :aria-label="tr(`Page Facebook de l'EDSP (nouvel onglet)`, 'EDSP Facebook page (opens in a new tab)')"
                >
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path
                            d="M13.5 21v-7h2.4l.4-3h-2.8V9.1c0-.9.3-1.5 1.6-1.5h1.5V5a20 20 0 0 0-2.1-.1c-2.1 0-3.5 1.3-3.5 3.6V11H8.6v3H11v7z"
                        />
                    </svg>
                    Facebook
                </SmartLink>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Mail, Phone } from 'lucide-vue-next';
import type { TeamMember } from '../../types';
import { mediaThumbnailUrl } from '../../lib/public-content';
import MediaPlaceholder from './MediaPlaceholder.vue';
import { useI18n } from '../../lib/i18n';

const props = defineProps<{
    member: TeamMember;
}>();
const { tr } = useI18n();

const fullName = computed(() => `${props.member.first_name} ${props.member.last_name}`.trim());
const image = computed(() => mediaThumbnailUrl(props.member.photo) || props.member.photo_url);
</script>

<template>
    <article
        class="surface-card p-5 text-center transition-colors hover:border-slate-300 sm:p-6 dark:hover:border-slate-600"
    >
        <div class="mx-auto mb-4 size-24 overflow-hidden rounded-full ring-2 ring-soft sm:size-28">
            <MediaPlaceholder
                :image-url="image"
                :alt="member.photo?.alt_text || fullName"
                :label="tr(`Portrait de ${fullName}`, `Portrait of ${fullName}`)"
            />
        </div>
        <h3 class="mt-3 text-lg font-semibold text-navy">{{ fullName }}</h3>
        <p class="mt-1 text-sm font-semibold text-edsp-green">{{ member.position }}</p>
        <p v-if="member.biography" class="mt-3 line-clamp-4 text-sm leading-6 text-slate-600">
            {{ member.biography }}
        </p>
        <div v-if="member.email || member.phone" class="mt-4 flex justify-center gap-2">
            <a
                v-if="member.email"
                :href="`mailto:${member.email}`"
                class="grid size-9 place-items-center rounded-full bg-soft text-navy transition hover:bg-edsp-green hover:text-white"
                :aria-label="tr(`Écrire à ${fullName}`, `Email ${fullName}`)"
            >
                <Mail :size="16" aria-hidden="true" />
            </a>
            <a
                v-if="member.phone"
                :href="`tel:${member.phone.replace(/\s+/g, '')}`"
                class="grid size-9 place-items-center rounded-full bg-soft text-navy transition hover:bg-edsp-green hover:text-white"
                :aria-label="tr(`Appeler ${fullName}`, `Call ${fullName}`)"
            >
                <Phone :size="16" aria-hidden="true" />
            </a>
        </div>
    </article>
</template>

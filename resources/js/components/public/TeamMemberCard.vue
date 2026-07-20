<script setup lang="ts">
import { computed } from 'vue';
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
        class="rounded-xl bg-white p-7 text-center shadow-[0_3px_14px_rgba(11,31,85,0.07)] transition hover:-translate-y-1 hover:shadow-[0_16px_34px_rgba(11,31,85,0.12)]"
    >
        <div class="mx-auto mb-5 size-28 overflow-hidden rounded-full ring-4 ring-soft">
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
    </article>
</template>

<script setup lang="ts">
import { Quote } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Testimonial } from '../../types';
import { mediaThumbnailUrl } from '../../lib/public-content';
import MediaPlaceholder from './MediaPlaceholder.vue';
import { useI18n } from '../../lib/i18n';

const props = defineProps<{
    testimonial: Testimonial;
}>();
const { tr } = useI18n();

const image = computed(() => mediaThumbnailUrl(props.testimonial.photo) || props.testimonial.photo_url);
</script>

<template>
    <figure class="flex h-full flex-col rounded-xl bg-soft p-7 sm:p-8">
        <Quote class="text-gold" :size="30" fill="currentColor" aria-hidden="true" />
        <blockquote class="mt-5 flex-1 text-pretty italic leading-7 text-slate-700">
            « {{ testimonial.content }} »
        </blockquote>
        <figcaption class="mt-6 flex items-center gap-4">
            <div class="size-13 overflow-hidden rounded-full bg-white">
                <MediaPlaceholder
                    :image-url="image"
                    :alt="testimonial.photo?.alt_text || testimonial.author_name"
                    :label="tr(`Portrait de ${testimonial.author_name}`, `Portrait of ${testimonial.author_name}`)"
                />
            </div>
            <div>
                <p class="font-heading text-sm font-semibold text-navy">{{ testimonial.author_name }}</p>
                <p v-if="testimonial.author_role" class="mt-0.5 text-xs text-slate-500">
                    {{ testimonial.author_role }}
                </p>
            </div>
        </figcaption>
    </figure>
</template>

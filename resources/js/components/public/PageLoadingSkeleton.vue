<script setup lang="ts">
type LoadingSkeletonVariant = 'home' | 'listing' | 'detail' | 'form' | 'page';

withDefaults(defineProps<{
    variant?: LoadingSkeletonVariant;
}>(), {
    variant: 'page',
});
</script>

<template>
    <section
        class="min-h-[34rem] bg-white"
        data-page-loading-skeleton
        role="status"
        aria-live="polite"
        aria-label="Chargement de la page"
    >
        <span class="sr-only">Chargement du contenu en cours…</span>

        <div v-if="variant === 'home'" aria-hidden="true">
            <div class="border-b border-slate-200 bg-soft px-4 py-12 sm:px-6 sm:py-16 lg:py-18">
                <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:gap-16">
                    <div>
                        <div class="skeleton-line h-10 w-[88%] sm:h-13" />
                        <div class="skeleton-line mt-3 h-10 w-[72%] sm:h-13" />
                        <div class="mt-7 space-y-3">
                            <div class="skeleton-line h-4 w-full" />
                            <div class="skeleton-line h-4 w-[86%]" />
                        </div>
                        <div class="mt-7 flex gap-3">
                            <div class="skeleton-line h-11 w-48 rounded-md" />
                            <div class="skeleton-line h-11 w-36 rounded-md" />
                        </div>
                        <div class="mt-7 flex gap-6">
                            <div class="skeleton-line h-4 w-40" />
                            <div class="skeleton-line h-4 w-28" />
                        </div>
                    </div>

                    <div class="relative mx-auto w-full max-w-2xl pt-4 pl-4 sm:pt-6 sm:pl-6">
                        <div class="absolute top-0 right-4 bottom-4 left-0 rounded-2xl border border-dashed border-slate-300 sm:right-6 sm:bottom-6" />
                        <div class="skeleton-block relative h-[22rem] rounded-2xl sm:h-[27rem]" />
                    </div>
                </div>
            </div>

            <div class="border-b border-slate-100 px-4 py-14 sm:px-6 sm:py-18">
                <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-2 lg:gap-14">
                    <div class="skeleton-block h-72 rounded-2xl" />
                    <div>
                        <div class="skeleton-line h-3 w-24" />
                        <div class="skeleton-line mt-4 h-8 w-2/3" />
                        <div class="mt-6 space-y-3">
                            <div class="skeleton-line h-4 w-full" />
                            <div class="skeleton-line h-4 w-[94%]" />
                            <div class="skeleton-line h-4 w-4/5" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-soft px-4 py-14 sm:px-6">
                <div class="mx-auto max-w-7xl">
                    <div class="skeleton-line mx-auto h-3 w-20" />
                    <div class="skeleton-line mx-auto mt-4 h-8 w-72 max-w-full" />
                    <div class="mt-9 grid gap-5 md:grid-cols-2">
                        <div v-for="index in 2" :key="index" class="rounded-xl border border-slate-200 bg-white p-7">
                            <div class="skeleton-block size-11 rounded-lg" />
                            <div class="skeleton-line mt-6 h-6 w-1/2" />
                            <div class="mt-5 space-y-3">
                                <div class="skeleton-line h-4 w-full" />
                                <div class="skeleton-line h-4 w-4/5" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else-if="variant === 'form'" aria-hidden="true" class="bg-white px-4 py-10 sm:px-6 sm:py-14">
            <div class="mx-auto grid max-w-7xl items-start gap-8 lg:grid-cols-[19rem_minmax(0,1fr)] lg:gap-12">
                <aside class="rounded-2xl bg-navy p-6 sm:p-7">
                    <div class="skeleton-dark size-11 rounded-xl" />
                    <div class="skeleton-dark mt-7 h-3 w-28" />
                    <div class="skeleton-dark mt-4 h-7 w-4/5" />
                    <div class="mt-9 space-y-5">
                        <div v-for="index in 3" :key="index">
                            <div class="skeleton-dark h-3 w-24" />
                            <div class="skeleton-dark mt-2 h-4 w-2/3" />
                        </div>
                    </div>
                </aside>

                <div>
                    <div class="skeleton-line h-3 w-28" />
                    <div class="skeleton-line mt-4 h-9 w-80 max-w-full" />
                    <div class="skeleton-line mt-4 h-4 w-3/4" />
                    <div class="mt-8 grid grid-cols-4 overflow-hidden rounded-xl border border-slate-200">
                        <div v-for="index in 4" :key="index" class="grid h-20 place-items-center border-r border-slate-200 last:border-r-0">
                            <div class="skeleton-block size-8 rounded-full" />
                        </div>
                    </div>
                    <div class="mt-9 rounded-2xl border border-slate-200 p-6 sm:p-8">
                        <div class="skeleton-line h-7 w-64 max-w-full" />
                        <div class="mt-8 grid gap-6 sm:grid-cols-2">
                            <div v-for="index in 8" :key="index">
                                <div class="skeleton-line h-3 w-24" />
                                <div class="skeleton-block mt-2.5 h-12 rounded-md" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else aria-hidden="true">
            <div class="border-b border-slate-200 bg-soft px-4 py-12 sm:px-6 sm:py-16">
                <div class="mx-auto max-w-7xl">
                    <div class="skeleton-line h-4 w-44" />
                    <div class="skeleton-line mt-8 h-3 w-24" />
                    <div class="skeleton-line mt-4 h-10 w-[34rem] max-w-[90%]" />
                    <div class="skeleton-line mt-5 h-4 w-[40rem] max-w-full" />
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-18">
                <template v-if="variant === 'listing'">
                    <div class="flex items-end justify-between gap-6">
                        <div>
                            <div class="skeleton-line h-3 w-20" />
                            <div class="skeleton-line mt-4 h-8 w-64" />
                        </div>
                        <div class="skeleton-line hidden h-4 w-28 sm:block" />
                    </div>
                    <div class="mt-9 grid gap-6 md:grid-cols-2">
                        <article v-for="index in 4" :key="index" class="rounded-xl border border-slate-200 p-7">
                            <div class="flex justify-between gap-5">
                                <div class="skeleton-block size-12 rounded-lg" />
                                <div class="skeleton-line h-6 w-20 rounded-full" />
                            </div>
                            <div class="skeleton-line mt-7 h-7 w-3/4" />
                            <div class="mt-5 space-y-3">
                                <div class="skeleton-line h-4 w-full" />
                                <div class="skeleton-line h-4 w-5/6" />
                            </div>
                        </article>
                    </div>
                </template>

                <template v-else-if="variant === 'detail'">
                    <div class="grid items-start gap-10 lg:grid-cols-[minmax(0,1fr)_20rem]">
                        <article>
                            <div class="skeleton-block aspect-[16/8] rounded-2xl" />
                            <div class="mt-9 space-y-4">
                                <div class="skeleton-line h-4 w-full" />
                                <div class="skeleton-line h-4 w-[96%]" />
                                <div class="skeleton-line h-4 w-[88%]" />
                                <div class="skeleton-line h-4 w-[92%]" />
                                <div class="skeleton-line h-4 w-3/4" />
                            </div>
                        </article>
                        <aside class="rounded-xl border border-slate-200 p-6">
                            <div class="skeleton-line h-6 w-2/3" />
                            <div class="mt-6 space-y-4">
                                <div v-for="index in 4" :key="index" class="skeleton-line h-4" :class="index % 2 ? 'w-full' : 'w-4/5'" />
                            </div>
                        </aside>
                    </div>
                </template>

                <template v-else>
                    <div class="mx-auto max-w-5xl space-y-12">
                        <div v-for="section in 3" :key="section" class="grid items-center gap-8 lg:grid-cols-[minmax(0,1fr)_18rem]">
                            <div>
                                <div class="skeleton-line h-7 w-1/2" />
                                <div class="mt-5 space-y-3">
                                    <div class="skeleton-line h-4 w-full" />
                                    <div class="skeleton-line h-4 w-[92%]" />
                                    <div class="skeleton-line h-4 w-3/4" />
                                </div>
                            </div>
                            <div class="skeleton-block h-48 rounded-xl" />
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>

<style scoped>
.skeleton-block,
.skeleton-line,
.skeleton-dark {
    position: relative;
    overflow: hidden;
}

.skeleton-block,
.skeleton-line {
    background: #e8edf3;
}

.skeleton-line {
    border-radius: 0.35rem;
}

.skeleton-dark {
    background: rgb(255 255 255 / 12%);
}

.skeleton-block::after,
.skeleton-line::after,
.skeleton-dark::after {
    position: absolute;
    inset: 0;
    content: '';
    transform: translateX(-100%);
    background: linear-gradient(90deg, transparent, rgb(255 255 255 / 62%), transparent);
    animation: skeleton-shimmer 1.35s ease-in-out infinite;
}

.skeleton-dark::after {
    background: linear-gradient(90deg, transparent, rgb(255 255 255 / 12%), transparent);
}

@keyframes skeleton-shimmer {
    to {
        transform: translateX(100%);
    }
}

@media (prefers-reduced-motion: reduce) {
    .skeleton-block::after,
    .skeleton-line::after,
    .skeleton-dark::after {
        animation: none;
    }
}
</style>

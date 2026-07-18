<script setup lang="ts">
import { Inbox } from 'lucide-vue-next';

defineProps<{
    message: string;
}>();
</script>

<template>
    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600">
        <Inbox class="mx-auto mb-3 text-slate-400" :size="30" aria-hidden="true" />
        <p>{{ message }}</p>
    </div>
</template>

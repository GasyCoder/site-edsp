<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Landmark, LoaderCircle, Pencil, Save, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import type { SiteSettings } from '../../types';
import { useI18n } from '../../lib/i18n';

const props = withDefaults(defineProps<{
    canEdit?: boolean;
    editing?: boolean;
    settings?: SiteSettings;
}>(), {
    canEdit: false,
    editing: false,
    settings: () => ({}),
});

const { tr } = useI18n();
const open = ref(false);
const label = computed(() => props.settings.ministerial_reference_label || tr('Référence ministérielle', 'Ministerial accreditation'));
const reference = computed(() => props.settings.ministerial_reference || 'Arrêté n°8008/2014-MESupRES du 29 janvier 2014');
const institutionName = computed(() => props.settings.institution_name || tr('École de Droit et Science Politique', 'School of Law and Political Science'));
const form = useForm({
    ministerial_reference_label: label.value,
    ministerial_reference: reference.value,
});

watch([label, reference], ([nextLabel, nextReference]) => {
    if (!open.value) {
        form.ministerial_reference_label = nextLabel;
        form.ministerial_reference = nextReference;
    }
});

function showEditor(): void {
    form.clearErrors();
    form.ministerial_reference_label = label.value;
    form.ministerial_reference = reference.value;
    open.value = true;
}

function closeEditor(): void {
    if (!form.processing) open.value = false;
}

function save(): void {
    form.patch('/edition/reference-ministerielle', {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    });
}
</script>

<template>
    <section v-if="reference" class="group relative border-y border-slate-200 bg-soft px-4 py-5 sm:px-6 dark:border-slate-700" :aria-label="label">
        <button
            v-if="editing && canEdit"
            type="button"
            class="absolute right-4 top-4 z-10 grid size-10 place-items-center rounded-full border border-slate-200 bg-white text-navy shadow-[0_8px_22px_rgba(11,31,85,0.14)] transition hover:border-edsp-green hover:text-edsp-green focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-edsp-green focus-visible:ring-offset-2 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:hover:border-emerald-400 dark:hover:text-emerald-300 sm:right-6"
            title="Modifier la référence ministérielle"
            aria-label="Modifier la référence ministérielle"
            @click="showEditor"
        >
            <Pencil :size="17" aria-hidden="true" />
        </button>

        <div class="mx-auto flex max-w-7xl items-center" :class="editing && canEdit ? 'pr-12' : ''">
            <div class="flex min-w-0 items-start gap-4">
                <span class="grid size-11 flex-none place-items-center rounded-xl bg-white text-edsp-green shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:text-emerald-300 dark:ring-slate-700">
                    <Landmark :size="22" aria-hidden="true" />
                </span>
                <div class="min-w-0">
                    <p class="text-[0.7rem] font-bold uppercase tracking-[0.14em] text-edsp-green dark:text-emerald-300">{{ label }}</p>
                    <p class="mt-1 font-heading text-sm font-semibold leading-6 text-navy sm:text-base">{{ reference }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">{{ institutionName }}</p>
                </div>
            </div>
        </div>
    </section>

    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-[100] grid place-items-center bg-navy/60 px-4 py-8 backdrop-blur-sm" @mousedown.self="closeEditor">
            <form class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900 sm:p-7" role="dialog" aria-modal="true" aria-labelledby="reference-editor-title" @submit.prevent="save">
                <div class="flex items-start justify-between gap-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-edsp-green">Information institutionnelle</p>
                        <h2 id="reference-editor-title" class="mt-1 text-xl font-bold text-navy">Modifier la référence ministérielle</h2>
                    </div>
                    <button type="button" class="grid size-9 place-items-center rounded-full text-slate-500 hover:bg-soft" aria-label="Fermer" @click="closeEditor">
                        <X :size="20" aria-hidden="true" />
                    </button>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <label for="ministerial-reference-label" class="text-sm font-semibold text-navy">Libellé public</label>
                        <input id="ministerial-reference-label" v-model="form.ministerial_reference_label" class="form-control mt-1.5" maxlength="180" required>
                        <p v-if="form.errors.ministerial_reference_label" class="mt-1.5 text-sm text-red-700">{{ form.errors.ministerial_reference_label }}</p>
                    </div>
                    <div>
                        <label for="ministerial-reference" class="text-sm font-semibold text-navy">Arrêté ministériel</label>
                        <textarea id="ministerial-reference" v-model="form.ministerial_reference" class="form-control mt-1.5 resize-y" rows="3" maxlength="500" required />
                        <p v-if="form.errors.ministerial_reference" class="mt-1.5 text-sm text-red-700">{{ form.errors.ministerial_reference }}</p>
                    </div>
                </div>

                <div class="mt-7 flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                    <button type="button" class="button-secondary" :disabled="form.processing" @click="closeEditor">Annuler</button>
                    <button type="submit" class="button-primary" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" :size="17" class="animate-spin" aria-hidden="true" />
                        <Save v-else :size="17" aria-hidden="true" />
                        {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
    Clock3,
    LoaderCircle,
    LockKeyhole,
    Mail,
    MapPin,
    Phone,
    Send,
} from 'lucide-vue-next';
import { computed } from 'vue';
import type { SharedPageProps, SiteSettings } from '../../types';
import { setting } from '../../lib/public-content';
import SmartLink from './SmartLink.vue';

const props = defineProps<{
    settings?: SiteSettings;
}>();

const page = usePage();
const shared = computed(() => page.props as SharedPageProps);
const newsletterFeedback = computed(() => shared.value.flash?.newsletter ?? null);
const currentYear = new Date().getFullYear();
const address = computed(() => setting(props.settings, 'address', 'Ambondrona, Mahajanga'));
const email = computed(() => setting(props.settings, 'email', 'edsp.mahajanga@gmail.com'));
const phone = computed(() => setting(props.settings, 'phone', '+261 32 05 579 90'));
const secondPhone = computed(() => props.settings?.phone_secondary || '+261 32 98 091 18');
const logoUrl = computed(() => setting(props.settings, 'logo_url', '/images/logo-edsp.png'));
const libraryUrl = computed(() => props.settings?.library_url || '/bibliotheque');
const footerText = computed(() =>
    setting(
        props.settings,
        'footer_text',
        "L'École de Droit et Science Politique forme les juristes et analystes politiques de demain.",
    ),
);

const newsletterForm = useForm({
    email: '',
    website: '',
});

const newsletterDescribedBy = computed(() => [
    'newsletter-privacy',
    newsletterForm.errors.email ? 'newsletter-email-error' : null,
    newsletterFeedback.value ? 'newsletter-feedback' : null,
].filter(Boolean).join(' '));

const feedbackClasses = computed(() => {
    if (newsletterFeedback.value?.status === 'error') {
        return 'border-red-300/30 bg-red-400/10 text-red-100';
    }

    if (newsletterFeedback.value?.status === 'verified') {
        return 'border-emerald-300/30 bg-emerald-400/10 text-emerald-50';
    }

    return 'border-blue-200/20 bg-white/8 text-blue-50';
});

const submitNewsletter = (): void => {
    newsletterForm.post('/newsletter', {
        preserveScroll: true,
        onSuccess: () => newsletterForm.reset(),
    });
};
</script>

<template>
    <footer id="contact" class="relative overflow-hidden bg-[#071943] px-4 text-[#C9D4EE] sm:px-6">
        <div class="pointer-events-none absolute -left-32 top-8 size-80 rounded-full bg-edsp-green/10 blur-3xl" aria-hidden="true" />
        <div class="pointer-events-none absolute -right-24 top-28 size-72 rounded-full bg-institutional/20 blur-3xl" aria-hidden="true" />

        <div class="relative mx-auto max-w-7xl pt-12 sm:pt-16">
            <section
                id="newsletter"
                aria-labelledby="newsletter-title"
                class="relative isolate overflow-hidden rounded-2xl border border-white/12 bg-gradient-to-br from-[#102966] to-[#0B1F55] px-5 py-7 shadow-[0_20px_60px_rgba(0,0,0,0.18)] sm:px-8 sm:py-9 lg:px-10"
            >
                <div class="absolute inset-y-0 right-0 -z-10 hidden w-2/5 bg-edsp-green/7 lg:block" aria-hidden="true" />
                <div class="grid items-center gap-7 lg:grid-cols-[0.9fr_1.1fr] lg:gap-12">
                    <div>
                        <div class="mb-4 flex size-11 items-center justify-center rounded-xl bg-gold/15 text-gold ring-1 ring-gold/20">
                            <Mail :size="21" aria-hidden="true" />
                        </div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-gold">Restez informé</p>
                        <h2 id="newsletter-title" class="mt-2 text-xl font-bold leading-tight text-white">
                            Les actualités de l’EDSP, directement dans votre boîte mail
                        </h2>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-[#C9D4EE] sm:text-base">
                            Recevez les dates d’admission, les événements et les nouvelles formations. Aucun message inutile.
                        </p>
                    </div>

                    <form action="/newsletter" method="post" novalidate @submit.prevent="submitNewsletter">
                        <label for="newsletter-email" class="mb-2 block font-heading text-sm font-semibold text-white">
                            Votre adresse e-mail
                        </label>
                        <div class="flex flex-col gap-2.5 sm:flex-row">
                            <div class="relative min-w-0 flex-1">
                                <Mail
                                    :size="18"
                                    class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                                    aria-hidden="true"
                                />
                                <input
                                    id="newsletter-email"
                                    v-model="newsletterForm.email"
                                    name="email"
                                    type="email"
                                    inputmode="email"
                                    autocomplete="email"
                                    required
                                    placeholder="vous@exemple.com"
                                    class="h-12 w-full rounded-lg border border-white/15 bg-white pl-11 pr-4 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 hover:border-white/40 focus:border-gold focus:ring-4 focus:ring-gold/15 focus:outline-none"
                                    :aria-invalid="Boolean(newsletterForm.errors.email)"
                                    :aria-describedby="newsletterDescribedBy"
                                />
                            </div>
                            <button
                                type="submit"
                                class="inline-flex h-12 flex-none items-center justify-center gap-2 rounded-lg bg-edsp-green px-5 font-heading text-sm font-bold text-white shadow-[0_10px_24px_rgba(7,139,62,0.25)] transition hover:bg-[#069344] disabled:cursor-wait disabled:opacity-70"
                                :disabled="newsletterForm.processing"
                            >
                                <LoaderCircle v-if="newsletterForm.processing" :size="18" class="animate-spin" aria-hidden="true" />
                                <Send v-else :size="17" aria-hidden="true" />
                                {{ newsletterForm.processing ? 'Envoi…' : 'Je m’inscris' }}
                            </button>
                        </div>

                        <div class="absolute -left-[9999px]" aria-hidden="true">
                            <label for="newsletter-website">Ne pas remplir ce champ</label>
                            <input id="newsletter-website" v-model="newsletterForm.website" name="website" type="text" tabindex="-1" autocomplete="off" />
                        </div>

                        <p v-if="newsletterForm.errors.email" id="newsletter-email-error" class="mt-2 text-sm font-semibold text-red-200" role="alert">
                            {{ newsletterForm.errors.email }}
                        </p>

                        <div
                            v-if="newsletterFeedback"
                            id="newsletter-feedback"
                            class="mt-3 flex items-start gap-2 rounded-lg border px-3.5 py-3 text-sm leading-5"
                            :class="feedbackClasses"
                            role="status"
                            aria-live="polite"
                        >
                            <CheckCircle2 v-if="newsletterFeedback.status === 'verified'" :size="18" class="mt-0.5 flex-none" aria-hidden="true" />
                            <Clock3 v-else :size="18" class="mt-0.5 flex-none" aria-hidden="true" />
                            <span>{{ newsletterFeedback.message }}</span>
                        </div>

                        <p id="newsletter-privacy" class="mt-3 flex items-start gap-2 text-xs leading-5 text-[#9FB0D5]">
                            <LockKeyhole :size="14" class="mt-0.5 flex-none text-gold" aria-hidden="true" />
                            <span>
                                Inscription sécurisée en deux étapes : vous devrez confirmer votre adresse par e-mail.
                                <Link href="/politique-de-confidentialite" class="underline decoration-white/30 underline-offset-2 hover:text-white">
                                    Confidentialité
                                </Link>
                            </span>
                        </p>
                    </form>
                </div>
            </section>

            <div class="grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-12 lg:gap-8 lg:py-14">
                <div class="lg:col-span-4 lg:pr-10">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="grid rounded-xl bg-white p-1.5 shadow-sm">
                            <img :src="logoUrl" alt="Logo EDSP" class="h-14 w-auto" />
                        </span>
                        <span>
                            <span class="block font-heading text-lg font-bold text-white">EDSP</span>
                            <span class="block text-xs text-edsp-green">Mahajanga</span>
                        </span>
                    </div>
                    <p class="max-w-sm text-sm leading-7">{{ footerText }}</p>
                </div>

                <nav aria-label="Liens du site" class="lg:col-span-2">
                    <h2 class="mb-4 font-heading text-sm font-semibold text-white">Le site</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li><Link href="/" class="footer-link">Accueil</Link></li>
                        <li><Link href="/presentation" class="footer-link">Présentation</Link></li>
                        <li><Link href="/formations" class="footer-link">Formations</Link></li>
                        <li><Link href="/admissions" class="footer-link">Admissions</Link></li>
                        <li><Link href="/actualites" class="footer-link">Actualités</Link></li>
                    </ul>
                </nav>

                <nav aria-label="Liens utiles" class="lg:col-span-2">
                    <h2 class="mb-4 font-heading text-sm font-semibold text-white">Liens utiles</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li><Link href="/vie-etudiante" class="footer-link">Vie étudiante</Link></li>
                        <li><SmartLink :href="libraryUrl" class="footer-link">Bibliothèque</SmartLink></li>
                        <li><Link href="/galerie" class="footer-link">Galerie</Link></li>
                        <li><Link href="/contact" class="footer-link">Contact</Link></li>
                        <li><Link href="/mentions-legales" class="footer-link">Mentions légales</Link></li>
                        <li><Link href="/politique-de-confidentialite" class="footer-link">Confidentialité</Link></li>
                    </ul>
                </nav>

                <div class="lg:col-span-4 lg:pl-6">
                    <h2 class="mb-4 font-heading text-sm font-semibold text-white">Nous contacter</h2>
                    <ul class="space-y-4 text-sm">
                        <li class="flex gap-3">
                            <span class="grid size-8 flex-none place-items-center rounded-lg bg-white/7 text-gold">
                                <MapPin :size="16" aria-hidden="true" />
                            </span>
                            <span class="pt-1.5">{{ address }}</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid size-8 flex-none place-items-center rounded-lg bg-white/7 text-gold">
                                <Phone :size="16" aria-hidden="true" />
                            </span>
                            <span class="pt-1">
                                <a :href="`tel:${phone.replace(/\s/g, '')}`" class="footer-link">{{ phone }}</a>
                                <template v-if="secondPhone">
                                    <br />
                                    <a :href="`tel:${secondPhone.replace(/\s/g, '')}`" class="footer-link">{{ secondPhone }}</a>
                                </template>
                            </span>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid size-8 flex-none place-items-center rounded-lg bg-white/7 text-gold">
                                <Mail :size="16" aria-hidden="true" />
                            </span>
                            <a :href="`mailto:${email}`" class="footer-link break-all pt-1.5">{{ email }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-white/10 pt-5 pb-[calc(5rem+env(safe-area-inset-bottom))] text-xs text-[#8295BD] sm:flex-row sm:items-center sm:justify-between min-[1280px]:pb-5">
                <p>© {{ currentYear }} École de Droit et Science Politique.</p>
                <p class="inline-flex items-center gap-1.5">
                    Tous droits réservés
                    <ArrowRight :size="12" aria-hidden="true" />
                    Mahajanga
                </p>
            </div>
        </div>
    </footer>
</template>

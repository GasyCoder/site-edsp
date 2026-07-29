<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {
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
import { useI18n } from '../../lib/i18n';

const props = defineProps<{
    settings?: SiteSettings;
}>();

const { tr } = useI18n();
const page = usePage();
const shared = computed(() => page.props as SharedPageProps);
const newsletterFeedback = computed(() => shared.value.flash?.newsletter ?? null);
const currentYear = new Date().getFullYear();
const address = computed(() => setting(props.settings, 'address', 'Ambondrona, Mahajanga'));
const email = computed(() => setting(props.settings, 'email', 'edsp.mahajanga@gmail.com'));
const phone = computed(() => setting(props.settings, 'phone', '+261 32 05 579 90'));
const secondPhone = computed(() => props.settings?.phone_secondary || '+261 32 98 091 18');
const logoUrl = computed(() => setting(props.settings, 'logo_url', '/images/logo-edsp.png'));
const darkLogoUrl = computed(() => setting(props.settings, 'logo_dark_url', '/images/logo-edsp-transparent.png'));
const libraryUrl = computed(() => props.settings?.library_url || '/bibliotheque');
const footerText = computed(() =>
    setting(
        props.settings,
        'footer_text',
        tr(
            "L'École de Droit et Science Politique forme les juristes et analystes politiques de demain.",
            'The School of Law and Political Science educates tomorrow’s legal professionals and political analysts.',
        ),
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
    <footer id="contact" class="bg-[#071943] px-4 text-[#C9D4EE] sm:px-6">
        <div class="mx-auto max-w-7xl pt-7 sm:pt-9">
            <section
                id="newsletter"
                aria-labelledby="newsletter-title"
                class="rounded-lg border border-white/12 bg-white/[0.035] px-4 py-5 sm:px-6 sm:py-6 lg:px-7"
            >
                <div class="grid items-center gap-5 lg:grid-cols-[0.9fr_1.1fr] lg:gap-8">
                    <div>
                        <div class="mb-2.5 flex items-center gap-2.5">
                            <span class="grid size-8 place-items-center rounded-md bg-white/8 text-edsp-green">
                                <Mail :size="17" aria-hidden="true" />
                            </span>
                            <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-edsp-green">{{ tr('Restez informé', 'Stay informed') }}</p>
                        </div>
                        <h2 id="newsletter-title" class="max-w-xl text-lg font-bold leading-snug text-white sm:text-xl">
                            {{ tr('Les actualités de l’EDSP, directement dans votre boîte mail', 'EDSP news, delivered straight to your inbox') }}
                        </h2>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-[#B8C5E0]">
                            {{ tr('Recevez les dates d’admission, les événements et les nouvelles formations. Aucun message inutile.', 'Receive admission dates, events and new programme announcements. Only useful updates.') }}
                        </p>
                    </div>

                    <form action="/newsletter" method="post" novalidate @submit.prevent="submitNewsletter">
                        <label for="newsletter-email" class="mb-1.5 block font-heading text-xs font-semibold text-white">
                            {{ tr('Votre adresse e-mail', 'Your email address') }}
                        </label>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <div class="relative min-w-0 flex-1">
                                <Mail
                                    :size="16"
                                    class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"
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
                                    :placeholder="tr('vous@exemple.com', 'you@example.com')"
                                    class="h-10.5 w-full rounded-md border border-slate-200 bg-[#f8fafc] pl-10 pr-3.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 hover:border-slate-300 focus:border-edsp-green focus:ring-3 focus:ring-edsp-green/15 focus:outline-none"
                                    :aria-invalid="Boolean(newsletterForm.errors.email)"
                                    :aria-describedby="newsletterDescribedBy"
                                />
                            </div>
                            <button
                                type="submit"
                                class="inline-flex h-10.5 flex-none items-center justify-center gap-2 rounded-md bg-edsp-green px-4 font-heading text-xs font-bold text-white transition hover:bg-[#069344] disabled:cursor-wait disabled:opacity-70 sm:text-sm"
                                :disabled="newsletterForm.processing"
                            >
                                <LoaderCircle v-if="newsletterForm.processing" :size="16" class="animate-spin" aria-hidden="true" />
                                <Send v-else :size="16" aria-hidden="true" />
                                {{ newsletterForm.processing ? tr('Envoi…', 'Sending…') : tr('Je m’inscris', 'Subscribe') }}
                            </button>
                        </div>

                        <div class="absolute -left-[9999px]" aria-hidden="true">
                            <label for="newsletter-website">{{ tr('Ne pas remplir ce champ', 'Leave this field blank') }}</label>
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

                        <p id="newsletter-privacy" class="mt-2 flex items-start gap-2 text-[11px] leading-4.5 text-[#9FB0D5]">
                            <LockKeyhole :size="13" class="mt-0.5 flex-none text-[#9FB0D5]" aria-hidden="true" />
                            <span>
                                {{ tr('Inscription sécurisée en deux étapes : vous devrez confirmer votre adresse par e-mail.', 'Secure double opt-in: you will need to confirm your email address.') }}
                                <Link href="/politique-de-confidentialite" class="underline decoration-white/30 underline-offset-2 hover:text-white">
                                    {{ tr('Confidentialité', 'Privacy') }}
                                </Link>
                            </span>
                        </p>
                    </form>
                </div>
            </section>

            <div class="grid grid-cols-2 gap-x-5 gap-y-7 py-7 lg:grid-cols-12 lg:gap-7 lg:py-8">
                <div class="col-span-2 lg:col-span-4 lg:pr-10">
                    <div class="mb-3 flex items-center gap-3">
                        <span class="grid size-12 place-items-center rounded-lg bg-white p-1 shadow-sm">
                            <img :src="darkLogoUrl || logoUrl" alt="Logo EDSP" class="max-h-full max-w-full object-contain" />
                        </span>
                        <span>
                            <span class="block font-heading text-base font-bold text-white">EDSP</span>
                            <span class="block text-xs text-edsp-green">Mahajanga</span>
                        </span>
                    </div>
                    <p class="max-w-sm text-sm leading-6">{{ footerText }}</p>
                </div>

                <nav :aria-label="tr('Liens du site', 'Website links')" class="lg:col-span-2">
                    <h2 class="mb-3 font-heading text-sm font-semibold text-white">{{ tr('Le site', 'Explore') }}</h2>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><Link href="/" class="footer-link">{{ tr('Accueil', 'Home') }}</Link></li>
                        <li><Link href="/presentation" class="footer-link">{{ tr('Mot du directeur', "Director's message") }}</Link></li>
                        <li><Link href="/formations" class="footer-link">{{ tr('Formations', 'Programmes') }}</Link></li>
                        <li><Link href="/admissions" class="footer-link">{{ tr('Admissions', 'Admissions') }}</Link></li>
                        <li><Link href="/actualites" class="footer-link">{{ tr('Actualités', 'News') }}</Link></li>
                    </ul>
                </nav>

                <nav :aria-label="tr('Liens utiles', 'Useful links')" class="lg:col-span-2">
                    <h2 class="mb-3 font-heading text-sm font-semibold text-white">{{ tr('Liens utiles', 'Useful links') }}</h2>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><Link href="/vie-etudiante" class="footer-link">{{ tr('Vie étudiante', 'Student life') }}</Link></li>
                        <li><SmartLink :href="libraryUrl" class="footer-link">{{ tr('Bibliothèque', 'Library') }}</SmartLink></li>
                        <li><Link href="/documents" class="footer-link">{{ tr('Documents publics', 'Public documents') }}</Link></li>
                        <li><Link href="/galerie" class="footer-link">{{ tr('Galerie', 'Gallery') }}</Link></li>
                        <li><Link href="/contact" class="footer-link">Contact</Link></li>
                        <li><Link href="/faq" class="footer-link">{{ tr('FAQ', 'FAQ') }}</Link></li>
                    </ul>
                </nav>

                <div class="col-span-2 lg:col-span-4 lg:pl-6">
                    <h2 class="mb-3 font-heading text-sm font-semibold text-white">{{ tr('Nous contacter', 'Contact us') }}</h2>
                    <ul class="grid gap-3 text-xs sm:grid-cols-2 sm:text-sm lg:grid-cols-1">
                        <li class="flex gap-3">
                            <span class="grid size-7 flex-none place-items-center rounded-md bg-white/7 text-edsp-green">
                                <MapPin :size="14" aria-hidden="true" />
                            </span>
                            <span class="pt-1">{{ address }}</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid size-7 flex-none place-items-center rounded-md bg-white/7 text-edsp-green">
                                <Phone :size="14" aria-hidden="true" />
                            </span>
                            <span class="pt-0.5">
                                <a :href="`tel:${phone.replace(/\s/g, '')}`" class="footer-link">{{ phone }}</a>
                                <template v-if="secondPhone">
                                    <br />
                                    <a :href="`tel:${secondPhone.replace(/\s/g, '')}`" class="footer-link">{{ secondPhone }}</a>
                                </template>
                            </span>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid size-7 flex-none place-items-center rounded-md bg-white/7 text-edsp-green">
                                <Mail :size="14" aria-hidden="true" />
                            </span>
                            <a :href="`mailto:${email}`" class="footer-link break-all pt-1">{{ email }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col gap-2 border-t border-white/10 pt-4 pb-[calc(4.75rem+env(safe-area-inset-bottom))] text-[11px] text-[#8295BD] sm:flex-row sm:items-center sm:justify-between sm:text-xs min-[1280px]:pb-4">
                <p>
                    © {{ currentYear }} {{ tr('École de Droit et Science Politique', 'School of Law and Political Science') }}
                    |
                    <a
                        href="https://mahajanga-univ.mg/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="transition hover:text-white hover:underline hover:underline-offset-2"
                    >
                        {{ tr('Université de Mahajanga', 'University of Mahajanga') }}
                    </a>.
                    {{ tr('Conçu par', 'Designed by') }}
                    <a
                        href="https://www.linkedin.com/in/florentbezara/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="transition hover:text-white hover:underline hover:underline-offset-2"
                    >
                        Florent B.
                    </a>
                </p>
                <nav
                    :aria-label="tr('Informations légales', 'Legal information')"
                    class="flex flex-wrap items-center gap-x-2.5 gap-y-1"
                >
                    <Link href="/mentions-legales" class="transition hover:text-white">
                        {{ tr('Mentions légales', 'Legal notice') }}
                    </Link>
                    <span class="text-white/25" aria-hidden="true">•</span>
                    <Link href="/politique-de-confidentialite" class="transition hover:text-white">
                        {{ tr('Confidentialité', 'Privacy') }}
                    </Link>
                </nav>
            </div>
        </div>
    </footer>
</template>

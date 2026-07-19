<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex,follow">
        <meta name="theme-color" content="#0B1F55">
        <link rel="icon" type="image/png" href="{{ asset('images/logo-edsp.png') }}">
        <title>{{ app()->isLocale('en') ? 'Page not found' : 'Page introuvable' }} | EDSP</title>
        <script>
            try {
                const saved = localStorage.getItem('edsp-color-mode');
                document.documentElement.classList.toggle('dark', saved === 'dark' || (saved === null && matchMedia('(prefers-color-scheme: dark)').matches));
            } catch (_) {}
        </script>
        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-soft text-slate-700 antialiased">
        <main class="grid min-h-screen place-items-center px-6 py-16">
            <section class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-[0_24px_70px_rgba(11,31,85,0.14)]">
                <div class="h-2 bg-edsp-green" aria-hidden="true"></div>
                <div class="px-7 py-12 text-center sm:px-14 sm:py-16">
                    <img
                        src="{{ asset('images/logo-edsp.png') }}"
                        alt="{{ app()->isLocale('en') ? 'School of Law and Political Science' : 'École de Droit et Science Politique' }}"
                        class="mx-auto h-20 w-auto"
                    >
                    <p class="mt-9 font-heading text-sm font-bold uppercase tracking-[0.2em] text-edsp-green">
                        Erreur 404
                    </p>
                    <h1 class="mt-3 text-3xl font-extrabold text-navy sm:text-4xl">{{ app()->isLocale('en') ? 'Page not found' : 'Page introuvable' }}</h1>
                    <p class="mx-auto mt-5 max-w-xl text-base leading-8 text-slate-600">
                        {{ app()->isLocale('en')
                            ? 'The page you requested does not exist, has moved or is no longer available. Return to the EDSP home page to continue browsing.'
                            : 'La page demandée n’existe pas, a été déplacée ou n’est plus disponible. Retrouvez les informations de l’EDSP depuis la page d’accueil.' }}
                    </p>
                    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <a
                            href="{{ route('home') }}"
                            class="inline-flex items-center justify-center rounded-md bg-edsp-green px-6 py-3 font-heading text-sm font-semibold text-white transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-edsp-green focus:ring-offset-2"
                        >
                            {{ app()->isLocale('en') ? 'Back to home' : 'Retour à l’accueil' }}
                        </a>
                        <a
                            href="{{ route('programs.index') }}"
                            class="inline-flex items-center justify-center rounded-md border border-navy px-6 py-3 font-heading text-sm font-semibold text-navy transition hover:bg-navy hover:text-white focus:outline-none focus:ring-2 focus:ring-navy focus:ring-offset-2"
                        >
                            {{ app()->isLocale('en') ? 'View programmes' : 'Voir les formations' }}
                        </a>
                    </div>
                </div>
                <footer class="bg-navy px-6 py-4 text-center text-xs text-blue-100">
                    {{ app()->isLocale('en') ? 'School of Law and Political Science · University of Mahajanga' : 'École de Droit et Science Politique · Université de Mahajanga' }}
                </footer>
            </section>
        </main>
    </body>
</html>

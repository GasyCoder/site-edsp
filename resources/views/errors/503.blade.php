<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ app()->isLocale('en') ? 'Maintenance in progress' : 'Maintenance en cours' }} | EDSP</title>
    <script>
        try {
            const saved = localStorage.getItem('edsp-color-mode');
            document.documentElement.dataset.theme = saved === 'dark' || (saved === null && matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
        } catch (_) {}
    </script>
    <style>
        :root { color-scheme: light; font-family: ui-sans-serif, system-ui, sans-serif; }
        body { align-items: center; background: #f4f7fb; color: #10235a; display: flex; justify-content: center; margin: 0; min-height: 100vh; padding: 1.5rem; }
        main { background: white; border-top: .35rem solid #159947; box-shadow: 0 1rem 3rem rgba(16, 35, 90, .12); max-width: 42rem; padding: 3rem; text-align: center; }
        p { color: #536078; line-height: 1.7; }
        small { color: #778197; }
        :root[data-theme="dark"] { color-scheme: dark; }
        :root[data-theme="dark"] body { background: #071126; color: #eef3ff; }
        :root[data-theme="dark"] main { background: #101a2d; border: 1px solid #2c3b57; border-top: .35rem solid #20b45a; box-shadow: 0 1rem 3rem rgba(0, 0, 0, .28); }
        :root[data-theme="dark"] p { color: #c9d4ee; }
        :root[data-theme="dark"] small { color: #9eb0d2; }
    </style>
</head>
<body>
    <main>
        <p><small>{{ app()->isLocale('en') ? 'School of Law and Political Science' : 'École de Droit et Science Politique' }}</small></p>
        <h1>{{ app()->isLocale('en') ? 'We will be back shortly' : 'Le site revient bientôt' }}</h1>
        <p>{{ app()->isLocale('en') ? 'Scheduled maintenance is currently in progress. Thank you for your patience.' : 'Une opération de maintenance est en cours. Merci de votre compréhension et à très bientôt.' }}</p>
    </main>
</body>
</html>

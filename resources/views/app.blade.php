<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        @php
            $seo = $page['props']['seo'] ?? [];
            $settings = $page['props']['settings'] ?? [];
            $seoTitle = $seo['title'] ?? $settings['default_meta_title'] ?? config('app.name', 'EDSP');
            $seoDescription = $seo['description'] ?? $settings['default_meta_description'] ?? null;
            $seoCanonical = $seo['canonical'] ?? url()->current();
            $seoRobots = $seo['robots'] ?? 'index,follow';
            $seoOgTitle = $seo['og_title'] ?? $seoTitle;
            $seoOgDescription = $seo['og_description'] ?? $seoDescription;
            $seoOgImage = $seo['og_image'] ?? $settings['default_og_image'] ?? null;
            $seoKeywords = $seo['keywords'] ?? $settings['default_meta_keywords'] ?? null;
            $favicon = $settings['favicon_url'] ?? '/images/logo-edsp.png';
        @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0B1F55">
        <script>
            (() => {
                try {
                    const saved = localStorage.getItem('edsp-color-mode');
                    const dark = saved === 'dark' || (saved === null && matchMedia('(prefers-color-scheme: dark)').matches);
                    document.documentElement.classList.toggle('dark', dark);
                    document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
                    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', dark ? '#071126' : '#0B1F55');
                } catch (_) {}
            })();
        </script>
        <link rel="icon" href="{{ str_starts_with($favicon, 'http') ? $favicon : asset(ltrim($favicon, '/')) }}">

        <title inertia data-inertia="">{{ $seoTitle }}</title>
        @if ($seoDescription)
            <meta data-inertia="description" name="description" content="{{ $seoDescription }}">
        @endif
        @if ($seoKeywords)
            <meta data-inertia="keywords" name="keywords" content="{{ $seoKeywords }}">
        @endif
        <meta data-inertia="robots" name="robots" content="{{ $seoRobots }}">
        <link data-inertia="canonical" rel="canonical" href="{{ $seoCanonical }}">
        <meta data-inertia="og:title" property="og:title" content="{{ $seoOgTitle }}">
        @if ($seoOgDescription)
            <meta data-inertia="og:description" property="og:description" content="{{ $seoOgDescription }}">
        @endif
        <meta data-inertia="og:type" property="og:type" content="{{ ($page['component'] ?? null) === 'News/Show' ? 'article' : 'website' }}">
        <meta data-inertia="og:url" property="og:url" content="{{ $seoCanonical }}">
        @if ($seoOgImage)
            <meta data-inertia="og:image" property="og:image" content="{{ $seoOgImage }}">
        @endif
        <meta data-inertia="twitter:card" name="twitter:card" content="summary_large_image">
        <meta data-inertia="twitter:title" name="twitter:title" content="{{ $seoOgTitle }}">
        @if ($seoOgDescription)
            <meta data-inertia="twitter:description" name="twitter:description" content="{{ $seoOgDescription }}">
        @endif
        @if ($seoOgImage)
            <meta data-inertia="twitter:image" name="twitter:image" content="{{ $seoOgImage }}">
        @endif
        @if (! empty($seo['schema']))
            <script data-inertia="structured-data" type="application/ld+json">{!! json_encode($seo['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>

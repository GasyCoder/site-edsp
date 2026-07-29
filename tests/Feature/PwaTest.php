<?php

use App\Models\Page;

test('the public application shell exposes the progressive web app metadata', function (): void {
    Page::query()->create([
        'title' => 'Accueil',
        'slug' => 'accueil',
        'status' => 'published',
        'template' => 'home',
        'published_at' => now(),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('rel="manifest" href="/manifest.webmanifest"', false)
        ->assertSee('rel="apple-touch-icon" sizes="180x180"', false)
        ->assertSee('name="mobile-web-app-capable" content="yes"', false)
        ->assertSee('name="apple-mobile-web-app-capable" content="yes"', false);
});

test('the web app manifest and its declared icons are valid', function (): void {
    $manifestPath = public_path('manifest.webmanifest');
    $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest)
        ->toHaveKey('name')
        ->toHaveKey('short_name', 'EDSP')
        ->toHaveKey('display', 'standalone')
        ->toHaveKey('start_url', '/')
        ->toHaveKey('icons')
        ->and($manifest['icons'])->toHaveCount(3);

    foreach ($manifest['icons'] as $icon) {
        $path = public_path(ltrim($icon['src'], '/'));

        expect($path)->toBeFile();
        [$width, $height] = getimagesize($path);
        [$expectedWidth, $expectedHeight] = array_map('intval', explode('x', $icon['sizes']));

        expect($width)->toBe($expectedWidth)
            ->and($height)->toBe($expectedHeight);
    }
});

test('the service worker keeps private pages out of caches and provides an offline fallback', function (): void {
    $serviceWorker = (string) file_get_contents(public_path('sw.js'));

    expect($serviceWorker)
        ->toContain("const OFFLINE_URL = '/offline.html'")
        ->toContain("'/admin'")
        ->toContain("'/edition'")
        ->toContain("'/login'")
        ->toContain("request.mode === 'navigate'")
        ->and(public_path('offline.html'))->toBeFile();
});

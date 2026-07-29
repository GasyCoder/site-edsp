<?php

use App\Models\News;
use App\Models\Page;
use App\Models\Program;
use App\Models\Redirect;
use App\Models\Setting;

test('sitemap and robots expose only configured public discovery data', function (): void {
    Page::query()->create([
        'title' => 'Présentation', 'slug' => 'presentation', 'status' => 'published',
        'template' => 'default', 'published_at' => now(),
    ]);
    Program::query()->create([
        'title' => 'Droit privé', 'slug' => 'droit-prive', 'level' => 'Licence',
        'description' => 'Formation', 'status' => 'published', 'published_at' => now(),
    ]);
    News::query()->create([
        'title' => 'Rentrée', 'slug' => 'rentree', 'excerpt' => 'Informations',
        'content' => '<p>Informations</p>', 'status' => 'published', 'published_at' => now(),
    ]);
    Setting::query()->create([
        'key' => 'robots_content',
        'value' => "User-agent: *\nDisallow: /admin\nSitemap: /sitemap.xml",
        'type' => 'text',
        'group' => 'seo',
        'is_public' => true,
    ]);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('content-type', 'application/xml; charset=UTF-8')
        ->assertSee('/presentation', false)
        ->assertSee('/formations/droit-prive', false)
        ->assertSee('/actualites/rentree', false);

    $this->get(route('robots'))
        ->assertOk()
        ->assertSee('Disallow: /admin', false)
        ->assertSee(route('sitemap'), false);
});

test('an active legacy URL redirects without looping', function (): void {
    Redirect::query()->create([
        'source_path' => '/ancienne-page',
        'destination_url' => '/presentation',
        'status_code' => 301,
        'is_active' => true,
    ]);

    $this->get('/ancienne-page')
        ->assertStatus(301)
        ->assertRedirect('/presentation');
});

test('public pages expose server rendered canonical metadata and json ld', function (): void {
    Page::query()->create([
        'title' => 'Missions et valeurs',
        'slug' => 'missions-et-valeurs-seo',
        'status' => 'published',
        'template' => 'default',
        'meta_title' => 'Missions de l’EDSP',
        'meta_description' => 'Les missions académiques et les valeurs de l’EDSP.',
        'canonical_url' => url('/missions-et-valeurs-seo'),
        'robots_index' => true,
        'robots_follow' => true,
        'published_at' => now(),
    ]);

    $this->get('/missions-et-valeurs-seo')
        ->assertOk()
        ->assertSee('<title inertia data-inertia="">Missions de l’EDSP</title>', false)
        ->assertSee('name="description" content="Les missions académiques et les valeurs de l’EDSP."', false)
        ->assertSee('rel="canonical" href="'.url('/missions-et-valeurs-seo').'"', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('WebPage', false);
});

test('long dashes are removed from html and social titles', function (): void {
    Page::query()->create([
        'title' => 'Vie étudiante',
        'slug' => 'titre-sans-tiret-long',
        'status' => 'published',
        'template' => 'default',
        'meta_title' => 'Vie étudiante — EDSP',
        'og_title' => 'Vie étudiante – Université de Mahajanga',
        'published_at' => now(),
    ]);

    $this->get('/titre-sans-tiret-long')
        ->assertOk()
        ->assertSee('<title inertia data-inertia="">Vie étudiante | EDSP</title>', false)
        ->assertSee('property="og:title" content="Vie étudiante | Université de Mahajanga"', false);
});

test('the administrable maintenance switch returns an institutional service page', function (): void {
    Setting::query()->updateOrCreate(
        ['key' => 'maintenance_mode'],
        ['value' => 'true', 'type' => 'boolean', 'group' => 'system', 'is_public' => false],
    );

    $this->get('/')
        ->assertServiceUnavailable()
        ->assertSee('Le site revient bientôt');

    $this->get('/admin/login')->assertOk();
});

test('filament is inaccessible without permission and available to administrators', function (): void {
    $this->actingAs(userWithPermissions([]))
        ->get('/admin')
        ->assertForbidden();

    $administrator = userWithPermissions(['access admin']);
    $this->actingAs($administrator)
        ->get('/admin')
        ->assertOk();
});

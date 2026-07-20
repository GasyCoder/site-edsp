<?php

use App\Models\Gallery;
use App\Models\Media;
use App\Models\News;
use App\Models\Page;
use App\Models\Program;
use Database\Seeders\PagesSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('a visitor sees published home content but not hidden sections', function (): void {
    $page = Page::query()->create([
        'title' => 'Accueil',
        'slug' => 'accueil',
        'status' => 'published',
        'template' => 'home',
        'published_at' => now(),
    ]);
    $page->sections()->create([
        'section_key' => 'hero',
        'section_type' => 'hero',
        'title' => 'Bienvenue à l’EDSP',
        'position' => 1,
        'is_visible' => true,
    ]);
    $page->sections()->create([
        'section_key' => 'draft-section',
        'section_type' => 'content',
        'title' => 'Section masquée',
        'position' => 2,
        'is_visible' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component('Home')
            ->where('canEdit', false)
            ->where('page.slug', 'accueil')
            ->has('page.sections', 1)
            ->where('page.sections.0.section_key', 'hero'));
});

test('an editor receives hidden sections so they can be re-enabled', function (): void {
    $page = Page::query()->create([
        'title' => 'Accueil',
        'slug' => 'accueil',
        'status' => 'published',
        'template' => 'home',
        'published_at' => now(),
    ]);
    foreach ([true, false] as $index => $visible) {
        $page->sections()->create([
            'section_key' => 'section-'.$index,
            'section_type' => 'content',
            'title' => 'Section '.$index,
            'position' => $index,
            'is_visible' => $visible,
        ]);
    }

    $editor = userWithPermissions(['edit pages']);

    $this->actingAs($editor)->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->where('canEdit', true)
            ->has('page.sections', 2));
});

test('only an authenticated administrator receives the public back office access flag', function (): void {
    Page::query()->create([
        'title' => 'Accueil',
        'slug' => 'accueil',
        'status' => 'published',
        'template' => 'home',
        'published_at' => now(),
    ]);
    $administrator = userWithPermissions(['access admin']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->where('auth.user', null)
            ->where('auth.canAccessAdmin', false));

    $this->actingAs($administrator)
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->where('auth.user.id', $administrator->id)
            ->where('auth.canAccessAdmin', true));
});

test('the public locale defaults to french and can be persisted in english', function (): void {
    $page = Page::query()->create([
        'title' => 'Accueil',
        'slug' => 'accueil',
        'status' => 'published',
        'template' => 'home',
        'published_at' => now(),
        'translations' => ['en' => ['title' => 'Home']],
    ]);
    $page->sections()->create([
        'section_key' => 'hero',
        'section_type' => 'hero',
        'title' => 'Comprendre le droit',
        'settings' => ['background' => 'light', 'visual_footer' => 'Droit privé · Science politique'],
        'translations' => ['en' => [
            'title' => 'Understand the law',
            'settings' => ['visual_footer' => 'Private Law · Political Science'],
        ]],
        'position' => 1,
        'is_visible' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->where('locale', 'fr')
            ->where('page.title', 'Accueil'));

    $this->from(route('home'))
        ->post(route('locale.update', 'en'))
        ->assertRedirect(route('home'))
        ->assertCookie('edsp_locale', 'en');

    $this->withCookie('edsp_locale', 'en')
        ->get(route('home'))
        ->assertOk()
        ->assertSee('<html lang="en">', false)
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->where('locale', 'en')
            ->where('page.title', 'Home')
            ->where('page.sections.0.title', 'Understand the law')
            ->where('page.sections.0.settings.background', 'light')
            ->where('page.sections.0.settings.visual_footer', 'Private Law · Political Science'));
});

test('inline public editing remains on the french source content', function (): void {
    Page::query()->create([
        'title' => 'Accueil',
        'slug' => 'accueil',
        'status' => 'published',
        'template' => 'home',
        'published_at' => now(),
        'translations' => ['en' => ['title' => 'Home']],
    ]);
    $editor = userWithPermissions(['edit pages']);

    $this->actingAs($editor)
        ->withCookie('edsp_locale', 'en')
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->where('locale', 'en')
            ->where('canEdit', false)
            ->where('auth.canEdit', false));
});

test('the application shell initializes the saved colour mode before assets load', function (): void {
    Page::query()->create([
        'title' => 'Accueil',
        'slug' => 'accueil',
        'status' => 'published',
        'template' => 'home',
        'published_at' => now(),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee("localStorage.getItem('edsp-color-mode')", false)
        ->assertSee("document.documentElement.classList.toggle('dark'", false);
});

test('every home block including statistics is an editable page section', function (): void {
    $this->seed(PagesSeeder::class);
    $editor = userWithPermissions(['edit pages']);

    $this->actingAs($editor)->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component('Home')
            ->where('canEdit', true)
            ->has('page.sections', 12)
            ->where('page.sections.3.section_key', 'stats')
            ->where('page.sections.3.section_type', 'stats'));
});

test('the public gallery page receives published albums and visible images', function (): void {
    Page::query()->create([
        'title' => 'Galerie',
        'slug' => 'galerie',
        'status' => 'published',
        'template' => 'default',
        'published_at' => now(),
    ]);
    $gallery = Gallery::query()->create([
        'title' => 'Vie étudiante',
        'slug' => 'vie-etudiante-test',
        'description' => 'Les temps forts de la vie étudiante.',
        'status' => 'published',
        'is_visible' => true,
        'published_at' => now(),
    ]);
    $media = collect(['visible', 'masquee'])->map(fn (string $name): Media => Media::query()->create([
        'disk' => 'public',
        'path' => "media/galerie-{$name}.jpg",
        'filename' => "galerie-{$name}.jpg",
        'original_name' => "galerie-{$name}.jpg",
        'mime_type' => 'image/jpeg',
        'extension' => 'jpg',
        'size' => 10,
        'alt_text' => "Photo {$name}",
    ]));
    $gallery->images()->create([
        'media_id' => $media[0]->id,
        'title' => 'Conférence des étudiants',
        'position' => 1,
        'is_visible' => true,
    ]);
    $gallery->images()->create([
        'media_id' => $media[1]->id,
        'title' => 'Photo masquée',
        'position' => 2,
        'is_visible' => false,
    ]);

    $this->get('/galerie')
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component('Page')
            ->where('page.slug', 'galerie')
            ->has('galleries', 1)
            ->where('galleries.0.title', 'Vie étudiante')
            ->has('galleries.0.images', 1)
            ->where('galleries.0.images.0.title', 'Conférence des étudiants'));
});

test('draft and future content is not publicly accessible', function (): void {
    Page::query()->create([
        'title' => 'Historique',
        'slug' => 'historique',
        'status' => 'draft',
        'template' => 'default',
    ]);
    $program = Program::query()->create([
        'title' => 'Programme brouillon',
        'slug' => 'programme-brouillon',
        'level' => 'Licence',
        'description' => 'Non publié',
        'status' => 'draft',
    ]);
    $news = News::query()->create([
        'title' => 'Actualité programmée',
        'slug' => 'actualite-programmee',
        'excerpt' => 'À venir',
        'content' => '<p>À venir</p>',
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $this->get('/historique')->assertNotFound();
    $this->get(route('programs.show', $program))->assertNotFound();
    $this->get(route('news.show', $news))->assertNotFound();
});

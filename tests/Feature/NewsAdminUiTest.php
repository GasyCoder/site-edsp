<?php

use App\Filament\Resources\News\Pages\CreateNews;
use App\Filament\Resources\News\Pages\EditNews;
use App\Filament\Resources\News\Pages\ManageNews;
use App\Models\News;
use Filament\Actions\Action;
use Livewire\Livewire;

test('news creation and editing use spacious dedicated pages', function (): void {
    $this->actingAs(userWithPermissions([
        'access admin',
        'view news',
        'create news',
        'edit news',
    ]));

    $this->get('/admin/news')
        ->assertOk()
        ->assertSee('Nouvelle actualité');

    Livewire::test(ManageNews::class)
        ->assertActionExists('create', fn (Action $action): bool => str_ends_with($action->getUrl() ?? '', '/admin/news/create'));

    $this->get('/admin/news/create')
        ->assertOk()
        ->assertSee('Créer une actualité');

    Livewire::test(CreateNews::class)
        ->assertFormFieldExists('title')
        ->assertFormFieldExists('content')
        ->assertFormFieldExists('status')
        ->assertFormFieldExists('meta_title')
        ->assertFormFieldExists('og_image_id');

    $news = News::query()->create([
        'title' => 'Une actualité à modifier',
        'slug' => 'une-actualite-a-modifier',
        'excerpt' => 'Résumé de l’actualité.',
        'content' => '<p>Contenu de l’actualité.</p>',
        'status' => 'draft',
        'author_id' => auth()->id(),
    ]);

    $this->get("/admin/news/{$news->id}/edit")
        ->assertOk()
        ->assertSee('Modifier : Une actualité à modifier');

    Livewire::test(EditNews::class, ['record' => $news->getRouteKey()])
        ->assertFormSet(['title' => 'Une actualité à modifier'])
        ->assertFormFieldExists('content')
        ->assertFormFieldExists('status');
});

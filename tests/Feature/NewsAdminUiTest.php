<?php

use App\Filament\Resources\News\Pages\ManageNews;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Livewire\Livewire;

test('the news creation form is spacious and clearly organized', function (): void {
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
        ->assertActionExists('create', fn (Action $action): bool => $action->getModalWidth() === Width::ScreenExtraLarge)
        ->mountAction('create')
        ->assertActionMounted('create')
        ->assertFormFieldExists('title')
        ->assertFormFieldExists('content')
        ->assertFormFieldExists('status')
        ->assertFormFieldExists('meta_title')
        ->assertFormFieldExists('og_image_id');
});

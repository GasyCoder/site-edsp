<?php

use App\Filament\Resources\Faqs\FaqResource;
use App\Models\Faq;
use Inertia\Testing\AssertableInertia as Assert;

test('the public faq only exposes visible questions in display order', function (): void {
    Faq::query()->create([
        'question' => 'Question publiée en deuxième position',
        'answer' => '<p>Réponse publiée.</p>',
        'category' => 'Inscriptions',
        'position' => 20,
        'is_visible' => true,
    ]);
    Faq::query()->create([
        'question' => 'Question publiée en première position',
        'answer' => '<p>Première réponse.</p>',
        'category' => 'Formations',
        'position' => 10,
        'is_visible' => true,
    ]);
    Faq::query()->create([
        'question' => 'Question masquée',
        'answer' => '<p>Cette réponse ne doit pas être publique.</p>',
        'position' => 0,
        'is_visible' => false,
    ]);

    $this->get(route('faq.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component('Faq/Index')
            ->has('faqs', 2)
            ->where('faqs.0.question', 'Question publiée en première position')
            ->where('faqs.1.question', 'Question publiée en deuxième position')
            ->where('seo.schema.@type', 'FAQPage')
            ->has('seo.schema.mainEntity', 2));
});

test('the faq uses its english translation when english is selected', function (): void {
    Faq::query()->create([
        'question' => 'Comment s’inscrire ?',
        'answer' => '<p>Complétez le formulaire.</p>',
        'category' => 'Inscriptions',
        'position' => 1,
        'is_visible' => true,
        'translations' => [
            'en' => [
                'question' => 'How can I apply?',
                'answer' => '<p>Complete the application form.</p>',
                'category' => 'Applications',
            ],
        ],
    ]);

    $this->withCookie('edsp_locale', 'en')
        ->get(route('faq.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->where('locale', 'en')
            ->where('faqs.0.question', 'How can I apply?')
            ->where('faqs.0.answer', '<p>Complete the application form.</p>')
            ->where('faqs.0.category', 'Applications'));
});

test('an authorized administrator can access faq management', function (): void {
    $administrator = userWithPermissions(['access admin', 'view faqs']);

    $this->actingAs($administrator)
        ->get(FaqResource::getUrl('index'))
        ->assertOk();

    expect(FaqResource::getNavigationLabel())->toBe('FAQ')
        ->and(FaqResource::getPluralModelLabel())->toBe('questions fréquentes');
});

test('faq answers are sanitized before persistence', function (): void {
    $faq = Faq::query()->create([
        'question' => 'Réponse sécurisée',
        'answer' => '<p>Texte autorisé</p><script>alert("xss")</script>',
        'position' => 1,
        'is_visible' => true,
    ]);

    expect($faq->fresh()->answer)
        ->toContain('<p>Texte autorisé</p>')
        ->not->toContain('<script');
});

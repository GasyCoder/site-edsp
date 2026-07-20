<?php

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    Storage::fake('private');
});

test('visitors can find public documents in the resource centre', function (): void {
    Storage::disk('private')->put('documents/brochure.pdf', '%PDF-1.4 public document');

    Document::query()->create([
        'title' => 'Brochure des formations',
        'description' => 'Présentation des parcours proposés.',
        'category' => 'Formations',
        'disk' => 'private',
        'path' => 'documents/brochure.pdf',
        'original_name' => 'brochure-formations.pdf',
        'mime_type' => 'application/pdf',
        'size' => 512,
        'is_public' => true,
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);

    Document::query()->create([
        'title' => 'Document interne',
        'category' => 'Administration',
        'disk' => 'private',
        'path' => 'documents/interne.pdf',
        'original_name' => 'interne.pdf',
        'mime_type' => 'application/pdf',
        'size' => 128,
        'is_public' => false,
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);

    $this->get(route('documents.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Index')
            ->where('documents.total', 1)
            ->where('documents.data.0.title', 'Brochure des formations')
            ->where('documents.data.0.preview_url', route('documents.preview', 1))
            ->where('documents.data.0.download_url', route('documents.download', 1))
            ->has('categories', 1));
});

test('document search and category filters are applied', function (): void {
    foreach ([
        ['Règlement des examens', 'Règlements'],
        ['Brochure des parcours', 'Formations'],
    ] as [$title, $category]) {
        $path = 'documents/'.str($title)->slug().'.pdf';
        Storage::disk('private')->put($path, '%PDF-1.4 public document');
        Document::query()->create([
            'title' => $title,
            'category' => $category,
            'disk' => 'private',
            'path' => $path,
            'original_name' => basename($path),
            'mime_type' => 'application/pdf',
            'size' => 512,
            'is_public' => true,
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);
    }

    $this->get(route('documents.index', ['q' => 'examen', 'category' => 'Règlements']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('documents.total', 1)
            ->where('documents.data.0.title', 'Règlement des examens')
            ->where('filters.q', 'examen')
            ->where('filters.category', 'Règlements'));
});

test('a published PDF can be viewed inline while a private document remains hidden', function (): void {
    Storage::disk('private')->put('documents/public.pdf', '%PDF-1.4 public document');
    Storage::disk('private')->put('documents/private.pdf', '%PDF-1.4 private document');

    $public = Document::query()->create([
        'title' => 'Document public',
        'disk' => 'private',
        'path' => 'documents/public.pdf',
        'original_name' => 'public.pdf',
        'mime_type' => 'application/pdf',
        'size' => 512,
        'is_public' => true,
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);
    $private = Document::query()->create([
        'title' => 'Document privé',
        'disk' => 'private',
        'path' => 'documents/private.pdf',
        'original_name' => 'private.pdf',
        'mime_type' => 'application/pdf',
        'size' => 512,
        'is_public' => false,
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);

    $this->get(route('documents.preview', $public))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $this->get(route('documents.preview', $private))->assertNotFound();
});

test('published brochures are automatically exposed in the programmes navigation', function (): void {
    Storage::disk('private')->put('documents/brochure-2026.pdf', '%PDF-1.4 brochure');

    $brochure = Document::query()->create([
        'title' => 'Brochure 2026',
        'category' => 'Brochures',
        'disk' => 'private',
        'path' => 'documents/brochure-2026.pdf',
        'original_name' => 'brochure-2026.pdf',
        'mime_type' => 'application/pdf',
        'size' => 512,
        'is_public' => true,
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);

    $this->get(route('documents.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('navigationBrochures', 1)
            ->where('navigationBrochures.0.id', $brochure->id)
            ->where('navigationBrochures.0.title', 'Brochure 2026')
            ->where('navigationBrochures.0.url', route('documents.preview', $brochure)));
});

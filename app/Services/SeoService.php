<?php

namespace App\Services;

use App\Models\News;
use App\Models\Page;
use App\Models\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class SeoService
{
    /** @return array<string, mixed> */
    public function for(Model $content, array $settings = []): array
    {
        $title = $content->meta_title ?: $content->title;
        $description = $content->meta_description ?: Str::limit(strip_tags((string) ($content->excerpt ?? $content->description ?? '')), 180, '');

        $seo = [
            'title' => $title ?: ($settings['seo_default_title'] ?? $settings['site_name'] ?? 'EDSP'),
            'description' => $description ?: ($settings['seo_default_description'] ?? null),
            'keywords' => $content->meta_keywords ?? null,
            'canonical' => $content->canonical_url ?: url()->current(),
            'robots' => ($content->robots_index ?? true ? 'index' : 'noindex').','.($content->robots_follow ?? true ? 'follow' : 'nofollow'),
            'og_title' => $content->og_title ?: $title,
            'og_description' => $content->og_description ?: $description,
            'og_image' => $content->ogImage?->image_url ?? $settings['seo_default_og_image'] ?? null,
        ];

        $seo['schema'] = $this->schema($content, $seo, $settings);

        return $seo;
    }

    /** @return array<string, mixed> */
    public function forListing(string $title, string $description, string $type = 'CollectionPage'): array
    {
        $canonical = url()->current();

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => null,
            'canonical' => $canonical,
            'robots' => 'index,follow',
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => null,
            'schema' => [
                '@context' => 'https://schema.org',
                '@type' => $type,
                'name' => $title,
                'description' => $description,
                'url' => $canonical,
            ],
        ];
    }

    /** @param array<string, mixed> $seo @param array<string, mixed> $settings @return array<string, mixed> */
    private function schema(Model $content, array $seo, array $settings): array
    {
        $base = [
            '@context' => 'https://schema.org',
            '@type' => match (true) {
                $content instanceof News => 'NewsArticle',
                $content instanceof Program => 'Course',
                $content instanceof Page && $content->slug === 'accueil' => 'CollegeOrUniversity',
                default => 'WebPage',
            },
            'name' => $seo['title'],
            'description' => $seo['description'],
            'url' => $seo['canonical'],
        ];

        if (filled($seo['og_image'] ?? null)) {
            $base['image'] = $seo['og_image'];
        }

        if ($content instanceof News) {
            $base['headline'] = $seo['og_title'] ?: $seo['title'];
            $base['datePublished'] = $content->published_at?->toAtomString();
            $base['dateModified'] = $content->updated_at?->toAtomString();
            $base['publisher'] = [
                '@type' => 'CollegeOrUniversity',
                'name' => $settings['institution_name'] ?? 'École de Droit et Science Politique',
            ];
        } elseif ($content instanceof Program) {
            $base['provider'] = [
                '@type' => 'CollegeOrUniversity',
                'name' => $settings['institution_name'] ?? 'École de Droit et Science Politique',
                'sameAs' => url('/'),
            ];
        } elseif ($content instanceof Page && $content->slug === 'accueil') {
            $base['name'] = $settings['institution_name'] ?? $base['name'];
            $base['parentOrganization'] = [
                '@type' => 'CollegeOrUniversity',
                'name' => $settings['parent_institution'] ?? 'Université de Mahajanga',
            ];
        }

        return array_filter($base, fn (mixed $value): bool => $value !== null && $value !== '');
    }
}

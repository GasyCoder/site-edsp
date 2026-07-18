<?php

namespace App\Services;

use App\Models\News;
use App\Models\Page;
use App\Models\Program;
use Illuminate\Database\Eloquent\Model;

final class SitemapService
{
    public function xml(): string
    {
        $urls = [
            [route('programs.index'), now()],
            [route('news.index'), now()],
        ];
        $home = Page::published()->where('slug', 'accueil')->where('robots_index', true)->first();

        if ($home !== null) {
            $urls[] = [route('home'), $home->updated_at];
        }
        foreach (Page::published()->where('robots_index', true)->where('slug', '!=', 'accueil')->get() as $page) {
            $urls[] = [route('pages.show', ['slug' => $page->slug]), $page->updated_at];
        }
        $this->append($urls, Program::published()->where('robots_index', true)->get(), 'programs.show');
        $this->append($urls, News::published()->where('robots_index', true)->get(), 'news.show');

        $items = collect($urls)->unique(fn (array $entry) => $entry[0])->map(function (array $entry): string {
            $location = htmlspecialchars($entry[0], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $modified = $entry[1]?->toAtomString();

            return '<url><loc>'.$location.'</loc>'.($modified ? '<lastmod>'.$modified.'</lastmod>' : '').'</url>';
        })->implode('');

        return '<?xml version="1.0" encoding="UTF-8"?>'.'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$items.'</urlset>';
    }

    /** @param array<int, array{0: string, 1: mixed}> $urls @param iterable<int, Model> $models */
    private function append(array &$urls, iterable $models, string $route): void
    {
        foreach ($models as $model) {
            $parameter = $route === 'programs.show' ? 'program' : 'news';
            $urls[] = [route($route, [$parameter => $model->slug]), $model->updated_at];
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AdmissionCampaign;
use App\Models\Document;
use App\Models\Gallery;
use App\Models\Mention;
use App\Models\News;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Program;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Services\SeoService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicSiteController extends Controller
{
    public function home(SettingService $settings, SeoService $seo)
    {
        $canEdit = (auth()->user()?->can('edit pages') ?? false) && app()->isLocale('fr');
        $page = Page::published()->with([
            'ogImage',
            'sections' => fn ($query) => $query->when(! $canEdit, fn ($query) => $query->where('is_visible', true))->with('image')->orderBy('position'),
        ])->where('slug', 'accueil')->firstOrFail();
        $publicSettings = $settings->public();
        $directorMessage = Page::published()
            ->with(['mainSection' => fn ($query) => $query->where('is_visible', true)->with('image')])
            ->where('slug', 'presentation')
            ->first()
            ?->mainSection;

        return Inertia::render('Home', [
            'page' => $page,
            'directorMessage' => $directorMessage,
            'programs' => Program::published()->with($this->programRelations())->orderBy('position')->limit(2)->get(),
            'news' => News::published()->with(['category', 'featuredImage'])->latest('published_at')->limit(3)->get(),
            'campaign' => $this->campaignForFrontend(),
            'teamMembers' => TeamMember::published()->with('photo')->orderBy('display_order')->limit(4)->get(),
            'testimonials' => Testimonial::query()->where('is_visible', true)->with('photo')->latest()->limit(3)->get(),
            'partners' => Partner::query()->where('is_visible', true)->with('logo')->orderBy('position')->limit(6)->get(),
            'settings' => $publicSettings,
            'seo' => $seo->for($page, $publicSettings),
            'canEdit' => $canEdit,
        ]);
    }

    public function page(Request $request, string $slug, SettingService $settings, SeoService $seo)
    {
        $canEdit = (auth()->user()?->can('edit pages') ?? false) && app()->isLocale('fr');
        $page = Page::published()->with([
            'ogImage',
            'sections' => fn ($query) => $query->when(! $canEdit, fn ($query) => $query->where('is_visible', true))->with('image')->orderBy('position'),
        ])->where('slug', $slug)->first();

        if ($page === null) {
            return app(RedirectController::class)($request);
        }
        $props = ['page' => $page, 'seo' => $seo->for($page, $settings->public()), 'canEdit' => $canEdit];

        if ($slug === 'equipe') {
            $props['teamMembers'] = TeamMember::published()->with('photo')->orderBy('display_order')->get();
        } elseif ($slug === 'galerie') {
            $props['galleries'] = Gallery::published()->with(['coverImage', 'images' => fn ($query) => $query->where('is_visible', true)->with('media')->orderBy('position')])->orderBy('position')->get();
        } elseif ($slug === 'partenaires') {
            $props['partners'] = Partner::query()->where('is_visible', true)->with('logo')->orderBy('position')->get();
        } elseif ($slug === 'bibliotheque') {
            $props['documents'] = Document::published()->where('is_public', true)->orderBy('position')->get();
        } elseif ($slug === 'admissions') {
            $props['campaign'] = $this->campaignForFrontend();
        } elseif ($slug === 'vie-etudiante') {
            $props['news'] = News::published()->with(['category', 'featuredImage'])->latest('published_at')->limit(3)->get();
            $props['galleries'] = Gallery::published()->with(['coverImage', 'images' => fn ($query) => $query->where('is_visible', true)->with('media')->orderBy('position')])->orderBy('position')->get();
        }

        return Inertia::render('Page', $props);
    }

    public function programs(SeoService $seo)
    {
        $english = app()->isLocale('en');

        return Inertia::render('Programs/Index', [
            'mentions' => Mention::query()
                ->where('is_active', true)
                ->with([
                    'programs' => fn ($query) => $query->published()->orderBy('position'),
                    'parcours' => fn ($query) => $query->orderBy('id')->with([
                        'levelLinks' => fn ($query) => $query->where('is_active', true)->with('level'),
                    ]),
                ])
                ->orderBy('id')
                ->get(),
            'seo' => $seo->forListing(
                $english ? 'Degree programmes — EDSP' : 'Formations — EDSP',
                $english ? 'Explore degree programmes offered by the School of Law and Political Science.' : 'Découvrez les parcours de formation proposés par l’École de Droit et Science Politique.',
            ),
        ]);
    }

    public function program(Program $program, SettingService $settings, SeoService $seo)
    {
        abort_unless(Program::published()->whereKey($program)->exists(), 404);

        $program->load([
            ...$this->programRelations(),
            'ogImage',
            'documents' => fn ($query) => $query->published()->where('is_public', true)->orderBy('position'),
        ]);

        return Inertia::render('Programs/Show', [
            'program' => $program,
            'seo' => $seo->for($program, $settings->public()),
        ]);
    }

    public function news(SeoService $seo)
    {
        $english = app()->isLocale('en');

        return Inertia::render('News/Index', [
            'news' => News::published()->with(['category', 'featuredImage'])->latest('published_at')->paginate(12),
            'seo' => $seo->forListing(
                $english ? 'News — EDSP' : 'Actualités — EDSP',
                $english ? 'Read news and announcements from the School of Law and Political Science.' : 'Consultez les actualités et communiqués publiés par l’École de Droit et Science Politique.',
            ),
        ]);
    }

    public function documents(Request $request, SeoService $seo)
    {
        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));
        $baseQuery = Document::published()->where('is_public', true);
        $categories = (clone $baseQuery)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        $documents = $baseQuery
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('original_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            }))
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->orderBy('position')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $english = app()->isLocale('en');

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'categories' => $categories,
            'filters' => ['q' => $search, 'category' => $category],
            'seo' => $seo->forListing(
                $english ? 'Public documents — EDSP' : 'Documents publics — EDSP',
                $english
                    ? 'Find and consult official documents published by the School of Law and Political Science.'
                    : 'Recherchez et consultez les documents officiels publiés par l’École de Droit et Science Politique.',
                'CollectionPage',
            ),
        ]);
    }

    public function article(News $news, SettingService $settings, SeoService $seo)
    {
        abort_unless(News::published()->whereKey($news)->exists(), 404);

        $news->increment('views');

        $article = $news->load([
            'category',
            'featuredImage',
            'ogImage',
            'documents' => fn ($query) => $query->published()->where('is_public', true),
            'galleries' => fn ($query) => $query->published()->with([
                'coverImage',
                'images' => fn ($images) => $images->where('is_visible', true)->with('media'),
            ]),
        ]);
        $article->setAttribute('author_name', $news->author()->value('name'));

        return Inertia::render('News/Show', [
            'article' => $article,
            'seo' => $seo->for($article, $settings->public()),
        ]);
    }

    /** @return array<int, string> */
    private function programRelations(): array
    {
        return ['image', 'mentionRecord.parcours.levelLinks.level'];
    }

    private function campaignForFrontend(): ?AdmissionCampaign
    {
        $campaign = AdmissionCampaign::open()->with('programs')->first();

        if ($campaign !== null) {
            $campaign->setAttribute('required_documents', $campaign->normalizedRequiredDocuments());
        }

        return $campaign;
    }
}

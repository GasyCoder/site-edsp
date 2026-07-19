<?php

namespace App\Http\Controllers;

use App\Models\AdmissionCampaign;
use App\Models\Document;
use App\Models\Gallery;
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

        return Inertia::render('Home', [
            'page' => $page,
            'programs' => Program::published()->with(['department', 'image'])->orderBy('position')->limit(2)->get(),
            'news' => News::published()->with(['category', 'featuredImage'])->latest('published_at')->limit(3)->get(),
            'campaign' => $this->campaignForFrontend(),
            'teamMembers' => TeamMember::published()->with(['department', 'photo'])->orderBy('display_order')->limit(4)->get(),
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
            $props['teamMembers'] = TeamMember::published()->with(['department', 'photo'])->orderBy('display_order')->get();
        } elseif ($slug === 'galerie') {
            $props['galleries'] = Gallery::published()->with(['coverImage', 'images' => fn ($query) => $query->where('is_visible', true)->with('media')->orderBy('position')])->orderBy('position')->get();
        } elseif ($slug === 'partenaires') {
            $props['partners'] = Partner::query()->where('is_visible', true)->with('logo')->orderBy('position')->get();
        } elseif ($slug === 'bibliotheque') {
            $props['documents'] = Document::published()->where('is_public', true)->orderBy('position')->get();
        } elseif ($slug === 'admissions') {
            $props['campaign'] = $this->campaignForFrontend();
        }

        return Inertia::render('Page', $props);
    }

    public function programs(SeoService $seo)
    {
        $english = app()->isLocale('en');

        return Inertia::render('Programs/Index', [
            'programs' => Program::published()->with(['department', 'image'])->orderBy('position')->paginate(12),
            'seo' => $seo->forListing(
                $english ? 'Degree programmes — EDSP' : 'Formations — EDSP',
                $english ? 'Explore degree programmes offered by the School of Law and Political Science.' : 'Découvrez les parcours de formation proposés par l’École de Droit et Science Politique.',
            ),
        ]);
    }

    public function program(Program $program, SettingService $settings, SeoService $seo)
    {
        abort_unless(Program::published()->whereKey($program)->exists(), 404);

        $program->load(['department', 'image', 'ogImage', 'documents' => fn ($query) => $query->published()->where('is_public', true)->orderBy('position')]);

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

    private function campaignForFrontend(): ?AdmissionCampaign
    {
        $campaign = AdmissionCampaign::open()->with('programs')->first();

        if ($campaign !== null) {
            $campaign->setAttribute('required_documents', $campaign->normalizedRequiredDocuments());
        }

        return $campaign;
    }
}

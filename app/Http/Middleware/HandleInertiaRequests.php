<?php

namespace App\Http\Middleware;

use App\Models\Document;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();
        $canEditSettings = $user !== null && (
            $user->can('edit settings')
            || $user->hasAnyRole(['superadmin', 'manager'])
        );

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user?->only('id', 'name', 'email'),
                'canEdit' => ($user?->can('edit pages') ?? false) && app()->isLocale('fr'),
                'canEditSettings' => $canEditSettings && app()->isLocale('fr'),
                'canAccessAdmin' => $user?->can('access admin') ?? false,
            ],
            'settings' => fn () => app(SettingService::class)->public(),
            'navigationBrochures' => fn () => Document::published()
                ->where('is_public', true)
                ->whereRaw('LOWER(category) IN (?, ?)', ['brochure', 'brochures'])
                ->orderBy('position')
                ->latest('published_at')
                ->limit(6)
                ->get()
                ->map(fn (Document $document): array => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'url' => $document->preview_url ?: $document->download_url,
                ])
                ->filter(fn (array $document): bool => filled($document['url']))
                ->values(),
            'locale' => app()->getLocale(),
            'locales' => [
                ['code' => 'fr', 'label' => 'Français', 'shortLabel' => 'FR'],
                ['code' => 'en', 'label' => 'English', 'shortLabel' => 'EN'],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'newsletter' => fn () => $request->session()->get('newsletter'),
            ],
        ];
    }
}

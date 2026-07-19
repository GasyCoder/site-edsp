<?php

namespace App\Http\Middleware;

use App\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user?->only('id', 'name', 'email'),
                'canEdit' => ($user?->can('edit pages') ?? false) && app()->isLocale('fr'),
                'canAccessAdmin' => $user?->can('access admin') ?? false,
            ],
            'settings' => fn () => app(SettingService::class)->public(),
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['fr', 'en'], true), 404);

        return back()->withCookie(cookie(
            name: 'edsp_locale',
            value: $locale,
            minutes: 60 * 24 * 365,
            secure: $request->isSecure(),
            sameSite: 'lax',
        ));
    }
}

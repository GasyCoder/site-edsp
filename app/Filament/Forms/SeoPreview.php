<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

final class SeoPreview
{
    /** @return array<Placeholder> */
    public static function components(string $routePrefix = ''): array
    {
        return [
            Placeholder::make('google_preview')
                ->label('Aperçu Google')
                ->content(fn (Get $get): HtmlString => new HtmlString(sprintf(
                    '<div style="max-width:42rem;border:1px solid #dfe3ea;border-radius:.6rem;padding:1rem;background:#fff"><div style="color:#1769aa;font-size:1.15rem">%s</div><div style="color:#137333;font-size:.82rem;margin:.2rem 0">%s</div><div style="color:#4b5563;font-size:.88rem;line-height:1.45">%s</div></div>',
                    e($get->string('meta_title') ?: $get->string('title') ?: 'Titre de la page'),
                    e(self::canonical($get, $routePrefix)),
                    e($get->string('meta_description') ?: 'Ajoutez une description SEO concise pour présenter ce contenu.'),
                )))
                ->columnSpanFull(),
            Placeholder::make('social_preview')
                ->label('Aperçu du partage social')
                ->content(fn (Get $get): HtmlString => new HtmlString(sprintf(
                    '<div style="max-width:32rem;border:1px solid #dfe3ea;border-radius:.6rem;overflow:hidden;background:#f5f7fa"><div style="height:6rem;background:#0b1f55;display:grid;place-items:center;color:#fff;font-weight:700">EDSP · Image Open Graph</div><div style="padding:1rem"><strong style="color:#0b1f55">%s</strong><p style="color:#4b5563;font-size:.86rem;margin:.35rem 0 0">%s</p></div></div>',
                    e($get->string('og_title') ?: $get->string('meta_title') ?: $get->string('title') ?: 'Titre du partage'),
                    e($get->string('og_description') ?: $get->string('meta_description') ?: 'Description du partage social.'),
                )))
                ->columnSpanFull(),
        ];
    }

    private static function canonical(Get $get, string $routePrefix): string
    {
        if (filled($get->string('canonical_url'))) {
            return $get->string('canonical_url');
        }

        return url('/'.trim($routePrefix, '/').($routePrefix === '' ? '' : '/').$get->string('slug'));
    }
}

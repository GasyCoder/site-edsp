<?php

test('the public site explains its limited cookie usage without deceptive consent choices', function (): void {
    $component = file_get_contents(resource_path('js/components/public/CookieNotice.vue'));
    $storage = file_get_contents(resource_path('js/lib/cookie-notice.ts'));

    expect($component)
        ->toContain('Votre vie privée, simplement')
        ->toContain('Aucun cookie publicitaire ni suivi commercial.')
        ->toContain('Fonctionnement essentiel')
        ->toContain('Préférences locales')
        ->toContain('Aucun outil publicitaire, pixel social ou service de mesure d’audience tiers')
        ->toContain('/politique-de-confidentialite')
        ->not->toContain('Tout accepter')
        ->not->toContain('Accepter tous les cookies')
        ->and($storage)
        ->toContain("export const COOKIE_NOTICE_STORAGE_KEY = 'edsp-cookie-notice-v1'")
        ->toContain('180 * 24 * 60 * 60 * 1000')
        ->toContain('expiresAt > Date.now()');
});

test('cookie information remains accessible and does not overlap the pwa prompt', function (): void {
    $layout = file_get_contents(resource_path('js/layouts/PublicLayout.vue'));
    $footer = file_get_contents(resource_path('js/components/public/PublicFooter.vue'));
    $pwaPrompt = file_get_contents(resource_path('js/components/public/PwaInstallPrompt.vue'));

    expect($layout)
        ->toContain("import CookieNotice from '../components/public/CookieNotice.vue'")
        ->toContain('<CookieNotice />')
        ->and($footer)
        ->toContain('Gérer les cookies')
        ->toContain('edsp:open-cookie-settings')
        ->and($pwaPrompt)
        ->toContain('data-cookie-notice-open')
        ->toContain('COOKIE_NOTICE_CLOSED_EVENT')
        ->toContain('COOKIE_NOTICE_OPEN_EVENT');
});

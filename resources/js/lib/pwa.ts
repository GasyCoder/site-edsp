const LOCAL_HOSTS = new Set(['localhost', '127.0.0.1', '[::1]']);

function canRegisterServiceWorker(): boolean {
    return 'serviceWorker' in navigator
        && (window.location.protocol === 'https:' || LOCAL_HOSTS.has(window.location.hostname));
}

export function registerPwaServiceWorker(): void {
    if (!canRegisterServiceWorker()) return;

    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js', {
                scope: '/',
                updateViaCache: 'none',
            })
            .then((registration) => registration.update())
            .catch((error: unknown) => {
                if (import.meta.env.DEV) {
                    console.warn('Le service worker EDSP n’a pas pu être enregistré.', error);
                }
            });
    }, { once: true });
}

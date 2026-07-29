export const COOKIE_NOTICE_STORAGE_KEY = 'edsp-cookie-notice-v1';
export const COOKIE_NOTICE_VERSION = 1;
export const COOKIE_NOTICE_DURATION = 180 * 24 * 60 * 60 * 1000;
export const OPEN_COOKIE_NOTICE_EVENT = 'edsp:open-cookie-settings';
export const COOKIE_NOTICE_OPEN_EVENT = 'edsp:cookie-notice-open';
export const COOKIE_NOTICE_CLOSED_EVENT = 'edsp:cookie-notice-closed';

interface StoredCookieNotice {
    acknowledgedAt: number;
    expiresAt: number;
    version: number;
}

function isStoredCookieNotice(value: unknown): value is StoredCookieNotice {
    if (!value || typeof value !== 'object') return false;

    const notice = value as Partial<StoredCookieNotice>;

    return notice.version === COOKIE_NOTICE_VERSION
        && typeof notice.acknowledgedAt === 'number'
        && typeof notice.expiresAt === 'number'
        && notice.expiresAt > Date.now();
}

export function hasAcknowledgedCookieNotice(): boolean {
    try {
        const stored = window.localStorage.getItem(COOKIE_NOTICE_STORAGE_KEY);

        return stored !== null && isStoredCookieNotice(JSON.parse(stored));
    } catch {
        return false;
    }
}

export function rememberCookieNotice(): void {
    const acknowledgedAt = Date.now();
    const notice: StoredCookieNotice = {
        acknowledgedAt,
        expiresAt: acknowledgedAt + COOKIE_NOTICE_DURATION,
        version: COOKIE_NOTICE_VERSION,
    };

    try {
        window.localStorage.setItem(COOKIE_NOTICE_STORAGE_KEY, JSON.stringify(notice));
    } catch {
        // La visite continue normalement lorsque le stockage local est bloqué.
    }
}

export function announceCookieNoticeState(open: boolean): void {
    document.documentElement.toggleAttribute('data-cookie-notice-open', open);
    window.dispatchEvent(new CustomEvent(open ? COOKIE_NOTICE_OPEN_EVENT : COOKIE_NOTICE_CLOSED_EVENT));
}

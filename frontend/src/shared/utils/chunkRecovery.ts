const CHUNK_RECOVERY_FLAG = 'chunk_recovery_attempted_v2';
const CHUNK_RECOVERY_TTL_MS = 2 * 60 * 1000;

const now = (): number => Date.now();

const readRecoveryStamp = (): number => {
    try {
        const raw = sessionStorage.getItem(CHUNK_RECOVERY_FLAG);
        const parsed = Number(raw);
        return Number.isFinite(parsed) ? parsed : 0;
    } catch {
        return 0;
    }
};

const writeRecoveryStamp = (): void => {
    try {
        sessionStorage.setItem(CHUNK_RECOVERY_FLAG, String(now()));
    } catch {
        // ignore storage failures
    }
};

export const isChunkLoadError = (error: unknown): boolean => {
    const message = String((error as { message?: string } | null)?.message ?? error ?? '').toLowerCase();
    return (
        message.includes('failed to fetch dynamically imported module') ||
        message.includes('error loading dynamically imported module') ||
        message.includes('disallowed mime type') ||
        message.includes('importing a module script failed') ||
        message.includes('async component timed out')
    );
};

const clearBrowserAssetCaches = async (): Promise<void> => {
    try {
        if ('caches' in window) {
            const keys = await caches.keys();
            await Promise.all(keys.map((key) => caches.delete(key)));
        }
    } catch {
        // ignore
    }
};

export const attemptChunkRecoveryReload = (): boolean => {
    const stamp = readRecoveryStamp();
    if (stamp > 0 && now() - stamp < CHUNK_RECOVERY_TTL_MS) {
        return false;
    }

    writeRecoveryStamp();
    const url = new URL(window.location.href);
    url.searchParams.set('_r', String(now()));

    void clearBrowserAssetCaches().finally(() => {
        window.location.replace(url.toString());
    });
    return true;
};

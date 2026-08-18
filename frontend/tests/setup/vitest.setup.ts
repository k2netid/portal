import { beforeEach, vi } from 'vitest';

beforeEach(() => {
    vi.stubGlobal(
        'fetch',
        vi.fn().mockResolvedValue({
            ok: true,
            status: 200,
            json: async () => ({}),
        })
    );

    vi.stubGlobal(
        'navigator',
        {
            ...globalThis.navigator,
            sendBeacon: vi.fn().mockReturnValue(false),
            userAgent: 'vitest',
        } as Navigator
    );
});

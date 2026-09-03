import { beforeEach, vi } from 'vitest';

const createStorageMock = () => {
    let store: Record<string, string> = {};
    return {
        getItem: vi.fn((key: string) => store[key] ?? null),
        setItem: vi.fn((key: string, value: string) => {
            store[key] = String(value);
        }),
        removeItem: vi.fn((key: string) => {
            delete store[key];
        }),
        clear: vi.fn(() => {
            store = {};
        }),
        key: vi.fn((index: number) => Object.keys(store)[index] ?? null),
        get length() {
            return Object.keys(store).length;
        },
    };
};

// Initialize immediately so top-level module imports have access to localStorage
vi.stubGlobal('localStorage', createStorageMock());
vi.stubGlobal('sessionStorage', createStorageMock());

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

    const storageMock = createStorageMock();
    vi.stubGlobal('localStorage', storageMock);
    vi.stubGlobal('sessionStorage', createStorageMock());
});

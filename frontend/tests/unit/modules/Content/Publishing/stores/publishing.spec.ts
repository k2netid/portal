import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { usePublishingStore } from '@/modules/Content/Publishing/stores/publishing';
import { PublishingService } from '@/modules/Content/Publishing/services/publishingService';

vi.mock('@/modules/Content/Publishing/services/publishingService', () => ({
    PublishingService: {
        settingsGroup: vi.fn(),
        publicContents: vi.fn(),
        publicContent: vi.fn(),
        publicCategories: vi.fn(),
    },
}));

// Mock logger to avoid console spam
vi.mock('@/shared/utils/logger', () => ({
    logger: {
        error: vi.fn(),
        debug: vi.fn(),
    }
}));

describe('CMS Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
        vi.useFakeTimers();

        // Mock matchMedia
        const listeners: any[] = [];
        Object.defineProperty(window, 'matchMedia', {
            writable: true,
            value: vi.fn().mockImplementation(query => ({
                matches: false,
                media: query,
                onchange: null,
                addListener: vi.fn(),
                removeListener: vi.fn(),
                addEventListener: vi.fn((type, cb) => {
                    if (type === 'change') listeners.push(cb);
                }),
                removeEventListener: vi.fn(),
                dispatchEvent: vi.fn(),
            })),
        });
        (window as any)._matchMediaListeners = listeners;

        localStorage.clear();
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('fetches settings group', async () => {
        const store = usePublishingStore();
        vi.mocked(PublishingService.settingsGroup).mockResolvedValueOnce({ data: { max_upload_size: 2048 } } as any);

        const result = await store.fetchSettingsGroup('media');

        expect(PublishingService.settingsGroup).toHaveBeenCalledWith('media');
        expect(store.settings.max_upload_size).toBe(2048);
        expect(result.max_upload_size).toBe(2048);
    });

    it('prevents concurrent duplicate settings fetches', async () => {
        const store = usePublishingStore();
        vi.mocked(PublishingService.settingsGroup).mockImplementation(() => new Promise(resolve => setTimeout(() => resolve({ data: { a: 1 } } as any), 100)));

        const p1 = store.fetchSettingsGroup('test');
        const p2 = store.fetchSettingsGroup('test');

        vi.advanceTimersByTime(101);
        await Promise.all([p1, p2]);
        expect(PublishingService.settingsGroup).toHaveBeenCalledTimes(1);
    });

    it('handles settings fetch error', async () => {
        const store = usePublishingStore();
        vi.mocked(PublishingService.settingsGroup).mockRejectedValueOnce(new Error('API Err'));

        const res = await store.fetchSettingsGroup('err');
        expect(res).toEqual({});
    });

    it('fetches contents', async () => {
        const store = usePublishingStore();
        vi.mocked(PublishingService.publicContents).mockResolvedValueOnce({ data: { data: [{ id: '1' }] } } as any);

        const result = await store.fetchContents({ page: 1 });
        expect(PublishingService.publicContents).toHaveBeenCalledWith({ page: 1 });
        expect(store.contents).toHaveLength(1);
        expect(result.data).toHaveLength(1);
    });

    it('handles contents fetch error', async () => {
        const store = usePublishingStore();
        vi.mocked(PublishingService.publicContents).mockRejectedValueOnce(new Error('fail'));

        await store.fetchContents();
        expect(store.contents).toEqual([]);
    });

    it('fetches single content', async () => {
        const store = usePublishingStore();
        vi.mocked(PublishingService.publicContent).mockResolvedValueOnce({ data: { title: 'Hello' } } as any);

        const res = await store.fetchContent('hello');
        expect(PublishingService.publicContent).toHaveBeenCalledWith('hello');
        expect(store.currentContent).toEqual({ title: 'Hello' });
        expect(res).toEqual({ title: 'Hello' });
    });

    it('handles single content fetch error', async () => {
        const store = usePublishingStore();
        vi.mocked(PublishingService.publicContent).mockRejectedValueOnce(new Error('fail'));

        const res = await store.fetchContent('hello');
        expect(res).toBeNull();
    });

    it('fetches categories', async () => {
        const store = usePublishingStore();
        vi.mocked(PublishingService.publicCategories).mockResolvedValueOnce({ data: { data: [{ id: '1' }] } } as any);

        const res = await store.fetchCategories();
        expect(store.categories).toHaveLength(1);
        expect(res).toHaveLength(1);
    });

    it('handles categories fetch error', async () => {
        const store = usePublishingStore();
        vi.mocked(PublishingService.publicCategories).mockRejectedValueOnce(new Error('err'));

        const res = await store.fetchCategories();
        expect(store.categories).toEqual([]);
        expect(res).toEqual([]);
    });
});

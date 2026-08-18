import { describe, it, expect, vi, beforeEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useContentStore } from '@/modules/Content/Publishing/stores/content';
import { PublishingService } from '@/modules/Content/Publishing/services/publishingService';
import { logger } from '@/shared/utils/logger';

vi.mock('@/modules/Content/Publishing/services/publishingService', () => ({
    PublishingService: {
        publicContent: vi.fn(),
    },
}));
vi.mock('@/shared/utils/logger');

describe('Content Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('initializes with default state', () => {
        const store = useContentStore();
        expect(store.currentContent).toBeNull();
        expect(store.loading).toBe(false);
    });

    it('fetches content successfully', async () => {
        const store = useContentStore();
        const mockContent = { id: "1", title: 'Test Content', slug: 'test-slug' };

        vi.mocked(PublishingService.publicContent).mockResolvedValueOnce({
            data: mockContent,
        } as any);

        await store.fetchContent('test-slug');

        expect(store.loading).toBe(false);
        expect(store.currentContent).toEqual(mockContent);
        expect(PublishingService.publicContent).toHaveBeenCalledWith('test-slug');
    });

    it('handles alternative response structure', async () => {
        const store = useContentStore();
        const mockContent = { id: "1", title: 'Test Content' };

        // Some APIs return data directly without a .data wrapper
        vi.mocked(PublishingService.publicContent).mockResolvedValueOnce({
            data: mockContent,
        } as any);

        await store.fetchContent('test-slug');

        expect(store.currentContent).toEqual(mockContent);
    });

    it('handles fetch error', async () => {
        const store = useContentStore();
        const error = new Error('Network Error');

        vi.mocked(PublishingService.publicContent).mockRejectedValueOnce(error);

        await store.fetchContent('test-slug');

        expect(store.loading).toBe(false);
        expect(store.currentContent).toBeNull();
        expect(logger.error).toHaveBeenCalledWith('Failed to fetch content:', error);
    });

    it('clears content', () => {
        const store = useContentStore();
        store.currentContent = { id: "1" } as any;

        store.clearContent();

        expect(store.currentContent).toBeNull();
    });
});

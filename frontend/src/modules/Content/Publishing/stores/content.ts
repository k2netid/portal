import { logger } from '@/shared/utils/logger';
import { defineStore } from 'pinia';
import type { Content } from '@/modules/Content/Publishing/types/content';
import { PublishingService } from '@/modules/Content/Publishing/services/publishingService';

interface ContentState {
    currentContent: Content | null;
    loading: boolean;
}

export const useContentStore = defineStore('content', {
    state: (): ContentState => ({
        currentContent: null,
        loading: false,
    }),

    actions: {
        async fetchContent(slug: string) {
            this.loading = true;
            try {
                const response = await PublishingService.publicContent(slug);
                this.currentContent = response.data;
            } catch (e) {
                logger.error('Failed to fetch content:', e);
                this.currentContent = null;
            } finally {
                this.loading = false;
            }
        },

        clearContent() {
            this.currentContent = null;
        }
    }
});

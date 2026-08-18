import { defineStore } from 'pinia';
import { logger } from '@/shared/utils/logger';
import { ensureArray, parseResponse } from '@/shared/utils/responseParser';
import { LibraryService } from '@/modules/Content/Library/services/libraryService';
import type { Tag } from '@/modules/Content/Library/types/taxonomy';
export type { Tag };



export interface LibraryState {
    tags: Tag[];
    pagination: any | null;
    statistics: Record<string, number> | null;
    loading: boolean;
}

export const useLibraryStore = defineStore('library', {
    state: (): LibraryState => ({
        tags: [],
        pagination: null,
        statistics: null,
        loading: false,
    }),

    actions: {
        async fetchTags(params: Record<string, any> = {}) {
            this.loading = true;
            try {
                const response = await LibraryService.listTags(params);
                const { data, pagination } = parseResponse(response);
                this.tags = ensureArray(data);
                this.pagination = pagination;
                return this.tags;
            } catch (error) {
                logger.error('[Library Store] Error fetching tags:', error);
                return [];
            } finally {
                this.loading = false;
            }
        },

        async fetchStatistics() {
            try {
                const response = await LibraryService.tagStatistics();
                this.statistics = response.data?.data || response.data;
                return this.statistics;
            } catch (error) {
                logger.error('[Library Store] Error fetching statistics:', error);
                return null;
            }
        },

        async saveTag(tag: Partial<Tag>) {
            this.loading = true;
            try {
                const response = tag.id
                    ? await LibraryService.updateTag(tag.id, tag)
                    : await LibraryService.createTag(tag);
                await this.fetchTags({ type: tag.type });
                return response.data;
            } catch (error) {
                logger.error('[Library Store] Error saving tag:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteTag(id: string, type: string = 'Jejakawan') {
            this.loading = true;
            try {
                await LibraryService.deleteTag(id);
                await this.fetchTags({ type });
            } catch (error) {
                logger.error('[Library Store] Error deleting tag:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        }
    }
});

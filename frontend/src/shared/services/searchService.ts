import apiClient from '@/engine/api/client';

export interface SearchResultItem {
    id?: string;
    type: string;
    title: string;
    description?: string;
    url?: string;
    route?: string;
    group?: string;
}

export interface SearchQueryOptions {
    q?: string;
    limit?: number;
    [key: string]: unknown;
}

export const SearchService = {
    async search(params: string | SearchQueryOptions): Promise<{ data: { results: SearchResultItem[]; suggestions?: Array<{ text: string; type?: string }>; is_loose?: boolean } }> {
        try {
            const query = typeof params === 'string' ? params : (params.q || '');
            const res = await apiClient.get(`/manage/system/search?q=${encodeURIComponent(query)}`);
            return res.data;
        } catch {
            return { data: { results: [] } };
        }
    },

    async suggestions(params: string | SearchQueryOptions): Promise<{ data: Array<{ text: string; type?: string }> }> {
        try {
            const query = typeof params === 'string' ? params : (params.q || '');
            const res = await apiClient.get(`/manage/system/search/suggestions?q=${encodeURIComponent(query)}`);
            return res.data;
        } catch {
            return { data: [] };
        }
    },

    async deleteQuery(id: string): Promise<void> {
        try {
            await apiClient.delete(`/manage/system/search/history/${id}`);
        } catch {
            // ignore
        }
    },

    async clearQueries(): Promise<void> {
        try {
            await apiClient.delete('/manage/system/search/history');
        } catch {
            // ignore
        }
    },

    async deleteHistory(id: string): Promise<void> {
        return this.deleteQuery(id);
    },

    async clearHistory(): Promise<void> {
        return this.clearQueries();
    },
};

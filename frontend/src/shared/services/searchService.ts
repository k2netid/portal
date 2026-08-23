import api from '@/engine/api/client';
import { searchPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export interface SearchResultItem {
    id?: string;
    type: string;
    title: string;
    description?: string;
    url?: string;
    route?: string;
    group?: string;
    searchable_id?: string;
    searchable_type?: string;
}

export interface SearchQueryOptions {
    q?: string;
    limit?: number;
    [key: string]: unknown;
}

export const SearchService = {
    search(params: string | SearchQueryOptions): Promise<AxiosResponse> {
        const queryParams = typeof params === 'string' ? { q: params } : params;
        return api.get(searchPaths.public, { params: queryParams });
    },

    suggestions(params: string | SearchQueryOptions): Promise<AxiosResponse> {
        const queryParams = typeof params === 'string' ? { q: params } : params;
        return api.get(searchPaths.suggestions, { params: queryParams });
    },

    deleteQuery(id: string): Promise<AxiosResponse> {
        return api.delete(searchPaths.deleteQuery(id));
    },

    clearQueries(): Promise<AxiosResponse> {
        return api.post(searchPaths.clearQueries);
    },

    deleteHistory(id: string): Promise<AxiosResponse> {
        return this.deleteQuery(id);
    },

    clearHistory(): Promise<AxiosResponse> {
        return this.clearQueries();
    },
};

export default SearchService;

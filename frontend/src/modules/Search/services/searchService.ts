import api from '@/engine/api/client';
import { searchPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export interface SearchIndexHealthResource {
    key: string;
    label: string;
    source: number;
    indexed: number;
    lag: number;
}

export interface SearchIndexHealthSnapshot {
    in_sync: boolean;
    total_lag: number;
    checked_at: string;
    resources: SearchIndexHealthResource[];
    index_totals: {
        all: number;
        post: number;
        page: number;
        category: number;
        tag: number;
    };
}

export const SearchService = {
    search(params: Record<string, unknown>): Promise<AxiosResponse> {
        return api.get(searchPaths.public, { params });
    },

    indexHealth(): Promise<AxiosResponse> {
        return api.get(searchPaths.indexHealth);
    },

    stats(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(searchPaths.manageStats, { params });
    },

    queries(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(searchPaths.manageQueries, { params });
    },

    deleteQuery(id: string): Promise<AxiosResponse> {
        return api.delete(searchPaths.deleteQuery(id));
    },

    clearQueries(): Promise<AxiosResponse> {
        return api.post(searchPaths.clearQueries);
    },

    reindex(): Promise<AxiosResponse> {
        return api.post(searchPaths.reindex);
    },
};

export default SearchService;

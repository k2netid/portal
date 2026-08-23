import api from '@/engine/api/client';
import { libraryPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export const LibraryService = {
    listTags(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(libraryPaths.tags, { params });
    },

    tagStatistics(): Promise<AxiosResponse> {
        return api.get(libraryPaths.tagStatistics);
    },

    createTag(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(libraryPaths.tags, payload);
    },

    updateTag(id: string, payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.put(libraryPaths.tag(String(id)), payload);
    },

    deleteTag(id: string): Promise<AxiosResponse> {
        return api.delete(libraryPaths.tag(String(id)));
    },

    listCategories(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(libraryPaths.categories, { params });
    },

    listCustomFields(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(libraryPaths.customFields, { params });
    },

    listFieldGroups(): Promise<AxiosResponse> {
        return api.get(libraryPaths.fieldGroups);
    },
};

export default LibraryService;

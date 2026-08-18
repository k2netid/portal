import api from '@/engine/api/client';
import { libraryPaths, publishingPaths, systemPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export const PublishingService = {
    publicContents(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(publishingPaths.publicContents, { params });
    },

    publicContent(slug: string, params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(publishingPaths.publicContent(slug), { params });
    },

    publicCategories(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(libraryPaths.publicCategories, { params });
    },

    settingsGroup(group: string): Promise<AxiosResponse> {
        return api.get(systemPaths.settingsGroup(group));
    },

    manageContents(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(publishingPaths.contents, { params });
    },

    manageContent(id: string): Promise<AxiosResponse> {
        return api.get(publishingPaths.content(id));
    },
};

export default PublishingService;

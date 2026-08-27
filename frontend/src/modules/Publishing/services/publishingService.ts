import api from '@/engine/api/client';
import { libraryPaths, publishingPaths } from '@/engine/api/paths';
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
        return api.get(publishingPaths.settingsGroup(group));
    },

    manageContents(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(publishingPaths.contents, { params });
    },

    manageContent(id: string): Promise<AxiosResponse> {
        return api.get(publishingPaths.content(id));
    },

    publicComments(contentId: string): Promise<AxiosResponse> {
        return api.get(`/publishing/contents/${contentId}/comments`);
    },

    postPublicComment(contentId: string, data: {
        body: string;
        name?: string;
        email?: string;
        parent_id?: string;
        captcha_token?: string;
        captcha_input?: string;
    }): Promise<AxiosResponse> {
        return api.post(`/publishing/contents/${contentId}/comments`, data);
    },
};

export default PublishingService;

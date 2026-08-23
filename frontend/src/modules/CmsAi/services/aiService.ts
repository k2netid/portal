import api from '@/engine/api/client';
import { cmsAiPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export const AiService = {
    draftPublishing(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(cmsAiPaths.draftPublishing, payload);
    },

    suggestTaxonomy(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(cmsAiPaths.suggestTaxonomy, payload);
    },

    taxonomyBatches(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(cmsAiPaths.taxonomyBatches, { params });
    },

    taxonomyBatch(id: string): Promise<AxiosResponse> {
        return api.get(cmsAiPaths.taxonomyBatch(id));
    },

    createTaxonomyBatch(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(cmsAiPaths.taxonomyBatches, payload);
    },

    usageStats(): Promise<AxiosResponse> {
        return api.get(cmsAiPaths.usageStats);
    },
};

export default AiService;

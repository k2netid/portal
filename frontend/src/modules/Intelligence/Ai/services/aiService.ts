import api from '@/engine/api/client';
import { aiPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export const AiService = {
    providers(): Promise<AxiosResponse> {
        return api.get(aiPaths.providers);
    },

    models(provider: string, params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(aiPaths.models(provider), { params });
    },

    generate(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(aiPaths.generate, payload);
    },

    draftPublishing(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(aiPaths.draftPublishing, payload);
    },

    suggestTaxonomy(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(aiPaths.suggestTaxonomy, payload);
    },

    taxonomyBatches(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(aiPaths.taxonomyBatches, { params });
    },

    taxonomyBatch(id: string): Promise<AxiosResponse> {
        return api.get(aiPaths.taxonomyBatch(id));
    },

    createTaxonomyBatch(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(aiPaths.taxonomyBatches, payload);
    },

    usageStats(): Promise<AxiosResponse> {
        return api.get(aiPaths.usageStats);
    },
};

export default AiService;

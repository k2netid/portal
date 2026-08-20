import api from '@/engine/api/client';
import { dynamicRecordPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export interface DynamicRecordRow {
    id: string;
    content_type_id: string;
    data: Record<string, unknown>;
    _relations?: Record<string, unknown>;
    created_at?: string;
    updated_at?: string;
}

export const DynamicRecordService = {
    list(slug: string, params?: Record<string, string | number>): Promise<AxiosResponse> {
        return api.get(dynamicRecordPaths.index(slug), { params });
    },

    get(slug: string, id: string): Promise<AxiosResponse> {
        return api.get(dynamicRecordPaths.record(slug, id));
    },

    create(slug: string, payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(dynamicRecordPaths.index(slug), payload);
    },

    update(slug: string, id: string, payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.put(dynamicRecordPaths.record(slug, id), payload);
    },

    remove(slug: string, id: string): Promise<AxiosResponse> {
        return api.delete(dynamicRecordPaths.record(slug, id));
    },
};

export default DynamicRecordService;

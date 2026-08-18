import api from '@/engine/api/client';
import { cckPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export interface CckFieldDefinition {
    name: string;
    slug: string;
    type: string;
    is_required?: boolean;
    options?: string[];
}

export interface CckContentType {
    id: string;
    name: string;
    slug: string;
    description?: string | null;
    fields: CckFieldDefinition[];
    is_active?: boolean;
    created_at?: string;
    updated_at?: string;
}

export const CckService = {
    listTypes(): Promise<AxiosResponse> {
        return api.get(cckPaths.types);
    },

    getType(id: string): Promise<AxiosResponse> {
        return api.get(cckPaths.type(id));
    },

    getTypeBySlug(slug: string): Promise<AxiosResponse> {
        return api.get(cckPaths.typeBySlug(slug));
    },

    createType(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(cckPaths.types, payload);
    },

    updateType(id: string, payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.put(cckPaths.type(id), payload);
    },

    deleteType(id: string): Promise<AxiosResponse> {
        return api.delete(cckPaths.type(id));
    },

    validationRules(id: string): Promise<AxiosResponse> {
        return api.get(cckPaths.validationRules(id));
    },
};

export default CckService;

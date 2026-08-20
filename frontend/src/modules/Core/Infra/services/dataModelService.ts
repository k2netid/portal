import api from '@/engine/api/client';
import { dataModelPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export interface DataModelFieldDefinition {
    name: string;
    slug: string;
    type: 'text' | 'longtext' | 'richtext' | 'number' | 'boolean' | 'date' | 'image' | 'media' | 'email' | 'url' | 'color' | 'select' | 'relation' | 'json';
    options?: string[];
    is_required?: boolean;
    target_type?: string;
    relation_mode?: 'single' | 'multiple';
    placeholder?: string;
    default_value?: unknown;
}

export interface DataModelSchema {
    id: string;
    name: string;
    slug: string;
    description?: string | null;
    is_active?: boolean;
    fields: DataModelFieldDefinition[];
    created_at?: string;
    updated_at?: string;
}

export const DataModelService = {
    listTypes(): Promise<AxiosResponse> {
        return api.get(dataModelPaths.types);
    },

    getType(id: string): Promise<AxiosResponse> {
        return api.get(dataModelPaths.type(id));
    },

    getTypeBySlug(slug: string): Promise<AxiosResponse> {
        return api.get(dataModelPaths.typeBySlug(slug));
    },

    createType(payload: Partial<DataModelSchema>): Promise<AxiosResponse> {
        return api.post(dataModelPaths.types, payload);
    },

    updateType(id: string, payload: Partial<DataModelSchema>): Promise<AxiosResponse> {
        return api.put(dataModelPaths.type(id), payload);
    },

    deleteType(id: string): Promise<AxiosResponse> {
        return api.delete(dataModelPaths.type(id));
    },

    getValidationRules(id: string): Promise<AxiosResponse> {
        return api.get(dataModelPaths.validationRules(id));
    },

    getOpenApiBySlug(slug: string): Promise<AxiosResponse> {
        return api.get(dataModelPaths.openApiBySlug(slug));
    },

    getOpenApiIndex(): Promise<AxiosResponse> {
        return api.get(dataModelPaths.openApiIndex);
    },
};

export default DataModelService;

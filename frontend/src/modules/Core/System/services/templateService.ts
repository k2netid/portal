
import api from '@/engine/api/client';
import type { AxiosResponse } from 'axios';

export interface TemplateData {
    name: string;
    body_template?: string;
    type?: string;
    [key: string]: unknown;
}

export default {
    getTemplates(params?: Record<string, unknown>): Promise<AxiosResponse> {
        return api.get('/manage/publishing/content-templates', { params });
    },
    saveTemplate(data: TemplateData): Promise<AxiosResponse> {
        return api.post('/manage/publishing/content-templates', data);
    },
    deleteTemplate(id: string | string): Promise<AxiosResponse> {
        return api.delete(`/manage/publishing/content-templates/${id}`);
    },
    getTemplate(id: string | string): Promise<AxiosResponse> {
        return api.get(`/manage/publishing/content-templates/${id}`);
    }
}

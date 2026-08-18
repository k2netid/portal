import api from '@/engine/api/client';
import { formsPaths, formSubmissionPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export const FormsService = {
    list(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(formsPaths.index, { params });
    },

    get(id: string): Promise<AxiosResponse> {
        return api.get(formsPaths.form(id));
    },

    create(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(formsPaths.index, payload);
    },

    update(id: string, payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.put(formsPaths.form(id), payload);
    },

    delete(id: string): Promise<AxiosResponse> {
        return api.delete(formsPaths.form(id));
    },

    duplicate(id: string, payload: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.post(`${formsPaths.form(id)}/duplicate`, payload);
    },

    restore(id: string): Promise<AxiosResponse> {
        return api.post(`${formsPaths.form(id)}/restore`);
    },

    forceDelete(id: string): Promise<AxiosResponse> {
        return api.delete(`${formsPaths.form(id)}/force-delete`);
    },

    bulkAction(payload: { ids: (string | number)[]; action: string }): Promise<AxiosResponse> {
        return api.post(formsPaths.bulkAction, payload);
    },

    addField(formId: string, payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(formsPaths.formFields(formId), payload);
    },

    updateField(formId: string, fieldId: string, payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.put(formsPaths.formField(formId, fieldId), payload);
    },

    deleteField(formId: string, fieldId: string): Promise<AxiosResponse> {
        return api.delete(formsPaths.formField(formId, fieldId));
    },

    reorderFields(formId: string, payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(formsPaths.reorderFields(formId), payload);
    },

    listSubmissions(formId: string, params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(formsPaths.submissions(formId), { params });
    },

    submissionStatistics(formId: string, params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(formsPaths.submissionsStatistics(formId), { params });
    },

    submissionsExportUrl(formId: string, query: string, baseUrl = ''): string {
        const q = query ? `?${query}` : '';
        return `${baseUrl}/api/v1${formsPaths.submissionsExport(formId)}${q}`;
    },

    markSubmissionRead(id: string): Promise<AxiosResponse> {
        return api.put(`${formSubmissionPaths.submission(id)}/read`);
    },

    archiveSubmission(id: string): Promise<AxiosResponse> {
        return api.put(`${formSubmissionPaths.submission(id)}/archive`);
    },

    deleteSubmission(id: string): Promise<AxiosResponse> {
        return api.delete(formSubmissionPaths.submission(id));
    },

    exportSubmissionPdfUrl(id: string, baseUrl = ''): string {
        return `${baseUrl}/api/v1${formSubmissionPaths.exportPdf(id)}`;
    },
};

export default FormsService;

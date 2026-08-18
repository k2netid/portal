import api from '@/engine/api/client';
import { mediaPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export const MediaService = {
    list(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(mediaPaths.index, { params });
    },

    upload(formData: FormData): Promise<AxiosResponse> {
        return api.post(mediaPaths.upload, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    update(id: string, payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.put(mediaPaths.file(id), payload);
    },

    delete(id: string, params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.delete(mediaPaths.file(id), { params });
    },

    restore(id: string): Promise<AxiosResponse> {
        return api.post(mediaPaths.restore(id));
    },

    usage(id: string): Promise<AxiosResponse> {
        return api.get(mediaPaths.usage(id));
    },

    thumbnail(id: string): Promise<AxiosResponse> {
        return api.post(mediaPaths.thumbnail(id));
    },

    resize(id: string, payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(mediaPaths.resize(id), payload);
    },

    edit(id: string, formData: FormData): Promise<AxiosResponse> {
        return api.post(mediaPaths.edit(id), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    statistics(): Promise<AxiosResponse> {
        return api.get(mediaPaths.statistics);
    },

    filters(): Promise<AxiosResponse> {
        return api.get(mediaPaths.filters);
    },

    bulk(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(mediaPaths.bulk, payload);
    },

    emptyTrash(): Promise<AxiosResponse> {
        return api.post(mediaPaths.emptyTrash);
    },

    downloadZip(ids: (string | number)[]): Promise<AxiosResponse> {
        return api.post('/manage/media/download-zip', { ids }, { responseType: 'blob' });
    },

    listFolders(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(mediaPaths.folders, { params });
    },

    deleteFolder(id: string): Promise<AxiosResponse> {
        return api.delete(mediaPaths.folder(id));
    },

    restoreFolder(id: string): Promise<AxiosResponse> {
        return api.post(`${mediaPaths.folder(id)}/restore`);
    },

    forceDeleteFolder(id: string): Promise<AxiosResponse> {
        return api.delete(`${mediaPaths.folder(id)}/force-delete`);
    },
};

export default MediaService;

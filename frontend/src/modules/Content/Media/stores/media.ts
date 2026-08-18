import { defineStore } from 'pinia';
import { logger } from '@/shared/utils/logger';
import { ensureArray } from '@/shared/utils/responseParser';
import { MediaService } from '@/modules/Content/Media/services/mediaService';

export interface MediaFile {
    id: string;
    name: string;
    url: string;
    thumb_url?: string;
    mime_type: string;
    size: number;
    folder_id?: string;
}

export interface MediaFolder {
    id: string;
    name: string;
    parent_id?: string;
}

export interface MediaState {
    files: MediaFile[];
    folders: MediaFolder[];
    currentFolder: string | null;
    loading: boolean;
}

export const useMediaStore = defineStore('media', {
    state: (): MediaState => ({
        files: [],
        folders: [],
        currentFolder: null,
        loading: false,
    }),

    actions: {
        async fetchMedia(folderId: string | null = null) {
            this.loading = true;
            try {
                const response = await MediaService.list({ folder_id: folderId });
                this.files = ensureArray(response.data?.files);
                this.folders = ensureArray(response.data?.folders);
                this.currentFolder = folderId;
            } catch (error) {
                logger.error('[Media Store] Error fetching media:', error);
            } finally {
                this.loading = false;
            }
        },

        async uploadFiles(files: FileList | File[], folderId: string | null = null) {
            this.loading = true;
            const formData = new FormData();
            Array.from(files).forEach(file => formData.append('files[]', file));
            if (folderId) formData.append('folder_id', folderId);

            try {
                const response = await MediaService.upload(formData);
                await this.fetchMedia(folderId);
                return response.data;
            } catch (error) {
                logger.error('[Media Store] Upload failed:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        }
    }
});

import { logger } from '@/shared/utils/logger';
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { MediaService } from '@/modules/Media/services/mediaService';
import { LibraryService } from '@/modules/Library/services/libraryService';
import { parseResponse, ensureArray, parseSingleResponse } from '@/shared/utils/responseParser';
import type { Media, MediaFolder, MediaStats } from '@/modules/Media/types/media';
import type { Tag } from '@/modules/Library/types/taxonomy';
import type { PaginationData } from '@/shared/utils/responseParser';

export function useMediaManager() {
    const { t } = useI18n();
    const toast = useToast();
    const { confirm: confirmDialog } = useConfirm();

    const sidebarCollapsed = ref(false);
    const viewMode = ref('grid');
    const loading = ref(false);
    const mediaList = ref<Media[]>([]);
    const folders = ref<MediaFolder[]>([]);
    const treeFolders = ref<MediaFolder[]>([]);
    const isTrashMode = ref(false);
    const selectedFolder = ref<string | number | null>(null);
    const selectedMedia = ref<(string | number)[]>([]);
    const selectedFolders = ref<(string | number)[]>([]);
    const pagination = ref<PaginationData | null>(null);
    const statistics = ref<MediaStats | null>(null);
    const search = ref('');
    const mimeFilter = ref('all');
    const usageFilter = ref('all');
    const tagFilter = ref('');
    const authorFilter = ref<number | string>('all');
    const minSizeFilter = ref<number | string>('');
    const maxSizeFilter = ref<number | string>('');
    const dateFromFilter = ref('');
    const dateToFilter = ref('');
    const tags = ref<Tag[]>([]);
    const availableFilters = ref<{ tags: Tag[], authors: { id: string, name: string }[] }>({ tags: [], authors: [] });
    const bulkAction = ref('');
    const bulkProcessing = ref(false);
    const bulkProgress = ref(0);
    const expandedFolders = ref(new Set<string | number>());

    // Modal State
    const showUploadModal = ref(false);
    const showViewModal = ref(false);
    const showFolderModal = ref(false);
    const showMoveFolderModal = ref(false);
    const showUpdateAltModal = ref(false);
    const showPropertiesPanel = ref(false); // New state for properties panel
    const editingMedia = ref<Media | null>(null);
    const viewingMedia = ref<Media | null>(null);

    const breadcrumbs = computed(() => {
        if (selectedFolder.value === null) return [];
        const crumbs = [];
        let currentId: string | number | null | undefined = selectedFolder.value;
        while (currentId) {
            const folder: MediaFolder | undefined = folders.value.find((f: MediaFolder) => f.id === currentId);
            if (folder) {
                crumbs.unshift({ id: folder.id, name: folder.name });
                currentId = folder.parent_id;
            } else {
                currentId = null;
            }
        }
        return crumbs;
    });

    const currentFolders = computed(() => {
        if (isTrashMode.value) {
            return folders.value.filter((f: MediaFolder) => f.is_trashed);
        }
        if (selectedFolder.value === null) {
            return treeFolders.value.filter((f: MediaFolder) => !f.is_trashed);
        }
        const current = folders.value.find((f: MediaFolder) => f.id === selectedFolder.value);
        if (!current || current.is_trashed) return [];
        return (current.children || []).filter((f: MediaFolder) => !f.is_trashed);
    });

    const fetchMedia = async () => {
        loading.value = true;
        try {
            const params: Record<string, unknown> = {
                page: pagination.value?.current_page || 1,
                per_page: pagination.value?.per_page || 15,
                view: viewMode.value,
                trashed: isTrashMode.value ? 'only' : undefined,
            };
            if (selectedFolder.value !== null) params.folder_id = selectedFolder.value;
            if (search.value) params.search = search.value;
            if (mimeFilter.value && mimeFilter.value !== 'all') {
                params.mime_type = mimeFilter.value === 'image' ? 'image/' :
                    mimeFilter.value === 'video' ? 'video/' :
                        mimeFilter.value === 'application' ? 'application/' : undefined;
            }
            if (usageFilter.value && usageFilter.value !== 'all') params.usage = usageFilter.value;
            if (tagFilter.value) params.tag = tagFilter.value;
            if (authorFilter.value && authorFilter.value !== 'all') params.author_id = authorFilter.value;
            if (minSizeFilter.value) params.min_size = minSizeFilter.value;
            if (maxSizeFilter.value) params.max_size = maxSizeFilter.value;
            if (dateFromFilter.value) params.date_from = dateFromFilter.value;
            if (dateToFilter.value) params.date_to = dateToFilter.value;

            const response = await MediaService.list(params);
            const { data, pagination: paginationData } = parseResponse(response);
            mediaList.value = ensureArray(data);
            if (paginationData) pagination.value = paginationData;
        } catch {
            // logger.error('Failed to fetch media:', error);
        } finally {
            loading.value = false;
        }
    };

    const fetchStatistics = async () => {
        try {
            const response = await MediaService.statistics();
            statistics.value = parseSingleResponse<MediaStats>(response);
        } catch {
            // logger.error('Failed to fetch media statistics:', error);
        }
    };

    const fetchFolders = async () => {
        try {
            const response = await MediaService.listFolders({ tree: true, trashed: 'with' });
            const { data } = parseResponse(response);
            treeFolders.value = ensureArray(data);

            const flatten = (items: MediaFolder[]): MediaFolder[] => {
                let result: MediaFolder[] = [];
                items.forEach(item => {
                    result.push(item);
                    if (item.children && item.children.length > 0) {
                        result = result.concat(flatten(item.children));
                    }
                });
                return result;
            };
            folders.value = flatten(treeFolders.value);
        } catch {
            // logger.error('Failed to fetch folders:', error);
        }
    };

    const fetchTags = async () => {
        try {
            const response = await LibraryService.listTags({ usage: 'media' });
            tags.value = response.data || [];
        } catch (error) {
            logger.error('Failed to fetch tags:', error);
        }
    };

    const fetchFilters = async () => {
        try {
            const response = await MediaService.filters();
            const payload = response.data as { success?: boolean; authors?: { id: string | string; name: string }[] };
            if (payload.success === false) return;
            const authors = Array.isArray(payload.authors)
                ? payload.authors
                    .map((author: { id: string | number; name: string }) => ({
                        id: String(author.id),
                        name: author.name
                    }))
                : [];
            availableFilters.value = {
                ...availableFilters.value,
                authors
            };
        } catch (error) {
            logger.error('Failed to fetch filters:', error);
        }
    };

    const toggleFolder = (folderId: string | number) => {
        if (expandedFolders.value.has(folderId)) {
            expandedFolders.value.delete(folderId);
        } else {
            expandedFolders.value.add(folderId);
        }
    };

    const selectFolder = (id: string | number | null) => {
        isTrashMode.value = false;
        selectedFolder.value = id;
    };

    const toggleTrashMode = () => {
        isTrashMode.value = !isTrashMode.value;
        selectedFolder.value = null;
        fetchMedia();
        fetchFolders(); // Refresh folders to ensure sync with trash state
    };

    const restoreMedia = async (media: Media) => {
        try {
            await MediaService.restore(String(media.id));
            await fetchMedia();
            fetchStatistics();
            toast.success.action(t('media.messages.restoreSuccess') || 'Media restored successfully');
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const restoreFolder = async (folder: MediaFolder) => {
        try {
            await MediaService.restoreFolder(String(folder.id));
            await fetchFolders();
            fetchStatistics();
            toast.success.action(t('media.messages.folderRestoreSuccess') || 'Folder restored successfully');
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const emptyTrash = async () => {
        const confirmed = await confirmDialog({
            title: t('media.confirm.emptyTrashTitle'),
            message: t('media.confirm.emptyTrashMessage'),
            variant: 'danger',
            confirmText: t('media.confirm.delete'),
            cancelText: t('media.confirm.cancel')
        });
        if (!confirmed) return;
        try {
            await MediaService.emptyTrash();
            await fetchMedia();
            await fetchFolders();
            fetchStatistics();
            toast.success.action(t('media.messages.emptyTrashSuccess') || 'Trash emptied successfully');
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const deleteMedia = async (media: Media, isPermanentArg?: boolean) => {
        const isPermanent = isPermanentArg ?? isTrashMode.value;
        const result = await confirmDialog({
            title: isPermanent ? t('media.confirm.deletePermanentTitle') : t('media.confirm.deleteTitle'),
            message: isPermanent ? t('media.confirm.deletePermanentMessage') : t('media.confirm.deleteMessage'),
            variant: 'danger',
            confirmText: t('media.confirm.delete'),
            cancelText: t('media.confirm.cancel'),
            checkbox: !isPermanent,
            checkboxLabel: 'Hapus juga file fisik dari penyimpanan',
            checkboxDefault: true
        });
        if (!result) return;
        
        let unlinkPhysical = true;
        if (typeof result === 'object' && result !== null && 'checkboxValue' in result) {
            unlinkPhysical = (result as { checkboxValue: boolean }).checkboxValue;
        }

        try {
            const params: Record<string, unknown> = isPermanent ? { permanent: 1 } : {};
            if (!isPermanent) {
                params.unlink_physical_file = unlinkPhysical ? 1 : 0;
            }
            await MediaService.delete(String(media.id), params);
            toast.success.action(t('media.messages.deleteSuccess') || 'Media deleted successfully');
            await fetchMedia();
            fetchStatistics();
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const deleteFolder = async (folder: MediaFolder, isPermanentArg?: boolean) => {
        const isPermanent = isPermanentArg ?? isTrashMode.value;
        const confirmed = await confirmDialog({
            title: isPermanent ? t('media.confirm.deletePermanentTitle') : t('media.confirm.deleteTitle'),
            message: isPermanent ? t('media.confirm.deletePermanentMessage') : t('media.confirm.deleteMessage'),
            variant: 'danger',
            confirmText: t('media.confirm.delete'),
            cancelText: t('media.confirm.delete')
        });
        if (!confirmed) return;
        try {
            if (isPermanent) {
                await MediaService.forceDeleteFolder(String(folder.id));
            } else {
                await MediaService.deleteFolder(String(folder.id));
            }
            toast.success.action(t('media.messages.folderDeleted') || 'Folder deleted successfully');
            await fetchFolders();
            if (selectedFolder.value === folder.id) selectedFolder.value = folder.parent_id || null;
            fetchStatistics();
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const toggleMediaSelection = (media: Media) => {
        const id = media.id;
        const index = selectedMedia.value.indexOf(id);
        if (index > -1) {
            selectedMedia.value.splice(index, 1);
        } else {
            selectedMedia.value.push(id);
        }
    };

    const toggleFolderSelection = (folder: MediaFolder) => {
        const id = folder.id;
        const index = selectedFolders.value.indexOf(id);
        if (index > -1) {
            selectedFolders.value.splice(index, 1);
        } else {
            selectedFolders.value.push(id);
        }
    };

    const toggleSelectAll = () => {
        const totalItems = mediaList.value.length + currentFolders.value.length;
        const selectedCount = selectedMedia.value.length + selectedFolders.value.length;

        if (selectedCount === totalItems && totalItems > 0) {
            selectedMedia.value = [];
            selectedFolders.value = [];
        } else {
            selectedMedia.value = mediaList.value.map(m => m.id);
            selectedFolders.value = currentFolders.value.map(f => f.id);
        }
    };

    const clearSelection = () => {
        selectedMedia.value = [];
        selectedFolders.value = [];
    };

    const handleBulkAction = async (action: string, options: { folderId?: string | number | null; altText?: string } = {}) => {
        if (!action || (selectedMedia.value.length === 0 && selectedFolders.value.length === 0)) return;

        if (action === 'delete') {
            const result = await confirmDialog({
                title: t('media.confirm.deleteTitle'),
                message: t('media.confirm.deleteMessage'),
                variant: 'danger',
                confirmText: t('media.confirm.delete'),
                cancelText: t('media.confirm.cancel'),
                checkbox: true,
                checkboxLabel: 'Hapus juga file fisik dari penyimpanan',
                checkboxDefault: true
            });
            if (!result) return;
            
            let unlinkPhysical = true;
            if (typeof result === 'object' && result !== null && 'checkboxValue' in result) {
                unlinkPhysical = (result as { checkboxValue: boolean }).checkboxValue;
            }

            bulkProcessing.value = true;
            try {
                await MediaService.bulk({ action: 'delete', media_ids: selectedMedia.value, unlink_physical_file: unlinkPhysical ? 1 : 0 });
                await fetchMedia();
                fetchStatistics();
                selectedMedia.value = [];
                toast.success.action(t('media.messages.bulkDeleted') || 'Items deleted successfully');
            } catch (error: unknown) {
                toast.error.fromResponse(error);
            } finally {
                bulkProcessing.value = false;
            }
        } else if (action === 'restore' || action === 'delete_permanent') {
            if (action === 'delete_permanent') {
                const confirmed = await confirmDialog({
                    title: t('media.confirm.deletePermanentTitle'),
                    message: t('media.confirm.deletePermanentMessage'),
                    variant: 'danger',
                    confirmText: t('media.confirm.delete'),
                    cancelText: t('media.confirm.cancel')
                });
                if (!confirmed) return;
            }
            bulkProcessing.value = true;
            try {
                await MediaService.bulk({
                    action: action === 'restore' ? 'restore' : 'delete_permanent',
                    media_ids: selectedMedia.value,
                    folder_ids: selectedFolders.value
                });

                await fetchMedia();
                await fetchFolders();
                fetchStatistics();
                selectedMedia.value = [];
                selectedFolders.value = [];

                const successMsg = action === 'restore'
                    ? (t('media.messages.bulkRestored') || 'Items restored successfully')
                    : (t('media.messages.bulkDeletedPermanent') || 'Items permanently deleted');

                toast.success.action(successMsg);
            } catch (error: unknown) {
                toast.error.fromResponse(error);
            } finally {
                bulkProcessing.value = false;
            }
        } else if (action === 'move') {
            bulkProcessing.value = true;
            try {
                await MediaService.bulk({
                    action: 'move',
                    media_ids: selectedMedia.value,
                    folder_id: options.folderId,
                });
                await fetchMedia();
                selectedMedia.value = [];
                toast.success.action(t('media.messages.bulkMoved') || 'Items moved successfully');
            } catch (error: unknown) {
                toast.error.fromResponse(error);
            } finally {
                bulkProcessing.value = false;
            }
        } else if (action === 'update_alt') {
            bulkProcessing.value = true;
            try {
                await MediaService.bulk({
                    action: 'update_alt',
                    media_ids: selectedMedia.value,
                    alt_text: options.altText,
                });
                await fetchMedia();
                selectedMedia.value = [];
                toast.success.action(t('media.messages.bulkAltUpdated') || 'Alt text updated successfully');
            } catch (error: unknown) {
                toast.error.fromResponse(error);
            } finally {
                bulkProcessing.value = false;
            }
        } else if (action === 'download') {
            bulkProcessing.value = true;
            try {
                const response = await MediaService.downloadZip(selectedMedia.value);
                const blob = new Blob([response.data], { type: 'application/zip' });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `media-${new Date().toISOString().slice(0, 10)}.zip`);
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
                selectedMedia.value = [];
                toast.success.action(t('media.messages.bulkDownloadSuccess') || 'Media downloaded successfully');
            } catch (error: unknown) {
                toast.error.fromResponse(error);
            } finally {
                bulkProcessing.value = false;
            }
        }
    };

    const startBulkAction = (action: string) => {
        if (action === 'move') {
            showMoveFolderModal.value = true;
        } else if (action === 'update_alt') {
            showUpdateAltModal.value = true;
        } else {
            handleBulkAction(action);
        }
    };

    // Watch for changes and fetch
    watch([
        selectedFolder,
        search,
        mimeFilter,
        usageFilter,
        tagFilter,
        authorFilter,
        minSizeFilter,
        maxSizeFilter,
        dateFromFilter,
        dateToFilter,
        viewMode,
        isTrashMode
    ], () => {
        selectedMedia.value = [];
        selectedFolders.value = []; // Also clear folder selection
        if (pagination.value) pagination.value.current_page = 1;
        fetchMedia();
        if (selectedFolder.value === null) {
            fetchFolders();
            fetchTags();
        }
    });

    // Helper Actions
    const togglePropertiesPanel = (media?: Media) => {
        if (media) {
            editingMedia.value = media; // Reuse editingMedia as the active item
            showPropertiesPanel.value = true;
        } else {
            showPropertiesPanel.value = !showPropertiesPanel.value;
        }
    };

    const viewMedia = (media: Media) => {
        viewingMedia.value = media;
        showViewModal.value = true;
    };

    const editMedia = (media: Media) => {
        togglePropertiesPanel(media);
    };

    const downloadMedia = (media: Media) => {
        if (media.url) {
            const link = document.createElement('a');
            link.href = media.url;
            link.setAttribute('download', media.name);
            document.body.appendChild(link);
            link.click();
            link.remove();
        }
    };

    const copyMediaUrl = (media: Media) => {
        if (media.url) {
            navigator.clipboard.writeText(media.url);
            toast.success.action(t('media.messages.urlCopied') || 'URL copied to clipboard');
        }
    };

    return {
        sidebarCollapsed,
        viewMode,
        loading,
        mediaList,
        folders,
        treeFolders,
        isTrashMode,
        selectedFolder,
        selectedMedia,
        selectedFolders,
        pagination,
        statistics,
        search,
        mimeFilter,
        usageFilter,
        tagFilter,
        authorFilter,
        minSizeFilter,
        maxSizeFilter,
        dateFromFilter,
        dateToFilter,
        tags,
        availableFilters,
        bulkAction,
        bulkProcessing,
        bulkProgress,
        expandedFolders,
        breadcrumbs,
        currentFolders,
        fetchMedia,
        fetchStatistics,
        fetchFolders,
        fetchTags,
        fetchFilters,
        toggleFolder,
        selectFolder,
        toggleTrashMode,
        restoreMedia,
        restoreFolder,
        emptyTrash,
        deleteMedia,
        deleteFolder,
        toggleMediaSelection,
        toggleFolderSelection,
        toggleSelectAll,
        clearSelection,
        handleBulkAction,
        startBulkAction,
        // Modal State
        showUploadModal,
        showViewModal,
        showFolderModal,
        showMoveFolderModal,
        showUpdateAltModal,
        showPropertiesPanel,
        editingMedia,
        viewingMedia,
        // Helper Actions
        togglePropertiesPanel,
        viewMedia,
        editMedia,
        downloadMedia,
        copyMediaUrl,
    };
}

export type MediaManager = ReturnType<typeof useMediaManager>;

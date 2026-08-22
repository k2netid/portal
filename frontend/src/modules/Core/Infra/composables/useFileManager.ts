import { logger } from '@/shared/utils/logger';
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { storeToRefs } from 'pinia';
import api from '@/engine/api/client';
import { infraPaths } from '@/engine/api/paths';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import type { FileItem, FolderItem, TrashItem } from '@/modules/Core/Infra/types/file-manager';

export function useFileManager(options: { rootPath?: string } = {}) {
    const { t } = useI18n();
    const toast = useToast();
    const { confirm: confirmDialog } = useConfirm();

    const rootPath = options.rootPath || '/';

    // State
    const files = ref<FileItem[]>([]);
    const folders = ref<FolderItem[]>([]);
    const allFolders = ref<FolderItem[]>([]);
    const filesCache = ref(new Map<string, FileItem[]>());
    const loading = ref(false);
    const currentPath = ref(rootPath);
    const viewMode = ref<'grid' | 'list'>(localStorage.getItem('fileManagerViewMode') as 'grid' | 'list' || 'grid');
    const sidebarCollapsed = ref(localStorage.getItem('fileManagerSidebarCollapsed') === 'true');
    const propertiesSidebarVisible = ref(localStorage.getItem('fileManagerPropertiesVisible') === 'true');
    const expandedFolders = ref(new Set([rootPath]));
    const scannedPaths = ref(new Set<string>());

    // Trash state
    const showTrashView = ref(false);
    const trashItems = ref<TrashItem[]>([]);
    const trashLoading = ref(false);

    // Filter/Sort state
    const searchQuery = ref('');
    const filterType = ref('all');
    const sortBy = ref('name');
    const sortDirection = ref<'asc' | 'desc'>('asc');

    // Advanced Filters state
    const authorFilter = ref<number | string>('all');
    const minSizeFilter = ref<number | string>('');
    const maxSizeFilter = ref<number | string>('');
    const dateFromFilter = ref('');
    const dateToFilter = ref('');
    const availableFilters = ref<{ authors: { id: string | string, name: string }[] }>({ authors: [] });

    // Selection state
    const selectedItems = ref<(FileItem | FolderItem)[]>([]);

    // Pagination state
    const currentPage = ref(1);
    const itemsPerPage = ref(10);

    // Clipboard state
    const clipboard = ref<{
        items: { path: string, type: 'file' | 'folder' }[],
        action: 'copy' | 'move'
    }>({
        items: [],
        action: 'copy'
    });

    // Drag & Drop state
    const draggedItem = ref<FileItem | FolderItem | null>(null);
    const draggedType = ref<'file' | 'folder' | null>(null);
    const dropTarget = ref<string | null>(null);

    const isMounted = ref(false);

    // Helpers
    const formatFileSize = (bytes: number) => {
        if (!bytes) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    };

    const systemStore = useSystemStore();
    const { settings } = storeToRefs(systemStore);

    const isImage = (file: FileItem) => {
        const imageExtensions = settings.value.allowed_image_types 
            ? String(settings.value.allowed_image_types).split(',').map(s => s.trim().toLowerCase())
            : ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        return imageExtensions.includes(file.extension?.toLowerCase() || '');
    };

    const isVideo = (file: FileItem) => {
        const videoExtensions = settings.value.allowed_video_types
            ? String(settings.value.allowed_video_types).split(',').map(s => s.trim().toLowerCase())
            : ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'];
        return videoExtensions.includes(file.extension?.toLowerCase() || '');
    };

    const isAudio = (file: FileItem) => {
        const audioExtensions = settings.value.allowed_audio_types
            ? String(settings.value.allowed_audio_types).split(',').map(s => s.trim().toLowerCase())
            : ['mp3', 'wav', 'aac'];
        return audioExtensions.includes(file.extension?.toLowerCase() || '');
    };

    const isArchive = (file: FileItem) => {
        if (!file || !file.extension) return false;
        const ext = file.extension.toLowerCase();
        const archiveExtensions = ['zip', 'tar', 'gz', 'tgz', 'rar', '7z'];
        return archiveExtensions.includes(ext);
    };

    // Computed
    const pathParts = computed(() => {
        const rPath = rootPath.replace(/\/$/, '');
        const cPath = currentPath.value.replace(/\/$/, '');
        
        if (cPath === rPath) return [];
        
        const relativePath = cPath.startsWith(rPath) 
            ? cPath.substring(rPath.length).replace(/^\//, '')
            : cPath.replace(/^\//, '');

        if (!relativePath) return [];
        
        const parts = relativePath.split('/').filter(p => p);
        let accumulated = rPath;
        
        return parts.map((part) => {
            accumulated += '/' + part;
            return {
                name: part,
                path: accumulated,
            };
        });
    });

    const folderTree = computed(() => {
        const tree: FolderItem[] = [];
        const folderMap = new Map<string, FolderItem>();

        allFolders.value.forEach(folder => {
            folderMap.set(folder.path, { ...folder, children: [] });
        });

        allFolders.value.forEach(folder => {
            const lastSlash = folder.path.lastIndexOf('/');
            const parentPath = lastSlash === 0 ? '/' : (folder.path.substring(0, lastSlash) || '/');
            const node = folderMap.get(folder.path);

            if (!node) return;

            if (parentPath === '/' && folder.path !== '/') {
                tree.push(node);
            } else if (folder.path !== '/') {
                const parent = folderMap.get(parentPath);
                if (parent) {
                    if (!parent.children) parent.children = [];
                    parent.children.push(node);
                } else {
                    tree.push(node);
                }
            }
        });

        const sortFolders = (foldersList: FolderItem[]) => {
            foldersList.sort((a, b) => a.name.localeCompare(b.name));
            foldersList.forEach(f => {
                if (f.children) sortFolders(f.children);
            });
        };
        sortFolders(tree);
        return tree;
    });

    const foldersInCurrentPath = computed(() => {
        return allFolders.value.filter((f: FolderItem) => {
            const lastSlash = f.path.lastIndexOf('/');
            const parentPath = lastSlash === 0 ? '/' : (f.path.substring(0, lastSlash) || '/');
            return parentPath === currentPath.value && f.path !== currentPath.value;
        });
    });

    const filesInCurrentPath = computed(() => {
        return files.value;
    });

    const filteredFolders = computed(() => {
        let result = foldersInCurrentPath.value;
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            result = result.filter(f => f.name.toLowerCase().includes(query));
        }
        return result;
    });

    const filteredFiles = computed(() => {
        let result = filesInCurrentPath.value;

        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            result = result.filter(f => f.name.toLowerCase().includes(query));
        }

        if (filterType.value !== 'all') {
            const typeMap: Record<string, string[]> = {
                images: settings.value.allowed_image_types 
                    ? String(settings.value.allowed_image_types).split(',').map(s => s.trim().toLowerCase())
                    : ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'],
                documents: settings.value.allowed_file_types
                    ? String(settings.value.allowed_file_types).split(',').map(s => s.trim().toLowerCase())
                    : ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt', 'xls', 'xlsx', 'ppt', 'pptx'],
                videos: settings.value.allowed_video_types
                    ? String(settings.value.allowed_video_types).split(',').map(s => s.trim().toLowerCase())
                    : ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'],
                audio: settings.value.allowed_audio_types
                    ? String(settings.value.allowed_audio_types).split(',').map(s => s.trim().toLowerCase())
                    : ['mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a'],
                archives: ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'],
            };
            const allowedExts = typeMap[filterType.value] || [];
            result = result.filter(f => allowedExts.includes(f.extension?.toLowerCase() || ''));
        }

        // Advanced Filter Logic
        if (authorFilter.value !== 'all') {
            result = result.filter(f => String(String(f.folder_id)) === String(authorFilter.value));
        }
        if (minSizeFilter.value) {
            result = result.filter(f => (f.size / 1024) >= Number(minSizeFilter.value));
        }
        if (maxSizeFilter.value) {
            result = result.filter(f => (f.size / 1024) <= Number(maxSizeFilter.value));
        }
        if (dateFromFilter.value) {
            const from = new Date(dateFromFilter.value).getTime();
            result = result.filter(f => new Date(f.updated_at).getTime() >= from);
        }
        if (dateToFilter.value) {
            const to = new Date(dateToFilter.value).getTime();
            result = result.filter(f => new Date(f.updated_at).getTime() <= to);
        }

        return result;
    });

    const sortedFolders = computed(() => {
        const items = [...filteredFolders.value];
        items.sort((a, b) => {
            let comp = 0;
            if (sortBy.value === 'name') comp = a.name.localeCompare(b.name);
            else if (sortBy.value === 'date') comp = new Date(a.updated_at).getTime() - new Date(b.updated_at).getTime();
            return sortDirection.value === 'asc' ? comp : -comp;
        });
        return items;
    });

    const sortedFiles = computed(() => {
        const items = [...filteredFiles.value];
        items.sort((a, b) => {
            let comp = 0;
            if (sortBy.value === 'name') comp = a.name.localeCompare(b.name);
            else if (sortBy.value === 'size') comp = (a.size || 0) - (b.size || 0);
            else if (sortBy.value === 'date') comp = new Date(a.updated_at).getTime() - new Date(b.updated_at).getTime();
            return sortDirection.value === 'asc' ? comp : -comp;
        });
        return items;
    });

    const totalItems = computed(() => sortedFolders.value.length + sortedFiles.value.length);

    const paginatedFolders = computed<FolderItem[]>(() => {
        const start = (currentPage.value - 1) * itemsPerPage.value;
        const end = start + itemsPerPage.value;
        return sortedFolders.value.slice(start, end);
    });

    const paginatedFiles = computed<FileItem[]>(() => {
        const start = (currentPage.value - 1) * itemsPerPage.value;
        const end = start + itemsPerPage.value;
        const foldersCount = sortedFolders.value.length;

        const filesStart = Math.max(0, start - foldersCount);
        const filesEnd = Math.max(0, end - foldersCount);

        return sortedFiles.value.slice(filesStart, filesEnd);
    });

    const isAllSelected = computed(() => {
        const allPaths = [...paginatedFolders.value.map(f => f.path), ...paginatedFiles.value.map(f => f.path)];
        if (allPaths.length === 0) return false;
        return allPaths.every(path => selectedItems.value.some(i => i.path === path));
    });

    const activeItem = computed(() => {
        if (selectedItems.value.length === 0) return null;
        return selectedItems.value[selectedItems.value.length - 1];
    });

    const trashCount = computed(() => trashItems.value.length);

    // Methods
    const selectItem = (item: FileItem | FolderItem, multi: boolean = false) => {
        if (!multi) {
            selectedItems.value = [item];
        } else {
            const index = selectedItems.value.findIndex(i => i.path === item.path);
            if (index > -1) {
                selectedItems.value.splice(index, 1);
            } else {
                selectedItems.value.push(item);
            }
        }
    };

    const fetchCurrentPath = async () => {
        loading.value = true;
        try {
            const response = await api.get(infraPaths.fileManager, {
                params: {
                    path: currentPath.value,
                    page: currentPage.value,
                    per_page: itemsPerPage.value,
                    search: searchQuery.value,
                    type: filterType.value,
                    sort: sortBy.value,
                    direction: sortDirection.value
                },
            });
            const data = parseSingleResponse<{ files: FileItem[], folders: FolderItem[] }>(response) || { files: [], folders: [] };
            files.value = Array.isArray(data.files) ? data.files : [];
            const newFolders = Array.isArray(data.folders) ? data.folders : [];

            newFolders.forEach((folder: FolderItem) => {
                if (!allFolders.value.find((f: FolderItem) => f.path === folder.path)) {
                    allFolders.value.push(folder);
                }
            });
            folders.value = newFolders;
            filesCache.value.set(currentPath.value, files.value);
        } catch (error: unknown) {
            logger.error('Failed to fetch files:', error);
        } finally {
            loading.value = false;
        }
    };

    const fetchAllFolders = async (path: string = rootPath, recursive: boolean = false) => {
        if (scannedPaths.value.has(path) && !recursive) return;

        try {
            const response = await api.get(infraPaths.fileManager, {
                params: { path },
            });
            const data = parseSingleResponse<{ folders: FolderItem[] }>(response) || { folders: [] };
            const newFolders = Array.isArray(data.folders) ? data.folders : [];

            newFolders.forEach((folder: FolderItem) => {
                if (!allFolders.value.find((f: FolderItem) => f.path === folder.path)) {
                    allFolders.value.push(folder);
                }
            });

            scannedPaths.value.add(path);

            if (recursive) {
                for (const folder of newFolders) {
                    await fetchAllFolders(folder.path, true);
                }
            }
        } catch (error) {
            logger.error('Failed to fetch folders:', error);
        }
    };

    const navigateToPath = (path: string) => {
        showTrashView.value = false;
        currentPath.value = path;
        fetchCurrentPath();
    };

    const toggleSidebar = () => {
        sidebarCollapsed.value = !sidebarCollapsed.value;
        localStorage.setItem('fileManagerSidebarCollapsed', sidebarCollapsed.value.toString());
    };

    const togglePropertiesSidebar = () => {
        propertiesSidebarVisible.value = !propertiesSidebarVisible.value;
        localStorage.setItem('fileManagerPropertiesVisible', propertiesSidebarVisible.value.toString());
    };

    const toggleFolderExpanded = (path: string) => {
        if (expandedFolders.value.has(path)) {
            expandedFolders.value.delete(path);
        } else {
            expandedFolders.value.add(path);
            if (!scannedPaths.value.has(path)) {
                fetchAllFolders(path);
            }
        }
        expandedFolders.value = new Set(expandedFolders.value);
    };

    const isFolderExpanded = (path: string) => expandedFolders.value.has(path);

    const toggleSelection = (path: string) => {
        const item = [...allFolders.value, ...files.value].find(i => i.path === path);
        if (!item) return;
        const index = selectedItems.value.findIndex(i => i.path === path);
        if (index > -1) selectedItems.value.splice(index, 1);
        else selectedItems.value.push(item);
    };

    const toggleSelectAll = (checked: boolean) => {
        const pagePaths = [...paginatedFolders.value.map(f => f.path), ...paginatedFiles.value.map(f => f.path)];
        if (checked) {
            const itemsToAdd = [...paginatedFolders.value, ...paginatedFiles.value].filter(i => !selectedItems.value.some(si => si.path === i.path));
            selectedItems.value.push(...itemsToAdd);
        } else {
            selectedItems.value = selectedItems.value.filter(i => !pagePaths.includes(i.path));
        }
    };

    const clearSelection = () => {
        selectedItems.value = [];
    };

    const deleteItem = async (item: FileItem | FolderItem) => {
        const isFolder = 'children' in item || !('extension' in item);
        const title = isFolder ? t('infra.fileManager.actions.delete_folder') : t('infra.fileManager.actions.delete_file');
        const message = isFolder ? t('infra.fileManager.messages.deleteFolderConfirm', { name: item.name }) : t('infra.fileManager.messages.deleteFileConfirm', { name: item.name });

        const confirmed = await confirmDialog({
            title,
            message,
            variant: 'danger',
            confirmText: t('common.actions.delete'),
        });

        if (!confirmed) return;

        try {
            const url = isFolder ? infraPaths.fileManagerFolder + '/delete' : infraPaths.fileManagerDelete;
            await api.post(url, { path: item.path.replace(/^\//, '') });

            if (isFolder) {
                const folderPath = item.path.endsWith('/') ? item.path : item.path + '/';
                allFolders.value = allFolders.value.filter(f => f.path !== item.path && !f.path.startsWith(folderPath));
                scannedPaths.value.delete(item.path);
            }
            await fetchCurrentPath();
            await fetchTrash();
            toast.success.action(t('infra.fileManager.messages.bulkDeleted'));
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const bulkDelete = async () => {
        const confirmed = await confirmDialog({
            title: t('infra.fileManager.bulk.delete'),
            message: t('infra.fileManager.bulk.confirmDelete', { count: selectedItems.value.length }),
            variant: 'danger',
            confirmText: t('common.actions.delete'),
        });

        if (!confirmed) return;

        try {
            for (const item of selectedItems.value) {
                const isFolder = 'children' in item || !('extension' in item);
                const url = isFolder ? infraPaths.fileManagerFolder + '/delete' : infraPaths.fileManagerDelete;
                await api.post(url, { path: item.path.replace(/^\//, '') });
                if (isFolder) {
                    const folderPath = item.path.endsWith('/') ? item.path : item.path + '/';
                    allFolders.value = allFolders.value.filter(f => f.path !== item.path && !f.path.startsWith(folderPath));
                    scannedPaths.value.delete(item.path);
                }
            }
            clearSelection();
            await fetchCurrentPath();
            fetchTrash();
            toast.success.action(t('infra.fileManager.messages.bulkDeleted'));
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const copyToClipboard = (items: (FileItem | FolderItem)[], action: 'copy' | 'move' = 'copy') => {
        clipboard.value = {
            items: items.map(item => ({
                path: item.path,
                type: 'extension' in item ? 'file' : 'folder'
            })),
            action
        };
        toast.success.action(t(`infra.fileManager.messages.${action === 'copy' ? 'copiedToClipboard' : 'movedToClipboard'}`));
    };

    const pasteFromClipboard = async (destinationPath: string) => {
        if (clipboard.value.items.length === 0) return;
        loading.value = true;
        try {
            const endpoint = clipboard.value.action === 'move' ? infraPaths.fileManagerMove : '/manage/infra/file-manager/copy';
            for (const item of clipboard.value.items) {
                await api.post(endpoint, {
                    source: item.path.replace(/^\//, ''),
                    destination: destinationPath === '/' ? '' : destinationPath.replace(/^\//, ''),
                    type: item.type
                });
            }
            if (clipboard.value.action === 'move') clipboard.value.items = [];
            allFolders.value = [];
            await fetchAllFolders();
            await fetchCurrentPath();
            toast.success.action(t('infra.fileManager.messages.pasted'));
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        } finally {
            loading.value = false;
        }
    };

    const moveItem = async (sourcePath: string, destinationPath: string, type: 'file' | 'folder') => {
        try {
            await api.post(infraPaths.fileManagerMove, {
                source: sourcePath.replace(/^\//, ''),
                destination: destinationPath === '/' ? '' : destinationPath.replace(/^\//, ''),
                type
            });
            allFolders.value = [];
            await fetchAllFolders();
            await fetchCurrentPath();
            toast.success.action(t('infra.fileManager.messages.moved'));
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        }
    };

    const fetchTrash = async () => {
        trashLoading.value = true;
        try {
            const response = await api.get(infraPaths.fileManagerTrash);
            const data = parseSingleResponse<{ items?: TrashItem[] }>(response) || (response.data as { items?: TrashItem[] } | undefined);
            trashItems.value = Array.isArray(data?.items) ? data.items : [];
        } catch (error) {
            logger.error('Failed to fetch trash:', error);
        } finally {
            trashLoading.value = false;
        }
    };

    watch(showTrashView, (val) => {
        if (val) {
            fetchTrash();
        }
    });

    const restoreTrashItem = async (item: TrashItem) => {
        try {
            await api.post(infraPaths.fileManagerRestore, { id: item.id });
            await fetchTrash();
            allFolders.value = [];
            await fetchAllFolders();
            await fetchCurrentPath();
            toast.success.action(t('infra.fileManager.messages.restored'));
        } catch (error) {
            toast.error.fromResponse(error);
        }
    };

    const emptyTrash = async () => {
        const confirmed = await confirmDialog({
            title: t('infra.fileManager.trash.empty'),
            message: t('infra.fileManager.trash.emptyConfirm') || 'Are you sure you want to permanently delete all items in trash?',
            variant: 'danger',
            confirmText: t('infra.fileManager.trash.empty'),
        });
        if (!confirmed) return;
        try {
            await api.post(infraPaths.fileManagerTrashEmpty);
            toast.success.action(t('infra.fileManager.messages.trashEmptied'));
            fetchTrash();
        } catch (error) {
            toast.error.fromResponse(error);
        }
    };

    const deleteFromTrashPermanent = async (item: TrashItem) => {
        const confirmed = await confirmDialog({
            title: t('infra.fileManager.trash.permanent_delete'),
            message: t('infra.fileManager.trash.confirmPermanentDelete', { name: item.name }),
            variant: 'danger',
            confirmText: t('common.actions.delete'),
        });
        if (!confirmed) return;
        try {
            await api.post(infraPaths.fileManagerTrashPermanent, { id: item.id });
            toast.success.action(t('infra.fileManager.messages.deleteSuccess'));
            fetchTrash();
        } catch (error) {
            toast.error.fromResponse(error);
        }
    };

    const copyPath = async (item: FileItem | FolderItem) => {
        try {
            await navigator.clipboard.writeText(item.path);
            toast.success.action(t('infra.fileManager.messages.path_copied'));
        } catch {
            toast.error.default(t('infra.fileManager.messages.copy_failed'));
        }
    };

    const copyUrl = async (file: FileItem) => {
        if (file.url) {
            try {
                await navigator.clipboard.writeText(file.url);
                toast.success.action(t('infra.fileManager.messages.url_copied'));
            } catch {
                toast.error.default(t('infra.fileManager.messages.copy_failed'));
            }
        }
    };

    const downloadFile = (file: FileItem) => {
        if (file.url) {
            const link = document.createElement('a');
            link.href = file.url;
            link.download = file.name;
            link.click();
        }
    };

    // Watchers
    watch(currentPath, () => {
        clearSelection();
        currentPage.value = 1;
        fetchCurrentPath();
    });

    watch(viewMode, (val) => localStorage.setItem('fileManagerViewMode', val));

    watch([
        searchQuery,
        filterType,
        sortBy,
        sortDirection,
        authorFilter,
        minSizeFilter,
        maxSizeFilter,
        dateFromFilter,
        dateToFilter
    ], (_newValues, _oldValues, onCleanup) => {
        currentPage.value = 1;
        if (searchQuery.value) {
            const timeoutId = setTimeout(() => {
                fetchCurrentPath();
            }, 300);
            onCleanup(() => clearTimeout(timeoutId));
        } else {
            fetchCurrentPath();
        }
    });

    return {
        // State
        files, folders, allFolders, loading, currentPath, viewMode,
        sidebarCollapsed, propertiesSidebarVisible, expandedFolders, showTrashView, trashItems, trashLoading,
        searchQuery, filterType, sortBy, sortDirection, selectedItems,
        authorFilter, minSizeFilter, maxSizeFilter, dateFromFilter, dateToFilter, availableFilters,
        currentPage, itemsPerPage, clipboard, draggedItem, draggedType, dropTarget,
        trashCount,
        // Computed
        pathParts, folderTree, filteredFolders, filteredFiles, sortedFolders, sortedFiles,
        totalItems, paginatedFolders, paginatedFiles, isAllSelected, activeItem,
        // Helpers
        formatFileSize, isImage, isVideo, isAudio, isArchive,
        // Actions
        fetchCurrentPath, fetchAllFolders, navigateToPath, toggleSidebar, togglePropertiesSidebar,
        toggleFolderExpanded, isFolderExpanded, toggleSelection, toggleSelectAll, clearSelection,
        deleteItem, bulkDelete, copyToClipboard, pasteFromClipboard, moveItem,
        fetchTrash, restoreTrashItem, emptyTrash, deleteFromTrashPermanent,
        selectItem, copyPath, copyUrl, downloadFile,
        isMounted
    };
}

export type FileManager = ReturnType<typeof useFileManager>;

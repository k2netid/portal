export interface FileItem {
    name: string;
    path: string;
    url?: string;
    size: number;
    extension: string;
    mime_type?: string;
    type: string;
    updated_at: string;
    is_trashed?: boolean;
    folder_id?: string | null;
}

export interface FolderItem {
    name: string;
    path: string;
    parent_path?: string | null;
    children?: FolderItem[];
    updated_at: string;
    is_trashed?: boolean;
    folder_id?: string | null;
}

export interface TrashItem {
    id: string;
    name: string;
    type: 'file' | 'folder';
    original_path: string;
    formatted_size: string;
    deleted_at: string;
    path: string;
}

export interface FileManagerState {
    showHelp: boolean;
    showTrashView: boolean;
    showCreateFolderModal: boolean;
    showUploadModal: boolean;
    searchQuery: string;
    filterType: string;
    sortBy: string;
    viewMode: string;
    selectedItems: (FileItem | FolderItem)[];
    // ... other state properties can be added here
}

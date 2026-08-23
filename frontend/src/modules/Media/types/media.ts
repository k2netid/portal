export interface Media {
    id: string;
    name: string;
    file_name: string;
    mime_type: string;
    size: number;
    url: string;
    thumbnail_url?: string;
    path: string;
    folder_id?: string | null;
    created_at: string;
    updated_at: string;
    is_trashed?: boolean;
    alt?: string | null;
    description?: string | null;
    extension?: string;
    is_shared?: boolean;
    tag_names?: string[];
    caption?: string | null;
    folder?: {
        id: string;
        name: string;
        path: string;
    } | null;
}

export interface MediaFolder {
    id: string;
    name: string;
    parent_id: string | null;
    path: string;
    is_shared?: boolean;
    is_trashed?: boolean;
    created_at: string;
    updated_at: string;
    children?: MediaFolder[];
    children_count?: number;
    parent?: MediaFolder | null;
}

export interface MediaConstraints {
    allowedExtensions: string[];
    maxSize: number | null;
    minWidth?: number | null;
    minHeight?: number | null;
    maxWidth?: number | null;
    maxHeight?: number | null;
}
export interface MediaStats {
    total_count: number;
    total_size: number;
    types: {
        type: string;
        count: number;
        size: number;
    }[];
    trash_count?: number;
}

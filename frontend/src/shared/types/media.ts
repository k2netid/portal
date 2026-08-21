export interface Media {
    id: number | string;
    name: string;
    file_name?: string;
    mime_type?: string;
    path: string;
    disk?: string;
    size?: number;
    url: string;
    extension?: string;
    alt?: string;
    alt_text?: string;
    caption?: string;
    created_at?: string;
    updated_at?: string;
}

export interface MediaFolder {
    id: number | string;
    name: string;
    parent_id?: number | string | null;
    children?: MediaFolder[];
    is_trashed?: boolean;
    updated_at?: string;
}

export interface MediaConstraints {
    max_size?: number;
    maxSize?: number;
    allowed_types?: string[];
    allowedExtensions?: string[];
    minWidth?: number;
    minHeight?: number;
    maxWidth?: number;
    maxHeight?: number;
    max_dimensions?: {
        width?: number;
        height?: number;
    };
}

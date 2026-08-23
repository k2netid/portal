export interface Tag {
    id: string;
    name: string;
    slug: string;
    type?: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
    posts_count?: number;
    contents_count?: number;
    isNew?: boolean; // UI only
}

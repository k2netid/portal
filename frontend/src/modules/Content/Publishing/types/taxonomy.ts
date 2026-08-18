import type { Tag } from '@/modules/Content/Library/types/taxonomy';

export type { Tag };

export interface Category {
    id: string;
    name: string;
    slug: string;
    description?: string;
    parent_id?: string | null;
    created_at?: string;
    updated_at?: string;
    posts_count?: number;
    contents_count?: number;
    image?: string | null;
    is_active?: boolean;
    sort_order?: number;
    children?: Category[];
    all_children?: Category[];
}

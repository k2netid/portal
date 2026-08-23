import type { Category, Tag } from './taxonomy';
import type { MenuItem } from '@/modules/Layout/types/menu';

export * from './taxonomy';

export interface ContentForm {
    title: string;
    slug: string;
    type: 'post' | 'page' | 'custom' | string;
    status: 'draft' | 'published' | 'scheduled' | 'archived' | 'pending' | 'trashed' | string;
    excerpt?: string;
    intro?: string;
    body?: string; // content alias
    content?: string;
    featured_image?: string | null;
    featured_image_title?: string;
    featured_image_caption?: string;
    featured_image_position?: 'hero' | 'inline-top' | 'full-bleed' | string;
    category_id?: string | null;
    published_at?: string;
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    og_image?: string | null;
    comment_status?: boolean;
    is_featured?: boolean;
    tags?: Tag[];
    meta?: Record<string, any>;
    menu_item?: MenuItem;
    menu_items?: MenuItem[];
}

export interface Content extends ContentForm {
    id: string;
    author_id?: string;
    author?: {
        id: string;
        name: string;
        email: string;
    } | null;
    category?: Category | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    lock_status?: {
        is_locked: boolean;
        locked_by?: string;
        locked_at?: string;
    } | null;
}

export interface CMSState {
    contents: Content[];
    categories: Category[];
    settings: Record<string, string | boolean | null>;
    currentContent: Content | null;
    loading: boolean;
    loadingGroups: Record<string, boolean>;
    settingsPromises: Record<string, Promise<unknown>>;
    publicSettingsLoaded: boolean;
}

export interface ContentTemplate {
    id: string;
    name: string;
    slug: string;
    description?: string;
    type: 'post' | 'page' | 'custom';
    title_template: string;
    body_template: string;
    excerpt_template: string;
    created_at?: string;
    updated_at?: string;
}

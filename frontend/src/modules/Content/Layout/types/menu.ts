import type { Component } from 'vue';

export interface Menu {
    id: string | string;
    name: string;
    description?: string;
    location?: string;
    locale?: string;
    is_active?: boolean;
    items?: MenuItem[];
    parent_items?: MenuItem[];
    created_at?: string;
    updated_at?: string;
}

export type PropertyValue = string | number | boolean | null | undefined | unknown[] | Record<string, unknown>;

export interface MenuItem {
    id?: string | null;
    _temp_id?: string;
    parent_id?: string | null;
    menu_id?: string | null;
    title?: string;
    type?: string; // 'custom', 'post', 'page', 'category', 'system'
    target_id?: string | null;
    url?: string | null;
    target?: string;
    order?: number;
    depth?: number;
    icon?: string | null;
    css_class?: string | null;
    description?: string | null;
    badge?: string | null;
    badge_color?: string | null;
    open_in_new_tab?: boolean;
    is_active?: number | boolean;
    sort_order?: number;

    // Media / Mega Menu
    image?: string | null;
    image_size?: string;
    mega_menu_layout?: string;
    mega_menu_column?: number;
    mega_menu_show_dividers?: boolean;

    // Display
    hide_label?: boolean;
    heading?: string | null;
    show_heading_line?: boolean;

    children?: MenuItem[];
    add_to_menu?: boolean; // UI only
    [key: string]: PropertyValue;
}

export interface MenuItemDTO {
    id?: string | null;
    parent_id?: string | null;
    menu_id?: string | null;
    sort_order?: number;
    title: string;
    type: string;
    target_id?: string | null;
    url?: string;
    icon?: string | null;
    css_class?: string | null;
    description?: string | null;
    badge?: string | null;
    badge_color?: string | null;
    open_in_new_tab?: boolean;
    is_active?: number | boolean;
    image?: string | null;
    image_size?: string;
    mega_menu_layout?: string;
    mega_menu_column?: number;
    mega_menu_show_dividers?: boolean;
    hide_label?: boolean;
    heading?: string | null;
    show_heading_line?: boolean;
}

export interface MenuItemSetting {
    key: string;
    type: 'text' | 'textarea' | 'select' | 'boolean' | 'number' | 'color' | 'icon_picker' | 'media' | 'data_select';
    label: string;
    required?: boolean;
    default?: unknown;
    placeholder?: string;
    description?: string;
    options?: { label: string; value: string | boolean | null }[];
    group?: 'appearance' | 'mega_menu' | 'badge' | string;
    min?: number;
    max?: number;
    source?: string;
    labelField?: string;
    valueField?: string;
}

export interface MenuItemDataSource {
    endpoint: string;
    labelField: string;
    valueField: string;
}

export interface MenuItemDefinition {
    name: string;
    label: string;
    category: 'basic' | 'content' | 'structure' | string;
    icon: Component | string; // Component or icon name
    color: string;
    description: string;
    defaultTitle: string;
    dataSource?: MenuItemDataSource;
    settings: MenuItemSetting[];
}

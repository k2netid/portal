export type MenuUsageType = 'theme_assignment' | 'public_location';

export interface MenuUsageEntry {
    type: MenuUsageType;
    location_label?: string;
    theme_name?: string;
    theme_slug?: string;
    theme_is_active?: boolean;
    slot_key?: string;
    location?: string;
    detail?: string;
}

export interface MenuUsageAnalysis {
    menu_id: string;
    is_active: boolean;
    is_in_use: boolean;
    is_served_on_public: boolean;
    location: string | null;
    usages: MenuUsageEntry[];
}

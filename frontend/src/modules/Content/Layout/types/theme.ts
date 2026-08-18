export interface ThemeOption {
    label: string;
    value: string | boolean;
}

export interface ThemeSetting {
    type: string;
    label: string;
    hidden?: boolean;
    category?: string;
    required?: boolean;
    min?: number;
    max?: number;
    step?: number;
    options?: ThemeOption[] | string;
    fields?: {
        name: string;
        label: string;
        type: string;
        options?: string | ThemeOption[];
    }[];
    placeholder?: string;
    description?: string;
    unit?: string;
    default?: unknown;
}

export interface ThemeManifest {
    settings_schema?: Record<string, ThemeSetting>;
    menus?: Record<string, string>;
    supports?: Record<string, boolean | string>;
    [key: string]: unknown;
}

export interface Theme {
    id: string | string;
    name: string;
    slug: string;
    manifest?: ThemeManifest;
    supports?: Record<string, boolean | string>;
    settings?: Record<string, unknown>;
    custom_css?: string;
    css_variables?: string;
    assets?: {
        css?: string[];
        js?: string[];
    };
    [key: string]: unknown;
}

export interface ThemeSection {
    id: string;
    label: string;
    settings: (ThemeSetting & { key: string })[];
}

export type ThemeSettings = Record<string, unknown>;
export type ThemeData = Theme;

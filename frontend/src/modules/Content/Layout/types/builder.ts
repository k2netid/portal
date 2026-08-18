import type { Ref, ComputedRef, Component } from 'vue';
import type { Category, Tag } from './taxonomy';
export type { Category, Tag };
import type { Menu } from './menu';
import type { ThemeData, ThemeSettings } from './theme';
export type { ThemeData, ThemeSettings };

export interface BuilderOptions {
    mode?: 'site' | 'page';
}

export interface ModalState<T = unknown> {
    visible: boolean;
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    resolve?: ((value: T) => void) | null;
}

export interface ConfirmModalState extends ModalState<boolean> {
    type?: 'warning' | 'info' | 'error' | 'delete' | 'danger' | string;
}

export interface InputModalState extends ModalState<string | null> {
    placeholder?: string;
    initialValue?: string;
    confirmText?: string;
    cancelText?: string;
}

export interface SavePresetModalState {
    visible: boolean;
    moduleId: string | null;
    loading: boolean;
}

export interface SubFieldGroup {
    match?: string;
    fields: ModuleField[];
}

export interface ResponsiveModalState {
    label: string;
    baseKey: string;
    type: string;
    options?: unknown[] | Record<string, unknown>;
    module: BlockInstance;
    settings: Record<string, unknown>;
    subFields?: SubFieldGroup[];
}

export interface ModuleSettings {
    _label?: string;
    disabled?: boolean;
    [key: string]: unknown; // Dynamically added settings
}

export interface BlockInstance {
    id: string;
    type: string;
    settings: ModuleSettings;
    children?: BlockInstance[];
    data?: Record<string, unknown>; // For compatibility with dynamic data
    sourceType?: string; // For compatibility
    dynamicSettings?: Record<string, string>;
}

export interface Canvas {
    id: string;
    title: string;
    blocks: BlockInstance[];
    isMain: boolean;
    isGlobal?: boolean;
    append?: boolean;
}

export interface PageMetadata {
    id: number | null;
    title: string;
    slug: string;
    status: string;
    excerpt?: string;
    body?: string;
    type?: string;
    editor_type?: string;
    category_id?: number | null;
    featured_image?: string | null;
    published_at?: string | null;
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    og_image?: string | null;
    comment_status?: boolean;
    is_featured?: boolean;
    tags?: Tag[];
    menu_item?: {
        add_to_menu: boolean;
        menu_id: string;
        parent_id: number | null;
        title: string;
    };
    meta?: Record<string, unknown>;
    [key: string]: unknown;
}

export interface GlobalSettings {
    container_max_width: string;
    block_spacing: string;
    [key: string]: string | number | boolean | null | undefined;
}

export interface BuilderHistoryEntry {
    blocks: BlockInstance[];
    globalSettings: GlobalSettings;
    editingIndex: number | null;
    activeBlockId: string | null;
}

export interface HistorySnapshot {
    blocks: string;
    timestamp: number;
}

export interface BuilderState {
    mode: Ref<string>;
    canvases: Ref<Canvas[]>;
    activeCanvasId: Ref<string>;
    blocks: Ref<BlockInstance[]>;
    history: Ref<string[]>;
    historyIndex: Ref<number>;
    maxHistory: number;
    selectedModuleId: Ref<string | null>;
    hoveredModuleId: Ref<string | null>;
    activeTab: Ref<string>;
    device: Ref<'desktop' | 'tablet' | 'mobile'>;
    deviceModeType: Ref<'auto' | 'manual'>;
    customViewportWidth: Ref<number | null>;
    zoom: Ref<number>;
    wireframeMode: Ref<boolean>;
    gridViewMode: Ref<boolean>;
    isFullscreen: Ref<boolean>;
    activeTheme: Ref<string>;
    selectedThemeSlug: Ref<string | null>;
    themeData: Ref<ThemeData | null>;
    themeSettings: Ref<ThemeSettings>;
    responsiveModal: Ref<ResponsiveModalState | null>;
    savePresetModal: Ref<SavePresetModalState>;
    confirmModal: Ref<ConfirmModalState>;
    inputModal: Ref<InputModalState>;
    content: Ref<PageMetadata>;
    showGrid: Ref<boolean>;
    snapToObjects: Ref<boolean>;
    autoSave: Ref<boolean>;
    pages: Ref<PageMetadata[]>;
    currentPageId: Ref<number | null>;
    pagesLoading: Ref<boolean>;
    categories: Ref<Category[]>;
    availableTags: Ref<Tag[]>;
    menus: Ref<Menu[]>;
    availableThemes: Ref<ThemeData[]>;
    loadingThemes: Ref<boolean>;
    clipboard: Ref<{ type: string; data: Record<string, unknown>; sourceType?: string } | null>;
    dataVersion: Ref<number>;
    lastSavedVersion: Ref<number>;
    markAsDirty: () => void;
    isDirty: ComputedRef<boolean>;
    insertTargetId: Ref<string | null>;
}

export interface SettingOption {
    label?: string;
    value: string | number | boolean | null;
    icon?: string | Component;
    group?: string;
}

export interface SettingDefinition {
    key?: string;
    name?: string; // Alias for key
    type: string;
    label?: string;
    default?: unknown;
    options?: SettingOption[] | string[] | unknown[] | string;
    tab?: 'content' | 'style' | 'advanced';
    condition?: (settings: Record<string, unknown>) => boolean;
    show_if?: { field: string; value: unknown; operator?: string } | { field: string; value: unknown; operator?: string }[] | Record<string, unknown>;
    responsive?: boolean;
    multiple?: boolean;
    searchable?: boolean;
    itemLabel?: string;
    units?: string[];
    unit?: string;
    min?: number;
    max?: number;
    step?: number;
    fields?: ModuleField[];
    filter_by?: {
        field: string;
        match_key: string;
    };
    placeholder?: string;
    rows?: number;
    [key: string]: unknown;
}

export type ModuleField = SettingDefinition | ModuleGroup;

export interface ModuleGroup {
    id: string;
    label: string;
    description?: string;
    presets?: boolean;
    fields: ModuleField[];
    show_if?: { field: string; value: unknown; operator?: string } | { field: string; value: unknown; operator?: string }[] | Record<string, unknown>;
    condition?: (settings: Record<string, unknown>) => boolean;
}

export interface BlockDefinition {
    type?: string;
    title?: string;
    name?: string;
    label?: string;
    description?: string;
    icon?: string | Component;
    category?: string;
    component?: Component | string;
    settingsComponent?: Component | string;
    defaultSettings?: Record<string, unknown> | null;
    defaults?: Record<string, unknown> | null; // Legacy alias for defaultSettings
    canHaveChildren?: boolean;
    allowedChildren?: string[];
    children?: string[] | null; // Legacy alias for allowedChildren/canHaveChildren hint
    settings?: SettingDefinition[] | {
        content?: (SettingDefinition | ModuleGroup)[];
        design?: (SettingDefinition | ModuleGroup)[];
        advanced?: (SettingDefinition | ModuleGroup)[];
        [key: string]: (SettingDefinition | ModuleGroup)[] | undefined;
    };
    parent?: string[] | string | null;
}

// Alias for BlockDefinition
export type ModuleDefinition = BlockDefinition;

export interface ModuleManager {
    selectedModule: ComputedRef<BlockInstance | null>;
    modulePath: ComputedRef<BlockInstance[]>;
    selectModule: (id: string | null) => void;
    hoverModule: (id: string | null) => void;
    clearSelection: () => void;
    insertModule: (type: string, parentId?: string | null, index?: number) => BlockInstance | null;
    insertFromPreset: (preset: BuilderPreset, parentId?: string | null, index?: number) => BlockInstance | null;
    removeModule: (id: string) => boolean;
    duplicateModule: (id: string) => BlockInstance | null;
    moveModule: (id: string, direction: 'up' | 'down') => boolean;
    updateModuleSettings: (id: string, settings: ModuleSettings) => boolean;
    updateModule: (id: string, data: Partial<BlockInstance>) => boolean;
    updateModuleSetting: (id: string, key: string, value: unknown) => boolean;
    applyPreset: (id: string, preset: BuilderPreset) => boolean;
    resetModuleStyles: (id: string) => boolean;
    updateRowLayout: (rowId: string, layout: string | Record<string, unknown>) => boolean;
    copyModule: (id: string) => boolean;
    pasteModule: (parentId: string | null, index?: number) => BlockInstance | null;
    copyStyles: (id: string) => boolean;
    pasteStyles: (id: string) => boolean;
    resetLayout: () => void;
    findModule: (id: string) => BlockInstance | null;
    findParentById: (items: BlockInstance[], id: string) => BlockInstance | null;
    getModulePath: (items: BlockInstance[], id: string) => BlockInstance[];

    // Management/Registry Parts
    definitions: Ref<Record<string, BlockDefinition>>;
    moduleCategories: Ref<string[]>;
    loadingModules: Ref<boolean>;
    registerModule: (definition: BlockDefinition) => void;
    getModuleDefinition: (type: string) => BlockDefinition | undefined;
    fetchTemplates: () => Promise<unknown[]>;
    fetchWidgets: () => Promise<unknown[]>;
}

export interface GlobalVariablesManager {
    globalNumbers: Ref<GlobalVariable[]>;
    globalText: Ref<GlobalVariable[]>;
    globalImages: Ref<GlobalVariable[]>;
    globalLinks: Ref<GlobalVariable[]>;
    globalColors: Ref<GlobalVariable[]>;
    globalFonts: Ref<GlobalVariable[]>;
    addVariable: (type: string) => void;
    deleteVariable: (index: number, type: string) => void;
    loadVariables: (data: Record<string, GlobalVariable[]>) => void;
}

export interface BuilderInstance extends BuilderState, ModuleManager {
    // History
    canUndo: ComputedRef<boolean>;
    canRedo: ComputedRef<boolean>;
    takeSnapshot: (options?: { immediate?: boolean; delay?: number }) => void;
    undo: () => boolean;
    redo: () => boolean;

    // UI & Sync
    closeResponsiveModal: () => void;
    closeSavePresetModal: () => void;
    closeConfirmModal: (result: boolean) => void;
    closeInputModal: (result: string | null) => void;

    // Canvas Operations
    addCanvas: (title: string) => void;
    removeCanvas: (id: string) => void;
    renameCanvas: (id: string, title: string) => void;
    switchCanvas: (id: string) => void;
    duplicateCanvas: (id: string) => void;
    exportCanvas: (id: string) => void;
    setMainCanvas: (id: string) => void;
    setDeviceMode: (mode: 'desktop' | 'tablet' | 'mobile') => void;
    setDeviceModeAuto: () => void;

    // Helpers
    globalVariables: GlobalVariablesManager;
    saveGlobalVariables: () => Promise<void>;
    // UI & Sync Extensions (Provided by Builder.vue)
    activePanel: Ref<string | null>;
    sidebarVisible: Ref<boolean>;
    darkMode: Ref<boolean>;
    globalAction: Ref<{ type: string; payload: unknown } | null>;

    loadTheme: (slug?: string | null) => Promise<void>;
    fetchPresets: () => Promise<void>;
    handleSavePreset: (name: string) => Promise<void>;
    confirm: (options: Partial<ConfirmModalState> & Record<string, unknown>) => Promise<boolean>;
    prompt: (options: Partial<InputModalState> & Record<string, unknown>) => Promise<string | null>;
    openResponsiveModal: (config: ResponsiveModalState) => void;
    fetchThemes: () => Promise<void>; // Restored
    updateThemeSettings: (themeSlug: string, settings: ThemeSettings) => Promise<void>; // Restored
    createTemplate: (data: { name: string; type: string }) => Promise<unknown>;
    deleteTemplate: (id: string | number) => Promise<boolean>;
    updateContentMeta: (id: string | number, meta: Record<string, unknown>) => Promise<unknown>;
    savePreset: (module: BlockInstance, name: string) => Promise<BuilderPreset>;
    deletePreset: (id: string | number) => Promise<boolean | void>;
    getComponent: (type: string) => Component | undefined;


    // External
    markAsSaved: () => void;
    loadContent: (id: string | number) => Promise<void>;
    fetchMetadata: () => Promise<void>;
    applyThemeStyles: () => void;

    // Modals helpers (runtime injected)
    openIconPickerModal?: (value: string, onSelect: (icon: string) => void) => void;
    openAddCanvasModal?: () => void;
    openImportExportModal?: (id: string) => void;
    openSavePresetModal?: (moduleId: string) => void;
    openInsertModal?: (targetId: string | null, index?: number) => void;
    openInsertRowModal?: (targetId: string | null) => void;
    openUpdateRowModal?: (rowId: string | null) => void;
    openInsertSectionModal?: (index?: number) => void;
    openStructureTemplateModal?: (targetId: string | null, targetType: string | null) => void;
    openContextMenu?: (moduleId: string, event: MouseEvent, title?: string, type?: string, mode?: string) => void;

    // Presets & Dynamic Content
    presets: Ref<BuilderPreset[]>;
    loadingPresets: Ref<boolean>;

    // Pages
    fetchPages: () => Promise<void>;
    setCurrentPage: (id: number | string) => void;
    addPage: (title: string) => Promise<void>;
    deletePage: (id: number | string) => Promise<void>;
}

export interface BuilderPreset {
    id: string | number;
    name: string;
    type: string;
    settings: ModuleSettings;
    category?: string;
    is_system?: boolean;
    image?: string;
}

export interface PresetManager {
    presets: Ref<BuilderPreset[]>;
    loading: Ref<boolean>;
    fetchPresets: (type?: string) => Promise<void>;
    savePreset: (module: BlockInstance, name: string) => Promise<BuilderPreset>;
    deletePreset: (id: string) => Promise<void>;
}

export interface GlobalVariable {
    id: string;
    name: string;
    slug?: string;
    type?: string;
    value?: unknown;
    group?: string;
    unit?: string;
    hex?: string;
    opacity?: number;
    family?: string;
}

export interface BuilderContext {
    state: BuilderState;
    availableVariables: Ref<GlobalVariable[]>;
    colors: Ref<GlobalVariable[]>;
    fonts: Ref<GlobalVariable[]>;
    spacings: Ref<GlobalVariable[]>;
    containers: Ref<GlobalVariable[]>;

    // Actions
    save: (data?: Record<string, unknown>) => Promise<void>;
    undo: () => void;
    redo: () => void;
    preview: () => void;

    // Modals
    confirm: (options: { title: string, message: string, variant?: string }) => Promise<boolean>;
    prompt: (options: { title: string, label: string, defaultValue?: string }) => Promise<string | null>;
    openResponsiveModal: (config: Record<string, unknown>) => void;
}

export interface DragData {
    type: 'module' | 'block' | 'section';
    id: string;
    data?: BlockInstance | Record<string, unknown>;
}

export interface BlockProps {
    module: BlockInstance;
    mode: 'edit' | 'view';
    device?: 'desktop' | 'tablet' | 'mobile';
    manualStyles?: boolean;
    settings?: ModuleSettings;
    nestedBlocks?: BlockInstance[];
    context?: Record<string, unknown>;
    id?: string;
}

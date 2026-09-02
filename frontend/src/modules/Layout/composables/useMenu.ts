import { logger } from '@/shared/utils/logger';
import { ref, computed, watch, provide, inject, type InjectionKey, type Ref, toRaw } from 'vue';
import api from '@/engine/api/client';
import { parseResponse, ensureArray, parseSingleResponse } from '@/shared/utils/responseParser';
import type { Menu, MenuItem, MenuItemDTO } from '@/modules/Layout/types/menu';

export interface MenuContext {
    menu: Ref<Menu | null>;
    items: Ref<MenuItem[]>;
    selectedItem: Ref<MenuItem | null>;
    selectedItemId: Ref<string | number | null>;
    isLoading: Ref<boolean>;
    isSaving: Ref<boolean>;
    error: Ref<unknown>;
    isDirty: Ref<boolean>;
    canUndo: Ref<boolean>;
    canRedo: Ref<boolean>;
    undo: () => void;
    redo: () => void;
    takeSnapshot: () => void;
    clipboard: Ref<MenuItem | null>;
    canPaste: Ref<boolean>;
    copyItem: (item: MenuItem) => void;
    cutItem: (item: MenuItem) => void;
    pasteItem: (parentId?: number | string | null) => void;
    findItemById: (id: string | number | null) => MenuItem | null;
    findParent: (id: string | number | null) => MenuItem | null;
    buildTree: (flatItems: MenuItem[]) => MenuItem[];
    flattenTree: (treeItems: MenuItem[]) => MenuItemDTO[];
    addItem: (itemData: Partial<MenuItem>, parentId?: number | string | null) => MenuItem;
    removeItem: (id: string | number | null) => void;
    updateItem: (id: string | number | null, updates: Partial<MenuItem>) => void;
    duplicateItem: (id: string | number | null) => void;
    moveItem: (id: string | number | null, newParentId: number | string | null, newIndex?: number | null) => void;
    selectItem: (id: string | number | null) => void;
    clearSelection: () => void;
    fetchMenu: () => Promise<void>;
    saveMenu: (menuData?: Partial<Menu>) => Promise<boolean>;
    deleteItem: (id: string | number | null) => Promise<void>;
    markClean: () => void;
    menus: Ref<Record<string, Menu>>;
    fetchMenuByLocation: (location: string) => Promise<void>;
    fetchMenuByIdentifier: (identifier: string | number, cacheKey?: string) => Promise<void>;
}

const MENU_CONTEXT_KEY: InjectionKey<MenuContext> = Symbol('menuBuilder');

/**
 * Generate a unique ID for new menu items
 */
const generateId = () => 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

/**
 * Deep clone an object, handling Vue Proxies
 */
const deepClone = <T>(obj: T): T => {
    return JSON.parse(JSON.stringify(toRaw(obj)));
};

/**
 * Build nested tree from flat items array
 */
const buildTree = (flatItems: MenuItem[], parentId: number | string | null = null): MenuItem[] => {
    if (!Array.isArray(flatItems)) return [];
    return flatItems
        .filter(item => item.parent_id == parentId) // Use loose comparison for string/number IDs
        .sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0))
        .map(item => ({
            ...item,
            children: buildTree(flatItems, item.id as number | string | null)
        }));
};

/**
 * useMenu composable - Centralized state management for Menu Builder
 */
// Global state for frontend/shared usage
const menus = ref<Record<string, Menu>>({});

const fetchMenuByLocation = async (location: string) => {
    try {
        const response = await api.get(`/public/layout/menus/location/${location}`);
        const menu = parseSingleResponse(response) as Menu;
        if (menu) {
            if (menu.parent_items) {
                menu.items = menu.parent_items;
            } else if (Array.isArray(menu.items)) {
                menu.items = buildTree(menu.items);
            }
        }
        menus.value[location] = menu;
    } catch (err) {
        // Silent fail for frontend menus to avoid crashing app
        logger.warning(`Failed to fetch menu for location: ${location}`, err);
    }
};

const fetchMenuByIdentifier = async (identifier: string | number, cacheKey?: string) => {
    const raw = String(identifier ?? '').trim();
    if (!raw || raw === 'none') {
        return;
    }

    const isNumericId = /^\d+$/.test(raw);
    const isUuid = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(raw);
    const targetKey = cacheKey || raw;

    try {
        const endpoint = (isNumericId || isUuid)
            ? `/public/layout/menus/${raw}`
            : `/public/layout/menus/location/${raw}`;
        const response = await api.get(endpoint);
        const menu = parseSingleResponse(response) as Menu;
        if (menu) {
            if (menu.parent_items) {
                menu.items = menu.parent_items;
            } else if (Array.isArray(menu.items)) {
                menu.items = buildTree(menu.items);
            }
        }
        menus.value[targetKey] = menu;
    } catch (err) {
        logger.warning(`Failed to fetch menu by identifier: ${raw}`, err);
    }
};

export function useMenu(menuId?: Ref<number | string | null>) {
    // ==================== STATE ====================
    const menu = ref<Menu | null>(null);
    const items = ref<MenuItem[]>([]);
    const selectedItemId = ref<number | string | null>(null);
    const isLoading = ref(false);
    const isSaving = ref(false);
    const error = ref<unknown>(null);

    // Initial state for dirty tracking
    const initialState = ref<MenuItem[] | null>(null);

    // ==================== HISTORY ====================
    interface HistoryState {
        items: MenuItem[];
        selectedItemId: string | number | null;
    }
    const history = ref<HistoryState[]>([]);
    const historyIndex = ref(-1);
    const isUndoing = ref(false);
    const MAX_HISTORY = 50;

    /**
     * Take a snapshot of current state for history
     */
    const takeSnapshot = () => {
        if (isUndoing.value) return;

        const currentState: HistoryState = deepClone({
            items: items.value,
            selectedItemId: selectedItemId.value
        });

        // Don't add duplicate states
        const lastState = history.value[historyIndex.value];
        if (lastState && JSON.stringify(currentState) === JSON.stringify(lastState)) {
            return;
        }

        // Remove future history if we're in the middle
        if (historyIndex.value < history.value.length - 1) {
            history.value = history.value.slice(0, historyIndex.value + 1);
        }

        history.value.push(currentState);
        historyIndex.value++;

        // Limit history size
        if (history.value.length > MAX_HISTORY) {
            history.value.shift();
            historyIndex.value--;
        }
    };

    const canUndo = computed(() => historyIndex.value > 0);
    const canRedo = computed(() => historyIndex.value < history.value.length - 1);

    const undo = () => {
        if (!canUndo.value) return;

        isUndoing.value = true;
        historyIndex.value--;
        const prevState = history.value[historyIndex.value];
        if (prevState) {
            items.value = deepClone(prevState.items);
            selectedItemId.value = prevState.selectedItemId;
        }
        setTimeout(() => isUndoing.value = false, 0);
    };

    const redo = () => {
        if (!canRedo.value) return;

        isUndoing.value = true;
        historyIndex.value++;
        const nextState = history.value[historyIndex.value];
        if (nextState) {
            items.value = deepClone(nextState.items);
            selectedItemId.value = nextState.selectedItemId;
        }
        setTimeout(() => isUndoing.value = false, 0);
    };

    // ==================== CLIPBOARD ====================
    const clipboard = ref<MenuItem | null>(null);
    const clipboardAction = ref<'copy' | 'cut' | null>(null);

    const canPaste = computed(() => clipboard.value !== null);

    const copyItem = (item: MenuItem) => {
        clipboard.value = deepClone(item);
        clipboardAction.value = 'copy';
    };

    const cutItem = (item: MenuItem) => {
        clipboard.value = deepClone(item);
        clipboardAction.value = 'cut';
        removeItem(item.id || item._temp_id!);
    };

    const pasteItem = (parentId: number | string | null = null) => {
        if (!clipboard.value) return;

        const newItem = deepClone(clipboard.value);
        // Reset IDs for the pasted item and all children
        const resetIds = (item: MenuItem) => {
            item.id = null;
            item._temp_id = generateId();
            if (item.children) {
                item.children.forEach(resetIds);
            }
        };
        resetIds(newItem);

        if (parentId) {
            // Add as child of specified parent
            const parent = findItemById(parentId);
            if (parent) {
                if (!parent.children) parent.children = [];
                parent.children.push(newItem);
            }
        } else {
            // Add to root
            items.value.push(newItem);
        }

        if (clipboardAction.value === 'cut') {
            clipboard.value = null;
            clipboardAction.value = null;
        }

        takeSnapshot();
    };

    // ==================== DIRTY STATE ====================
    const isDirty = computed(() => {
        if (!initialState.value) return false;
        return JSON.stringify(items.value) !== JSON.stringify(initialState.value);
    });

    const markClean = () => {
        initialState.value = deepClone(items.value);
    };

    // ==================== ITEM HELPERS ====================


    /**
     * Flatten tree to array for API
     */
    const flattenTree = (treeItems: MenuItem[], parentId: string | null = null): MenuItemDTO[] => {
        let result: MenuItemDTO[] = [];
        treeItems.forEach((item, index) => {
            result.push({
                id: item.id as string | null,
                parent_id: parentId as string | null,
                sort_order: index,
                title: (item.title as string) || '',
                type: (item.type as 'custom' | 'page' | 'post' | 'category' | 'tag' | 'external') || 'custom',
                target_id: item.target_id as string | null,
                url: item.url as string | undefined,
                icon: item.icon || null,
                css_class: item.css_class || null,
                description: item.description || null,
                badge: item.badge || null,
                badge_color: item.badge_color || 'primary',
                mega_menu_layout: item.mega_menu_layout || 'default',
                mega_menu_column: item.mega_menu_column || 0,
                open_in_new_tab: item.open_in_new_tab || false,
                image: item.image || null,
                image_size: item.image_size || 'auto',
                mega_menu_show_dividers: item.mega_menu_show_dividers || false,
                hide_label: item.hide_label || false,
                heading: item.heading || null,
                show_heading_line: item.show_heading_line || false,
                menu_id: String(menuId?.value || ''),
                is_active: item.is_active ? 1 : 0,
            });
            if (item.children && item.children.length > 0) {
                result = result.concat(flattenTree(item.children, item.id as string | null));
            }
        });
        return result;
    };

    /**
     * Find item by ID recursively
     */
    const findItemById = (id: string | number | null, searchItems: MenuItem[] = items.value): MenuItem | null => {
        if (!searchItems || !Array.isArray(searchItems)) return null;
        for (const item of searchItems) {
            if (!item) continue;
            const itemId = item.id || item._temp_id;
            // Loose equality for string/number match
            if (itemId == id) return item;
            if (item.children) {
                const found = findItemById(id, item.children);
                if (found) return found;
            }
        }
        return null;
    };

    /**
     * Find parent of an item
     */
    const findParent = (id: string | number | null, searchItems: MenuItem[] = items.value, parent: MenuItem | null = null): MenuItem | null => {
        if (!searchItems || !Array.isArray(searchItems)) return null;
        for (const item of searchItems) {
            if (!item) continue;
            const itemId = item.id || item._temp_id;
            if (itemId == id) return parent;
            if (item.children) {
                const found = findParent(id, item.children, item);
                if (found) return found;
            }
        }
        return null;
    };

    /**
     * Get selected item
     */
    const selectedItem = computed(() => {
        if (!selectedItemId.value) return null;
        return findItemById(selectedItemId.value);
    });

    // ==================== ITEM ACTIONS ====================

    const addItem = (itemData: Partial<MenuItem>, parentId: number | string | null = null): MenuItem => {
        const newItem: MenuItem = {
            id: null,
            _temp_id: generateId(),
            title: itemData.title || 'New Item',
            type: itemData.type || 'custom',
            target_id: itemData.target_id || null,
            url: itemData.url || '#',
            children: [],
            is_active: 1,
            ...itemData
        };

        if (parentId) {
            const parent = findItemById(parentId);
            if (parent) {
                if (!parent.children) parent.children = [];
                parent.children.push(newItem);
            }
        } else {
            items.value.push(newItem);
        }

        takeSnapshot();
        return newItem;
    };

    const removeItem = (id: string | number | null) => {
        const removeFromList = (list: MenuItem[]): boolean => {
            const index = list.findIndex(i => (i.id || i._temp_id) == id);
            if (index > -1) {
                list.splice(index, 1);
                return true;
            }
            for (const item of list) {
                if (item.children && removeFromList(item.children)) {
                    return true;
                }
            }
            return false;
        };

        removeFromList(items.value);

        if (selectedItemId.value == id) {
            selectedItemId.value = null;
        }

        takeSnapshot();
    };

    const updateItem = (id: string | number | null, updates: Partial<MenuItem>) => {
        const item = findItemById(id);
        if (item) {
            Object.assign(item, updates);
            takeSnapshot();
        }
    };

    const duplicateItem = (id: string | number | null) => {
        const item = findItemById(id);
        if (!item) return;

        const parent = findParent(id);
        const targetList = parent ? parent.children! : items.value;
        const index = targetList.findIndex(i => (i.id || i._temp_id) == id);

        const clone = deepClone(item);
        const resetIds = (i: MenuItem) => {
            i.id = null;
            i._temp_id = generateId();
            if (i.children) i.children.forEach(resetIds);
        };
        resetIds(clone);
        clone.title = `${clone.title} (Copy)`;

        targetList.splice(index + 1, 0, clone);
        takeSnapshot();
    };

    const moveItem = (id: string | number | null, newParentId: number | string | null, newIndex: number | null = null) => {
        const item = findItemById(id);
        if (!item) return;

        // Remove from current location
        const currentParent = findParent(id);
        const currentList = currentParent ? currentParent.children! : items.value;
        const currentIndex = currentList.findIndex(i => (i.id || i._temp_id) == id);
        if (currentIndex > -1) {
            currentList.splice(currentIndex, 1);
        }

        // Add to new location
        let targetList: MenuItem[];
        if (newParentId) {
            const newParent = findItemById(newParentId);
            if (newParent) {
                targetList = newParent.children || (newParent.children = []);
            } else {
                targetList = items.value; // Fallback
            }
        } else {
            targetList = items.value;
        }

        if (newIndex !== null) {
            targetList.splice(newIndex, 0, item);
        } else {
            targetList.push(item);
        }

        takeSnapshot();
    };

    const selectItem = (id: string | number | null) => {
        selectedItemId.value = id;
    };

    const clearSelection = () => {
        selectedItemId.value = null;
    };

    // ==================== API INTEGRATION ====================

    const fetchMenu = async () => {
        if (!menuId?.value) return;

        isLoading.value = true;
        error.value = null;

        try {
            // Fetch menu details
            const menuResponse = await api.get(`/manage/layout/menus/${menuId?.value}`);
            menu.value = (parseSingleResponse(menuResponse) || {}) as Menu;

            // Fetch menu items (cache-buster: GET /items must not reuse a stale 200)
            const itemsResponse = await api.get(`/manage/layout/menus/${menuId?.value}/items`, {
                params: { _: Date.now() },
                headers: { 'Cache-Control': 'no-cache' },
            });
            const { data } = parseResponse(itemsResponse);
            applyFlatItems(ensureArray(data) as MenuItem[]);

        } catch (err) {
            logger.error('Failed to fetch menu:', err);
            error.value = err;
        } finally {
            isLoading.value = false;
        }
    };

    const saveMenu = async (menuData: Partial<Menu> = {}) => {
        if (!menuId?.value) return false;

        isSaving.value = true;
        error.value = null;

        try {
            // Snapshot the tree before any network call so in-flight reloads cannot
            // overwrite the payload the user just edited.
            const snapshot = deepClone(items.value);
            const syncRows = flattenForSync(snapshot);

            // Only send menu columns — spreading menu.value used to POST parent_items
            // and other GET leftovers that are ignored server-side but inflate the body.
            if (Object.keys(menuData).length > 0) {
                const menuResponse = await api.put(`/manage/layout/menus/${menuId?.value}`, {
                    name: menuData.name ?? menu.value?.name,
                    location: menuData.location ?? menu.value?.location ?? null,
                });
                const savedMenu = parseSingleResponse(menuResponse) as Menu | null;
                if (savedMenu) {
                    menu.value = savedMenu;
                }
            }

            const syncResponse = await api.post(
                `/manage/layout/menus/${menuId?.value}/items/sync`,
                { items: syncRows },
            );
            const { data } = parseResponse(syncResponse);
            applyFlatItems(ensureArray(data) as MenuItem[]);

            return true;
        } catch (err) {
            logger.error('Failed to save menu:', err);
            error.value = err;
            return false;
        } finally {
            isSaving.value = false;
        }
    };

    const MENU_ITEM_COLUMNS = new Set([
        'id',
        'menu_id',
        'parent_id',
        'title',
        'url',
        'type',
        'target_id',
        'target_type',
        'icon',
        'css_class',
        'sort_order',
        'open_in_new_tab',
        'metadata',
        'created_at',
        'updated_at',
        'deleted_at',
        'children',
        '_temp_id',
        'add_to_menu',
    ]);

    const hydrateMenuItem = (item: MenuItem): MenuItem => {
        const meta = item.metadata && typeof item.metadata === 'object' && !Array.isArray(item.metadata)
            ? { ...(item.metadata as Record<string, unknown>) }
            : {};
        return {
            ...meta,
            ...item,
            metadata: meta,
        };
    };

    const toPersistedPayload = (item: MenuItem, parentId: string | null, sortOrder: number): Record<string, unknown> => {
        const raw = toRaw(item);
        const meta: Record<string, unknown> = {
            ...(raw.metadata && typeof raw.metadata === 'object' && !Array.isArray(raw.metadata)
                ? { ...(raw.metadata as Record<string, unknown>) }
                : {}),
        };
        for (const [key, value] of Object.entries(raw)) {
            if (MENU_ITEM_COLUMNS.has(key) || value === undefined) continue;
            meta[key] = value;
        }
        const persistedId = raw.id && !String(raw.id).startsWith('temp_') ? String(raw.id) : null;
        const clientId = String(persistedId || raw._temp_id || generateId());
        return JSON.parse(JSON.stringify({
            id: persistedId,
            client_id: clientId,
            title: raw.title || '',
            url: raw.url ?? null,
            type: raw.type || 'custom',
            target_id: raw.target_id ?? null,
            target_type: raw.target_type ?? null,
            parent_id: parentId,
            icon: raw.icon ?? null,
            css_class: raw.css_class ?? null,
            sort_order: sortOrder,
            open_in_new_tab: Boolean(raw.open_in_new_tab),
            metadata: Object.keys(meta).length > 0 ? meta : null,
        }));
    };

    const flattenForSync = (itemsList: MenuItem[], parentClientId: string | null = null): Record<string, unknown>[] => {
        const rows: Record<string, unknown>[] = [];
        itemsList.forEach((item, index) => {
            const payload = toPersistedPayload(item, parentClientId, index);
            rows.push(payload);
            if (item.children && item.children.length > 0) {
                rows.push(...flattenForSync(item.children, String(payload.client_id)));
            }
        });
        return rows;
    };

    const applyFlatItems = (flatItems: MenuItem[]) => {
        items.value = buildTree(flatItems.map(hydrateMenuItem));
        markClean();
        history.value = [];
        historyIndex.value = -1;
        takeSnapshot();
    };

    const deleteItem = async (id: string | number | null) => {
        const item = findItemById(id);
        if (!item) return;

        // If item has a real ID, delete from server
        if (item.id && !String(item.id).startsWith('temp_')) {
            await api.delete(`/manage/layout/menus/${menuId?.value}/items/${item.id}`);
        }

        removeItem(id);
    };

    // ==================== WATCHERS ====================

    // Debounced snapshot on items change
    const snapshotTimeout = ref<ReturnType<typeof setTimeout> | null>(null);
    watch(items, () => {
        if (isUndoing.value) return;
        if (snapshotTimeout.value) clearTimeout(snapshotTimeout.value);
        snapshotTimeout.value = setTimeout(() => {
            takeSnapshot();
        }, 500);
    }, { deep: true });

    // Fetch menu when ID changes
    watch(() => menuId?.value, (newId) => {
        if (newId) fetchMenu();
    }, { immediate: true });

    // ==================== RETURN ====================
    return {
        // State
        menu,
        items,
        selectedItem,
        selectedItemId,
        isLoading,
        isSaving,
        error,
        isDirty,

        // History
        canUndo,
        canRedo,
        undo,
        redo,
        takeSnapshot,

        // Clipboard
        clipboard,
        canPaste,
        copyItem,
        cutItem,
        pasteItem,

        // Item Helpers
        findItemById,
        findParent,
        buildTree,
        flattenTree,

        // Item Actions
        addItem,
        removeItem,
        updateItem,
        duplicateItem,
        moveItem,
        selectItem,
        clearSelection,

        // API
        fetchMenu,
        saveMenu,
        deleteItem,
        markClean,

        // Frontend / Shared
        menus,
        fetchMenuByLocation,
        fetchMenuByIdentifier
    } as MenuContext;
}

/**
 * Provide menu context for child components
 */
export function provideMenu(menuState: MenuContext) {
    provide(MENU_CONTEXT_KEY, menuState);
}

/**
 * Inject menu context in child components
 */
export function useMenuContext(): MenuContext {
    const context = inject(MENU_CONTEXT_KEY);
    if (!context) {
        throw new Error('useMenuContext must be used within a MenuBuilder that provides the context');
    }
    return context;
}

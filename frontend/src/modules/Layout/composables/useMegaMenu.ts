import type { MenuItem } from '@/modules/Layout/types/menu';

export interface MegaColumn {
    heading: string | null;
    items: MenuItem[];
}

export function useMegaMenu() {
    /**
     * Determine if a menu item should render as a mega menu.
     */
    const isMegaMenu = (item: MenuItem | null | undefined): boolean => {
        if (!item) return false;
        const layout = item.mega_menu_layout || (item.metadata as Record<string, unknown> | undefined)?.mega_menu_layout;
        const isMega = item.is_mega_menu ?? (item.metadata as Record<string, unknown> | undefined)?.is_mega_menu;
        return Boolean(isMega) || Boolean(layout && layout !== 'default');
    };

    /**
     * Get the resolved mega menu layout key.
     */
    const getMegaLayout = (item: MenuItem): string => {
        const layout = String(item.mega_menu_layout || (item.metadata as Record<string, unknown> | undefined)?.mega_menu_layout || 'grid-3');
        if (layout === 'default') return 'grid-3';
        return layout;
    };

    /**
     * Determine mega menu container width class for desktop dropdown.
     */
    const getMegaWidthClass = (item: MenuItem): string => {
        const layout = getMegaLayout(item);
        switch (layout) {
            case 'grid-2':
                return 'w-[540px]';
            case 'grid-3':
                return 'w-[760px]';
            case 'grid-4':
                return 'w-[940px] max-w-[92vw]';
            case 'full':
                return 'w-[1024px] max-w-[95vw]';
            default:
                return 'w-[720px]';
        }
    };

    /**
     * Determine CSS grid layout for the columns container.
     */
    const getMegaGridClass = (item: MenuItem): string => {
        const layout = getMegaLayout(item);
        switch (layout) {
            case 'grid-2':
                return 'grid grid-cols-2 gap-4';
            case 'grid-3':
                return 'grid grid-cols-3 gap-5';
            case 'grid-4':
                return 'grid grid-cols-4 gap-4';
            case 'full':
                return 'grid grid-cols-4 gap-6';
            default:
                return 'grid grid-cols-3 gap-4';
        }
    };

    /**
     * Check if divider lines between columns should be visible.
     */
    const showDividers = (item: MenuItem): boolean => {
        const val = item.mega_menu_show_dividers ?? (item.metadata as Record<string, unknown> | undefined)?.mega_menu_show_dividers;
        return Boolean(val);
    };

    /**
     * Partition child items into structured columns.
     * Supports:
     * 1. Nested 'column_group' items as dedicated columns with optional headings
     * 2. Items with explicit `mega_menu_column` numbers (1, 2, 3, 4)
     * 3. Automatic distribution across columns based on layout
     */
    const getMegaColumns = (item: MenuItem): MegaColumn[] => {
        const children = item.children || [];
        if (children.length === 0) return [];

        // 1. Column groups
        const hasColumnGroups = children.some(c => c.type === 'column_group');
        if (hasColumnGroups) {
            return children.map(c => ({
                heading: c.hide_label ? null : (c.heading || c.title || null),
                items: c.children && c.children.length > 0 ? c.children : [c]
            }));
        }

        // 2. Explicit column numbers
        const hasExplicitColumns = children.some(c => {
            const col = Number(c.mega_menu_column || (c.metadata as Record<string, unknown> | undefined)?.mega_menu_column || 0);
            return col > 0;
        });

        if (hasExplicitColumns) {
            const colMap: Record<number, MenuItem[]> = {};
            children.forEach(c => {
                const col = Number(c.mega_menu_column || (c.metadata as Record<string, unknown> | undefined)?.mega_menu_column || 1);
                if (!colMap[col]) colMap[col] = [];
                colMap[col].push(c);
            });
            return Object.keys(colMap)
                .sort((a, b) => Number(a) - Number(b))
                .map(k => ({
                    heading: null,
                    items: colMap[Number(k)] ?? []
                }));
        }

        // 3. Fallback: Distribute evenly based on layout column count
        const layout = getMegaLayout(item);
        const numCols = layout === 'grid-4' || layout === 'full' ? 4 : (layout === 'grid-3' ? 3 : 2);
        const cols: MegaColumn[] = Array.from({ length: numCols }, () => ({ heading: null, items: [] }));
        
        children.forEach((child, idx) => {
            const targetCol = cols[idx % numCols];
            if (targetCol) {
                targetCol.items.push(child);
            }
        });

        return cols.filter(col => col.items.length > 0);
    };

    /**
     * Helper to retrieve badge text and color from an item.
     */
    const getMenuItemBadge = (item: MenuItem): { text: string; color: string } | null => {
        const text = String(item.badge || (item.metadata as Record<string, unknown> | undefined)?.badge || '').trim();
        if (!text) return null;
        const color = String(item.badge_color || (item.metadata as Record<string, unknown> | undefined)?.badge_color || 'default').trim();
        return { text, color };
    };

    return {
        isMegaMenu,
        getMegaLayout,
        getMegaWidthClass,
        getMegaGridClass,
        showDividers,
        getMegaColumns,
        getMenuItemBadge,
    };
}

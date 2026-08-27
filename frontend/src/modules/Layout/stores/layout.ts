import { defineStore } from 'pinia';
import { logger } from '@/shared/utils/logger';
import { ensureArray } from '@/shared/utils/responseParser';
import { LayoutService } from '@/modules/Layout/services/layoutService';

export interface MenuItem {
    id: string;
    label: string;
    url: string;
    parent_id?: string;
    order: number;
}

export interface LayoutState {
    menus: Record<string, MenuItem[]>;
    menuList: any[];
    widgets: Record<string, any[]>;
    loading: boolean;
    trashedCount: number;
}

export const useLayoutStore = defineStore('layout', {
    state: (): LayoutState => ({
        menus: {},
        menuList: [],
        widgets: {},
        loading: false,
        trashedCount: 0,
    }),

    actions: {
        async fetchAllMenus(params: Record<string, any> = {}) {
            this.loading = true;
            try {
                const response = await LayoutService.listMenus(params);
                this.menuList = ensureArray(response.data?.data || response.data);
                this.trashedCount = response.data?.meta?.trashed_count ?? 0;
                return this.menuList;
            } catch (error) {
                logger.error('[Layout Store] Error fetching menu list:', error);
                return [];
            } finally {
                this.loading = false;
            }
        },

        async fetchMenu(location: string, module: string = 'Jejakawan') {
            this.loading = true;
            try {
                const response = await LayoutService.publicMenuByLocation(location, module);
                this.menus[location] = ensureArray(response.data);
                return this.menus[location];
            } catch (error) {
                logger.error(`[Layout Store] Error fetching menu ${location}:`, error);
                return [];
            } finally {
                this.loading = false;
            }
        },

        async deleteMenu(id: string, force = false) {
            this.loading = true;
            try {
                await LayoutService.deleteMenu(id, force);
                return true;
            } catch (error) {
                logger.error('[Layout Store] Error deleting menu:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async restoreMenu(id: string) {
            this.loading = true;
            try {
                await LayoutService.restoreMenu(id);
                return true;
            } catch (error) {
                logger.error('[Layout Store] Error restoring menu:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async fetchWidgets(location: string, module: string = 'publishing') {
            this.loading = true;
            try {
                const response = await LayoutService.publicWidgetsByLocation(location, module);
                this.widgets[location] = ensureArray(response.data);
                return this.widgets[location];
            } catch (error) {
                logger.error(`[Layout Store] Error fetching widgets ${location}:`, error);
                return [];
            } finally {
                this.loading = false;
            }
        }
    }
});

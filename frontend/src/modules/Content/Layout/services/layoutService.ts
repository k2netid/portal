import api from '@/engine/api/client';
import { layoutPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export const LayoutService = {
    listMenus(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(layoutPaths.menus, { params });
    },

    createMenu(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(layoutPaths.menus, payload);
    },

    getMenu(id: string): Promise<AxiosResponse> {
        return api.get(layoutPaths.menu(id));
    },

    getMenuUsage(id: string): Promise<AxiosResponse> {
        return api.get(layoutPaths.menuUsage(id));
    },

    deleteMenu(id: string, force = false): Promise<AxiosResponse> {
        const url = force ? layoutPaths.menuForceDelete(id) : layoutPaths.menu(id);
        return api.delete(url);
    },

    restoreMenu(id: string): Promise<AxiosResponse> {
        return api.post(layoutPaths.menuRestore(id));
    },

    publicMenuByLocation(location: string, module = 'Jejakawan'): Promise<AxiosResponse> {
        return api.get(layoutPaths.publicMenuByLocation(location), { params: { module } });
    },

    listWidgets(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(layoutPaths.widgets, { params });
    },

    updateWidget(id: string, payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.put(layoutPaths.widget(id), payload);
    },

    deleteWidget(id: string): Promise<AxiosResponse> {
        return api.delete(layoutPaths.widget(id));
    },

    publicWidgetsByLocation(location: string, module = 'Jejakawan'): Promise<AxiosResponse> {
        return api.get(layoutPaths.publicWidgetsByLocation(location), { params: { module } });
    },

    themeLocations(): Promise<AxiosResponse> {
        return api.get(layoutPaths.themeLocations);
    },

    publicThemeActive(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(layoutPaths.publicThemeActive, { params });
    },
};

export default LayoutService;

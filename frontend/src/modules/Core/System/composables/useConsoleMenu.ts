import { ref, computed } from 'vue';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';

export interface ConsoleMenuItem {
    id: string;
    parent_id?: string | null;
    group_slug: string;
    name: string;
    label_key?: string | null;
    route_name?: string | null;
    url?: string | null;
    icon?: string | null;
    permission?: string | null;
    role?: string | null;
    extension_slug?: string | null;
    badge_text?: string | null;
    badge_variant?: 'default' | 'primary' | 'amber' | 'emerald' | 'rose';
    order: number;
    is_visible: boolean;
    meta?: Record<string, any> | null;
    children?: ConsoleMenuItem[];
}

export function useConsoleMenu() {
    const toast = useToast();
    const menus = ref<ConsoleMenuItem[]>([]);
    const loading = ref(false);
    const saving = ref(false);
    const selectedGroup = ref<string>('all');

    const availableGroups = [
        { slug: 'all', name: 'All Groups', icon: 'layers' },
        { slug: 'identity', name: 'Identity & Access', icon: 'users' },
        { slug: 'observability', name: 'Observability & Journals', icon: 'book-open' },
        { slug: 'system_config', name: 'System Config', icon: 'sliders' },
        { slug: 'infrastructure', name: 'Infrastructure', icon: 'cpu' },
        { slug: 'content', name: 'Content & Publishing', icon: 'file-text' },
        { slug: 'extensions', name: 'Extensions & Plugins', icon: 'box' },
    ];

    const fetchMenus = async (group?: string) => {
        loading.value = true;
        try {
            const params: Record<string, string> = {};
            if (group && group !== 'all') {
                params.group = group;
            }
            const res = await api.get('/manage/console-menus', { params });
            const data = res.data?.data || res.data;
            if (Array.isArray(data)) {
                menus.value = data;
            }
        } catch (error: unknown) {
            toast.error.fromResponse(error);
        } finally {
            loading.value = false;
        }
    };

    const saveMenu = async (payload: Partial<ConsoleMenuItem>, id?: string) => {
        saving.value = true;
        try {
            if (id) {
                await api.put(`/manage/console-menus/${id}`, payload);
                toast.success.action('Menu item updated successfully');
            } else {
                await api.post('/manage/console-menus', payload);
                toast.success.action('Menu item created successfully');
            }
            await fetchMenus(selectedGroup.value);
            return true;
        } catch (error: unknown) {
            toast.error.fromResponse(error);
            return false;
        } finally {
            saving.value = false;
        }
    };

    const deleteMenu = async (id: string) => {
        saving.value = true;
        try {
            await api.delete(`/manage/console-menus/${id}`);
            toast.success.action('Menu item deleted');
            await fetchMenus(selectedGroup.value);
            return true;
        } catch (error: unknown) {
            toast.error.fromResponse(error);
            return false;
        } finally {
            saving.value = false;
        }
    };

    const reorderMenus = async (items: Array<{ id: string; parent_id?: string | null; order: number; group_slug?: string }>) => {
        try {
            const res = await api.post('/manage/console-menus/reorder', { items });
            const data = res.data?.data || res.data;
            if (Array.isArray(data)) {
                menus.value = data;
            }
            toast.success.action('Menu order saved successfully');
            return true;
        } catch (error: unknown) {
            toast.error.fromResponse(error);
            return false;
        }
    };

    const resetDefaults = async () => {
        saving.value = true;
        try {
            const res = await api.post('/manage/console-menus/reset');
            const data = res.data?.data || res.data;
            if (Array.isArray(data)) {
                menus.value = data;
            }
            toast.success.action('Navigation reset to system factory defaults');
            return true;
        } catch (error: unknown) {
            toast.error.fromResponse(error);
            return false;
        } finally {
            saving.value = false;
        }
    };

    const filteredMenus = computed(() => {
        if (selectedGroup.value === 'all') return menus.value;
        return menus.value.filter(m => m.group_slug === selectedGroup.value);
    });

    return {
        menus,
        filteredMenus,
        loading,
        saving,
        selectedGroup,
        availableGroups,
        fetchMenus,
        saveMenu,
        deleteMenu,
        reorderMenus,
        resetDefaults,
    };
}

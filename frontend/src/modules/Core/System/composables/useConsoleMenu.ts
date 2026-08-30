import { ref, computed, unref, type MaybeRef } from 'vue';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useNavigationStore } from '@/shared/stores/navigation';

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
    meta?: Record<string, unknown> | null;
    children?: ConsoleMenuItem[];
}

export function useConsoleMenu() {
    const toast = useToast();
    const navStore = useNavigationStore();
    const menus = ref<ConsoleMenuItem[]>([]);
    const loading = ref(false);
    const saving = ref(false);
    const selectedGroup = ref<string>('all');

    const catalogGroups = ref<Array<{ slug: string; name: string; icon: string }>>([
        { slug: 'all', name: 'All Groups', icon: 'layers' },
        { slug: 'studio', name: 'Data Model Studio', icon: 'layers' },
        { slug: 'editorial', name: 'Editorial', icon: 'file-text' },
        { slug: 'insight', name: 'Insight', icon: 'bar-chart' },
        { slug: 'library', name: 'Library', icon: 'bookmark' },
        { slug: 'audience', name: 'Audience', icon: 'users' },
        { slug: 'identity', name: 'Users & Access', icon: 'users' },
        { slug: 'communications', name: 'Communications', icon: 'mail' },
        { slug: 'observability', name: 'Journals', icon: 'book-open' },
        { slug: 'system_config', name: 'Configuration', icon: 'sliders' },
        { slug: 'infrastructure', name: 'Infrastructure', icon: 'cpu' },
        { slug: 'integrations_dev', name: 'Identity & Integrations', icon: 'code' },
    ]);

    const extractGroupList = (payload: unknown): unknown[] => {
        if (Array.isArray(payload)) {
            return payload;
        }
        if (payload && typeof payload === 'object') {
            const inner = (payload as { data?: unknown }).data;
            if (Array.isArray(inner)) {
                return inner;
            }
            return Object.values(payload as Record<string, unknown>);
        }
        return [];
    };

    const normalizeGroups = (payload: unknown): Array<{ slug: string; name: string; icon: string }> => {
        return extractGroupList(payload).flatMap((entry) => {
            if (!entry || typeof entry !== 'object') {
                return [];
            }
            const row = entry as { slug?: unknown; name?: unknown; icon?: unknown };
            if (typeof row.slug !== 'string' || row.slug === '') {
                return [];
            }
            return [{
                slug: row.slug,
                name: typeof row.name === 'string' && row.name !== '' ? row.name : row.slug,
                icon: typeof row.icon === 'string' && row.icon !== '' ? row.icon : 'folder',
            }];
        });
    };

    /** Plain ref (not computed-of-ref) so v-for never iterates a ComputedRef object. */
    const availableGroups = catalogGroups;

    const filterGroups = (source: MaybeRef<Array<{ slug: string; name: string; icon: string }>>, query: string) => {
        const raw = unref(source);
        const asList = Array.isArray(raw)
            ? raw
            : (raw && typeof raw === 'object' ? Object.values(raw as Record<string, { slug: string; name: string; icon: string }>) : []);
        const list = asList.filter((g) => g && typeof g.slug === 'string');
        const needle = query.trim().toLowerCase();
        if (!needle) {
            return list;
        }
        return list.filter((g) => (
            g.slug.toLowerCase().includes(needle)
            || (g.name || '').toLowerCase().includes(needle)
        ));
    };

    const fetchGroups = async () => {
        try {
            const res = await api.get('/manage/console-menus/groups');
            const normalized = normalizeGroups(res.data?.data ?? res.data);
            if (normalized.length > 0) {
                catalogGroups.value = normalized;
            }
        } catch {
            /* keep catalog fallback */
        }
    };

    const fetchMenus = async (group?: string) => {
        loading.value = true;
        await fetchGroups();
        try {
            const params: Record<string, string> = {};
            if (group && group !== 'all') {
                params.group = group;
            }
            const res = await api.get('/manage/console-menus', { params });
            const data = res.data?.data || res.data;
            if (Array.isArray(data)) {
                menus.value = data;
                if (!group || group === 'all') {
                    navStore.setDatabaseMenus(data);
                }
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
                navStore.setDatabaseMenus(data);
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
                navStore.setDatabaseMenus(data);
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
        filterGroups,
        fetchGroups,
        fetchMenus,
        saveMenu,
        deleteMenu,
        reorderMenus,
        resetDefaults,
    };
}

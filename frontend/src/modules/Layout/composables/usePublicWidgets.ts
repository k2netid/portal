import { onMounted, ref, unref, type MaybeRef } from 'vue';
import api from '@/engine/api/client';

export interface PublicWidgetItem {
    id: string;
    title?: string;
    name?: string;
    slug?: string;
    excerpt?: string | null;
}

export interface PublicWidget {
    id: string;
    title: string;
    type: string;
    location: string;
    content?: string;
    items?: PublicWidgetItem[];
}

const unwrapList = (payload: unknown): PublicWidget[] => {
    if (Array.isArray(payload)) {
        return payload as PublicWidget[];
    }
    if (payload && typeof payload === 'object' && Array.isArray((payload as { data?: unknown }).data)) {
        return (payload as { data: PublicWidget[] }).data;
    }
    return [];
};

export const usePublicWidgets = (location: MaybeRef<string>) => {
    const widgets = ref<PublicWidget[]>([]);
    const loading = ref(false);

    const load = async (): Promise<void> => {
        loading.value = true;
        try {
            const res = await api.get(`/public/layout/widgets/location/${unref(location)}`, {
                params: { module_scope: 'publishing' },
            });
            widgets.value = unwrapList(res.data);
        } catch {
            widgets.value = [];
        } finally {
            loading.value = false;
        }
    };

    onMounted(() => {
        void load();
    });

    return { widgets, loading, load };
};

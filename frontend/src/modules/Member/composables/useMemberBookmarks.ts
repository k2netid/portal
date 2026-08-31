import { ref, type Ref } from 'vue';
import api from '@/engine/api/client';

export interface MemberBookmarkRow {
    id: string;
    content?: { title?: string; slug?: string };
}

interface PaginatedBookmarks {
    data?: MemberBookmarkRow[];
}

export function extractBookmarkRows(payload: unknown): MemberBookmarkRow[] {
    if (Array.isArray(payload)) {
        return payload;
    }
    if (payload && typeof payload === 'object' && 'data' in payload) {
        const inner = (payload as PaginatedBookmarks).data;
        return Array.isArray(inner) ? inner : [];
    }
    return [];
}

export function useMemberBookmarks(limit?: number): {
    bookmarks: Ref<MemberBookmarkRow[]>;
    loading: Ref<boolean>;
    load: () => Promise<void>;
    removeBookmark: (id: string) => Promise<void>;
} {
    const bookmarks = ref<MemberBookmarkRow[]>([]);
    const loading = ref(false);

    const load = async (): Promise<void> => {
        loading.value = true;
        try {
            const params = limit ? { per_page: limit } : undefined;
            const response = await api.get('/member/bookmarks', { params });
            bookmarks.value = extractBookmarkRows(response.data);
        } catch {
            bookmarks.value = [];
        } finally {
            loading.value = false;
        }
    };

    const removeBookmark = async (id: string): Promise<void> => {
        await api.delete(`/member/bookmarks/${id}`);
        bookmarks.value = bookmarks.value.filter((row) => row.id !== id);
    };

    return { bookmarks, loading, load, removeBookmark };
}

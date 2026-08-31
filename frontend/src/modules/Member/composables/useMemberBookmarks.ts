import { ref, type Ref } from 'vue';
import api from '@/engine/api/client';
import { extractPaginatedRows } from '@/modules/Member/utils/memberApi';

export interface MemberBookmarkRow {
    id: string;
    content?: { title?: string; slug?: string };
}

export function extractBookmarkRows(payload: unknown): MemberBookmarkRow[] {
    if (Array.isArray(payload)) {
        return payload;
    }
    if (payload && typeof payload === 'object' && 'data' in payload) {
        const inner = (payload as { data?: unknown }).data;
        if (Array.isArray(inner)) {
            return inner;
        }
        if (inner && typeof inner === 'object' && 'data' in inner) {
            const rows = (inner as { data?: MemberBookmarkRow[] }).data;
            return Array.isArray(rows) ? rows : [];
        }
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
            bookmarks.value = extractPaginatedRows<MemberBookmarkRow>(response);
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

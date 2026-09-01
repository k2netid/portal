import api from '@/engine/api/client';
import { memberPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export interface MemberDirectoryRow {
    id: string;
    name: string | null;
    email: string;
    phone: string | null;
    status: 'active' | 'inactive' | string;
    email_verified_at: string | null;
    last_login_at: string | null;
    created_at: string | null;
    deleted_at: string | null;
}

export interface MemberDirectoryDetail extends MemberDirectoryRow {
    avatar?: string | null;
    bio?: string | null;
    locale?: string | null;
    timezone?: string | null;
    pending_email?: string | null;
    activity?: {
        bookmarks: number;
        comments: number;
        submissions: number;
        newsletter_subscribed: boolean | null;
    };
}

export interface MemberDirectoryStats {
    total: number;
    verified: number;
    unverified: number;
    active_status: number;
    inactive_status: number;
    recent: number;
    active: number;
    trashed: number;
}

export const MemberDirectoryService = {
    stats(): Promise<AxiosResponse> {
        return api.get(memberPaths.stats);
    },

    create(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(memberPaths.create, payload);
    },

    list(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(memberPaths.index, { params });
    },

    show(id: string): Promise<AxiosResponse> {
        return api.get(memberPaths.show(id));
    },

    update(id: string, payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.patch(memberPaths.update(id), payload);
    },

    destroy(id: string): Promise<AxiosResponse> {
        return api.delete(memberPaths.destroy(id));
    },

    restore(id: string): Promise<AxiosResponse> {
        return api.post(memberPaths.restore(id));
    },

    forceDelete(id: string): Promise<AxiosResponse> {
        return api.delete(memberPaths.forceDelete(id));
    },

    securityEvents(id: string, params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(memberPaths.securityEvents(id), { params });
    },

    bulkAction(payload: { ids: string[]; action: string }): Promise<AxiosResponse> {
        return api.post(memberPaths.bulkAction, payload);
    },

    export(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(memberPaths.export, { params, responseType: 'blob' });
    },
};

export default MemberDirectoryService;

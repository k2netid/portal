import type { User } from '@/engine/types/auth';

export type CommentStatus = 'pending' | 'approved' | 'rejected' | 'spam';

export interface Comment {
    id: string;
    parent_id?: string | null;
    content_id: string;
    user_id?: string | null;
    name?: string | null;
    email?: string | null;
    body: string;
    status: CommentStatus;
    ip_address?: string;
    user_agent?: string;
    created_at: string;
    updated_at: string;

    // Relations
    user?: User | null;
    content?: {
        id: string;
        title: string;
        slug: string;
    };
    replies?: Comment[];
    replies_count?: number;
    parent?: Comment | null;
}

export interface CommentStatistics {
    total: number;
    pending: number;
    approved: number;
    rejected: number;
    spam: number;
}

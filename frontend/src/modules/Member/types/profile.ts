/** Public reader profile returned by /member/me and PATCH /member/profile */
export interface PublicMember {
    id: string;
    name: string;
    email: string;
    phone?: string | null;
    avatar?: string | null;
    bio?: string | null;
    locale?: string | null;
    timezone?: string | null;
    status: string;
    email_verified?: boolean;
    pending_email?: string | null;
    last_login_at?: string | null;
    created_at?: string | null;
}

export interface MemberProfileInput {
    name: string;
    phone?: string | null;
    avatar?: string | null;
    bio?: string | null;
    locale?: string | null;
    timezone?: string | null;
}

export const MEMBER_TIMEZONE_OPTIONS = [
    'Asia/Jakarta',
    'Asia/Makassar',
    'Asia/Jayapura',
    'Asia/Singapore',
    'UTC',
] as const;

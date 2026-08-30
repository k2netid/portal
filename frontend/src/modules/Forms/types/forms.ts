import type { User } from '@/engine/types/auth';


/**
 * Standard form field interface used for traditional forms.
 */
export interface FormField {
    id?: string;
    name: string;
    type: string;
    label: string;
    description?: string;
    placeholder?: string;
    default_value?: unknown;
    options?: (string | number | Record<string, unknown>)[];
    validation_rules?: string | Record<string, unknown>;
    is_required?: boolean;
    help_text?: string;
    order?: number;
    settings?: Record<string, unknown>;
}

export interface Form {
    id: string;
    name: string;
    slug: string;
    description?: string | null;
    success_message?: string | null;
    redirect_url?: string | null;
    is_active: boolean;
    fields?: FormField[];
    fields_count?: number;
    submission_count?: number;
    view_count?: number;
    start_count?: number;
    settings?: Record<string, unknown>;
    deleted_at?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface FormSubmission {
    id: string;
    form_id: string;
    data: Record<string, unknown>;
    ip_address?: string;
    user_agent?: string;
    status: 'new' | 'read' | 'archived';
    user_id?: string | null;
    user?: User | null;
    created_at: string;
    updated_at: string;
}

export interface FormStatistics {
    total: number;
    new: number;
    read: number;
    archived: number;
}

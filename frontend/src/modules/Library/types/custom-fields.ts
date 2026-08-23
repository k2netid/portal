export interface FieldGroup {
    id: string;
    name: string;
    description?: string | null;
    fields_count?: number;
    attachable_type?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface CustomField {
    id: string;
    field_group_id: string;
    label: string;
    name: string;
    type: string;
    description?: string | null;
    placeholder?: string | null;
    default_value?: unknown;
    options?: (string | Record<string, unknown>)[];
    validation_rules?: string | Record<string, unknown>;
    is_required?: boolean;
    order?: number;
    settings?: Record<string, unknown>;
    created_at?: string;
    updated_at?: string;

    // Relations
    field_group?: FieldGroup;
}

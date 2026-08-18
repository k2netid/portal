export interface RedirectRow {
    id: string;
    from_url: string;
    to_url: string;
    status_code: number;
    hits: number;
    is_active: boolean;
}

type ApiRedirect = {
    id?: string;
    source_path?: string;
    target_path?: string;
    from_url?: string;
    to_url?: string;
    status_code?: number;
    hits?: number;
    is_active?: boolean;
};

export function fromApiRedirect(raw: ApiRedirect): RedirectRow {
    return {
        id: String(raw.id ?? ''),
        from_url: String(raw.source_path ?? raw.from_url ?? ''),
        to_url: String(raw.target_path ?? raw.to_url ?? ''),
        status_code: Number(raw.status_code ?? 301),
        hits: Number(raw.hits ?? 0),
        is_active: raw.is_active !== undefined ? Boolean(raw.is_active) : true,
    };
}

export function toApiRedirectPayload(form: {
    from_url: string;
    to_url: string;
    status_code: number;
    is_active: boolean;
}) {
    return {
        source_path: form.from_url.trim(),
        target_path: form.to_url.trim(),
        status_code: form.status_code,
        is_active: form.is_active,
    };
}

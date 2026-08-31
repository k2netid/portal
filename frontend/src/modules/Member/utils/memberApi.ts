/**
 * Normalizes Laravel API envelopes ({ success, data }) and paginated payloads.
 */
export function unwrapApiPayload<T>(response: { data?: unknown }): T {
    const root = response.data;
    if (root && typeof root === 'object' && 'data' in root) {
        return (root as { data: T }).data;
    }

    return root as T;
}

export function extractPaginatedRows<T>(response: { data?: unknown }): T[] {
    const payload = unwrapApiPayload<{ data?: T[] } | T[]>(response);
    if (Array.isArray(payload)) {
        return payload;
    }
    if (payload && typeof payload === 'object' && Array.isArray(payload.data)) {
        return payload.data;
    }

    return [];
}

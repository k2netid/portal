import type { AxiosResponse } from 'axios';

export interface PaginationData {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page?: number;
}

/**
 * Standard API Response structure from BaseApiController
 */
export interface BaseApiResponse<T> {
    success: boolean;
    message?: string;
    data: T;
}

/**
 * Parsed structure for frontend consume
 */
export interface ParsedResponse<T> {
    data: T[];
    pagination: PaginationData | null;
}

/**
 * Parse API response data
 * Handles BaseApiController response structure for lists/collections
 */
export function parseResponse<T>(response: AxiosResponse<unknown>): ParsedResponse<T> {
    let data: T[] = [];
    let pagination: PaginationData | null = null;

    const responseData = response.data;
    if (!responseData) return { data: [], pagination: null };

    // Helper to extract pagination from an object
    const extractPagination = (obj: unknown): PaginationData | null => {
        if (!obj || typeof obj !== 'object') return null;
        const data = obj as Record<string, unknown>;
        if (data.pagination) return data.pagination as PaginationData;
        if ((data.meta as Record<string, unknown>)?.pagination) return (data.meta as Record<string, unknown>).pagination as PaginationData;
        if (typeof data.current_page === 'number') {
            return {
                current_page: data.current_page,
                last_page: data.last_page as number,
                from: data.from as number,
                to: data.to as number,
                total: data.total as number,
                per_page: data.per_page as number
            };
        }
        return null;
    };

    const responseDataObj = responseData as Record<string, unknown>;

    // Case 1: Paginated data (already unpacked by interceptor, so it has .data array and pagination info)
    if (responseDataObj && Array.isArray(responseDataObj.data)) {
        data = responseDataObj.data as T[];
        pagination = extractPagination(responseDataObj);
    }
    // Case 2: Direct array (already unpacked)
    else if (Array.isArray(responseDataObj)) {
        data = responseDataObj as unknown as T[];
    }
    // Case 3: Items property (some endpoints use this)
    else if (responseDataObj && Array.isArray(responseDataObj.items)) {
        data = responseDataObj.items as T[];
        pagination = extractPagination(responseDataObj);
    }
    // Case 4: Legacy/Wrapped (if interceptor missed it or it's a different structure)
    else if (responseDataObj && responseDataObj.data) {
        const inner = responseDataObj.data as Record<string, unknown>;
        if (Array.isArray(inner)) {
            data = inner as T[];
            pagination = extractPagination(responseDataObj);
        } else if (inner && Array.isArray(inner.data)) {
            data = inner.data as T[];
            pagination = extractPagination(inner) ?? extractPagination(responseDataObj);
        } else if (inner && typeof inner.data === 'object' && inner.data !== null && Array.isArray((inner.data as Record<string, unknown>).data)) {
            const nested = inner.data as Record<string, unknown>;
            data = nested.data as T[];
            pagination = extractPagination(nested) ?? extractPagination(inner);
        } else {
            data = [inner as T];
        }
    }

    return { data, pagination };
}

/**
 * Parse single object response
 */
export function parseSingleResponse<T>(response: AxiosResponse<unknown>): T | null {
    if (!response.data || typeof response.data !== 'object') return null;

    const data = response.data as Record<string, unknown>;

    // Check if it's wrapped in a 'data' property
    if (Object.prototype.hasOwnProperty.call(data, 'data')) {
        return data.data as T;
    }

    return data as unknown as T;
}

/**
 * Parse pagination info from response
 */
export function parsePagination(response: AxiosResponse<unknown>): PaginationData | null {
    const d = response.data as Record<string, unknown>;
    if (!d || typeof d !== 'object') return null;

    if ((d.data as Record<string, unknown>)?.pagination) return (d.data as Record<string, unknown>).pagination as PaginationData;
    if (d.pagination) return d.pagination as PaginationData;

    if (typeof d.current_page === 'number') {
        return {
            current_page: d.current_page,
            last_page: d.last_page as number,
            from: d.from as number,
            to: d.to as number,
            total: d.total as number,
        };
    }
    return null;
}

/**
 * Ensure value is an array
 */
export function ensureArray<T>(value: unknown): T[] {
    if (Array.isArray(value)) {
        return value as T[];
    }
    return [];
}

/**
 * Get list data from unwrapped API response payload.
 * Supports: [] | { data: [] } | { items: [] }
 */
export function getResponseList<T>(payload: unknown): T[] {
    if (!payload || typeof payload !== 'object') {
        return Array.isArray(payload) ? payload as T[] : [];
    }

    const obj = payload as Record<string, any>;
    
    // 1. If payload is directly the array
    if (Array.isArray(obj)) return obj as T[];

    // 2. If it's wrapped in { data: [...] }
    if (Array.isArray(obj.data)) return obj.data as T[];

    // 3. If it's paginated { data: { data: [...] } }
    if (obj.data && Array.isArray(obj.data.data)) return obj.data.data as T[];

    // 4. If it's wrapped in { items: [...] }
    if (Array.isArray(obj.items)) return obj.items as T[];

    return [];
}

/**
 * Get single object from unwrapped API response payload.
 * Supports: { ...object } | { data: object }
 */
export function getResponseObject<T>(payload: unknown): T | null {
    if (!payload || typeof payload !== 'object') {
        return null;
    }

    const obj = payload as Record<string, unknown>;
    if (obj.data && typeof obj.data === 'object' && !Array.isArray(obj.data)) {
        return obj.data as T;
    }

    return obj as unknown as T;
}

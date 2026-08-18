export interface PaginationData {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page?: number;
}

export interface BaseApiResponse<T> {
    success: boolean;
    message?: string;
    data: T;
}

// Common shared types can be added here.
// Module-specific types should be imported directly from @/modules/[Module]/types

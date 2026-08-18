export interface Permission {
    id: string;
    name: string;
    description?: string;
}

export interface Role {
    id: string;
    name: string;
    permissions?: Permission[];
    users_count?: number;
}

export interface User {
    id: string;
    name: string;
    email: string;
    avatar?: string | { url?: string; path?: string } | null;
    roles?: Role[];
    permissions?: Permission[];
    email_verified_at?: string | null;
    last_login_at?: string | null;
    deleted_at?: string | null;
    phone?: string | null;
    bio?: string | null;
    website?: string | null;
    location?: string | null;
    created_at?: string;
    updated_at?: string;
    levels?: Array<{ id: string; name: string; level: string }>;
}

export interface AuthState {
    user: User | null;
    isAuthenticated: boolean;
}

export interface LoginCredentials {
    email: string;
    password: string;
    remember?: boolean;
}

export interface AuthResponse {
    success: boolean;
    data?: {
        user: User;
        token?: string;
        redirect_to?: string;
    };
    message?: string;
    errors?: Record<string, string[]>;
    requiresTwoFactor?: boolean;
    requiresVerification?: boolean;
    rateLimited?: boolean;
    retryAfter?: number;
    userId?: string;
}

export interface RegisterData {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
}

export interface ResetPasswordData {
    email: string;
    token: string;
    password: string;
    password_confirmation: string;
}

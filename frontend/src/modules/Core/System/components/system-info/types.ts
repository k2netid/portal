export interface DiskUsage {
    used: string;
    total: string;
    percent?: number;
}

export interface SystemInfo {
    uptime: number;
    php_version: string;
    laravel_version: string;
    environment: string;
    debug_mode: boolean;
    server_software: string;
    os_distro?: string;
    os_kernel?: string;
    php_sapi?: string;
    database_version?: string;
    memory_usage: string;
    memory_usage_percent: number;
    disk_usage: DiskUsage | string;
    disk_usage_percent: number;
    database: string;
    cache_driver?: string;
    session_driver?: string;
}

export interface CacheData {
    status: string;
}

export interface RequirementFixGuide {
    ubuntu: string;
    rhel: string;
    general: string;
}

export interface RequirementItem {
    id: string;
    name: string;
    category: string;
    required: boolean;
    current_value: string;
    required_value: string;
    status: 'ok' | 'warning' | 'error';
    description: string;
    fix_guide: RequirementFixGuide;
    can_autofix: boolean;
}

export interface ServerSpec {
    distro: string;
    kernel: string;
    php_version: string;
    php_sapi: string;
    web_server: string;
    database_engine: string;
    database_version: string;
    redis_version: string;
    redis_latency: string;
    redis_memory: string;
    node_version: string;
    npm_version: string;
    queue_workers_count: number;
    cron_configured: boolean;
}

export interface RequirementsData {
    overview: {
        total: number;
        passed: number;
        warnings: number;
        errors: number;
        score_percent: number;
        is_ready: boolean;
    };
    server_spec: ServerSpec;
    items: RequirementItem[];
}

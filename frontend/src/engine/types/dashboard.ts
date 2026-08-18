export interface ContentStats {
    total: number;
    published: number;
    draft?: number;
    scheduled?: number;
    pending?: number;
    archived?: number;
    trashed?: number;
}

export interface DashboardMediaStats {
    total: number;
    total_count?: number; // Some endpoints return total_count
    size?: number;
    images?: number;
    videos?: number;
    documents?: number;
}

export interface UserStats {
    total: number;
    active?: number;
    online?: number;
}

export interface TrafficItem {
    period: string;
    visits: number | Record<string, unknown>; // Support dynamic visit objects from API
    [key: string]: unknown;
}

export interface SystemStats {
    contents?: ContentStats;
    media?: DashboardMediaStats;
    users?: UserStats;
}

export interface CreatorDashboardData {
    stats?: {
        myContents: ContentStats;
        myMedia: DashboardMediaStats;
    };
    charts?: {
        myContentByStatus: { status: string; count: number }[];
        contentTraffic: { date: string; count: number }[];
        recentActivity?: { date: string; count: number }[];
    };
    topContent?: TopContentItem[];
}

export type TopContentItem = {
    id: string | string;
    title: string;
    type: string;
    views: number;
    status: string;
};

export type TrafficDataPoint = {
    period: string;
    visits: number;
    [key: string]: unknown;
};

export type StatusDataPoint = {
    label: string;
    count: number;
    [key: string]: unknown;
};

export interface DashboardStats {
    contents?: ContentStats;
    myContents?: ContentStats;
    media?: DashboardMediaStats;
    myMedia?: DashboardMediaStats;
    users?: UserStats;
}

export interface DashboardCharts {
    contentTraffic?: (TrafficDataPoint | { date: string; count: number })[];
    userActivity?: (TrafficDataPoint | { date: string; count: number })[];
}

export interface DashboardData {
    stats?: DashboardStats;
    charts?: DashboardCharts;
}

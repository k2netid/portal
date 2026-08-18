import api from '@/engine/api/client';
import { analyticsPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export const AnalyticsService = {
    trackEvent(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(analyticsPaths.track, payload);
    },

    trackBatch(events: unknown[]): Promise<AxiosResponse> {
        return api.post(analyticsPaths.trackBatch, { events });
    },

    trackVisit(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(analyticsPaths.trackVisit, payload);
    },

    overview(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(analyticsPaths.overview, { params });
    },

    visits(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(analyticsPaths.visits, { params });
    },

    topPages(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(analyticsPaths.topPages, { params });
    },

    topContent(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(analyticsPaths.topContent, { params });
    },

    devices(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(analyticsPaths.devices, { params });
    },

    browsers(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(analyticsPaths.browsers, { params });
    },

    countries(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(analyticsPaths.countries, { params });
    },

    referrers(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(analyticsPaths.referrers, { params });
    },

    realtime(): Promise<AxiosResponse> {
        return api.get(analyticsPaths.realtime);
    },

    export(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(analyticsPaths.export, { params, responseType: 'blob' });
    },

    cleanup(): Promise<AxiosResponse> {
        return api.post(analyticsPaths.cleanup);
    },

    purgeAll(confirmation: string): Promise<AxiosResponse> {
        return api.post(analyticsPaths.purgeAll, { confirmation });
    },
};

export default AnalyticsService;

import api from '@/engine/api/client';
import { newsletterPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export const NewsletterService = {
    subscribe(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(newsletterPaths.subscribe, payload);
    },

    listSubscribers(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(newsletterPaths.subscribers, { params });
    },

    deleteSubscriber(id: string): Promise<AxiosResponse> {
        return api.delete(newsletterPaths.subscriber(id));
    },

    forceDeleteSubscriber(id: string): Promise<AxiosResponse> {
        return api.delete(newsletterPaths.subscriberForce(id));
    },

    restoreSubscriber(id: string): Promise<AxiosResponse> {
        return api.post(newsletterPaths.subscriberRestore(id));
    },

    exportSubscribers(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(newsletterPaths.subscribersExport, { params, responseType: 'blob' });
    },

    bulkSubscribers(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(newsletterPaths.subscribersBulk, payload);
    },
};

export default NewsletterService;

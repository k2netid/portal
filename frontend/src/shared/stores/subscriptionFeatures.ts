import { defineStore } from 'pinia';
import api from '@/engine/api/client';
import { platformPaths } from '@/engine/api/paths';
import { logger } from '@/shared/utils/logger';

interface SubscriptionFeaturesState {
    features: Record<string, boolean>;
    isLoaded: boolean;
    isLoading: boolean;
}

export const useSubscriptionFeaturesStore = defineStore('subscriptionFeatures', {
    state: (): SubscriptionFeaturesState => ({
        features: {},
        isLoaded: false,
        isLoading: false,
    }),

    actions: {
        async fetchFeatures() {
            if (this.isLoaded || this.isLoading) return;

            this.isLoading = true;
            try {
                const response = await api.get(platformPaths.publicSubscriptionFeatures);
                const payload = response.data as { features?: Record<string, boolean> } | null;
                if (payload && typeof payload === 'object') {
                    this.features = payload.features ?? {};
                    this.isLoaded = true;
                }
            } catch (error) {
                logger.warning('Failed to fetch subscription features', error);
            } finally {
                this.isLoading = false;
            }
        },

        can(feature: string): boolean {
            return !!this.features[feature];
        },
    },
});

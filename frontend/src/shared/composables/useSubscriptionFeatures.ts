import { useSubscriptionFeaturesStore } from '@/shared/stores/subscriptionFeatures';

export function useSubscriptionFeatures() {
    const store = useSubscriptionFeaturesStore();

    return {
        fetchFeatures: () => store.fetchFeatures(),
        can: (feature: string) => store.can(feature),
        features: store.features,
        isLoaded: store.isLoaded,
    };
}

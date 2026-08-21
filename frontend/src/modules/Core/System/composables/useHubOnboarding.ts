import { computed, onMounted, ref } from 'vue';
import api from '@/engine/api/client';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import { logger } from '@/shared/utils/logger';

export interface OnboardingSteps {
    identity: boolean;
    data_model: boolean;
    security: boolean;
}

export interface OnboardingStatusPayload {
    dismissed: boolean;
    steps: OnboardingSteps;
    models_count?: number;
    site_name: string;
    complete: boolean;
    progress_percent: number;
}

const defaultStatus = (): OnboardingStatusPayload => ({
    dismissed: false,
    steps: { identity: false, data_model: false, security: false },
    site_name: '',
    complete: false,
    progress_percent: 0,
});

declare global {
    interface Window {
        __FORCE_ONBOARDING__?: boolean;
    }
}

function isForceOnboarding(): boolean {
    return typeof window !== 'undefined' && Boolean(window.__FORCE_ONBOARDING__);
}

export function useHubOnboarding() {
    const loading = ref(true);
    const dismissing = ref(false);
    const status = ref<OnboardingStatusPayload>(defaultStatus());

    const showCard = computed(() => {
        if (isForceOnboarding()) {
            return !status.value.dismissed;
        }
        return !status.value.dismissed && !status.value.complete;
    });

    const completedCount = computed(() => {
        const s = status.value.steps;
        return Number(s.identity) + Number(s.data_model) + Number(s.security);
    });

    const totalSteps = 3;

    async function fetchStatus(): Promise<void> {
        loading.value = true;
        try {
            const response = await api.get('/manage/system/onboarding-status');
            const data = parseSingleResponse<OnboardingStatusPayload>(response);
            if (data) {
                status.value = { ...defaultStatus(), ...data, steps: { ...defaultStatus().steps, ...data.steps } };
            }
        } catch (error: unknown) {
            logger.error('Failed to load onboarding status', error);
        } finally {
            loading.value = false;
        }
    }

    async function dismiss(): Promise<void> {
        dismissing.value = true;
        try {
            if (!isForceOnboarding()) {
                await api.post('/manage/system/onboarding/dismiss');
            }
            status.value = { ...status.value, dismissed: true };
        } catch (error: unknown) {
            logger.error('Failed to dismiss onboarding', error);
        } finally {
            dismissing.value = false;
        }
    }

    onMounted(() => {
        if (isForceOnboarding()) {
            loading.value = false;
            status.value = {
                dismissed: false,
                steps: { identity: true, data_model: true, security: false },
                site_name: 'Jejakawan Core',
                complete: false,
                progress_percent: 66,
            };
            return;
        }
        void fetchStatus();
    });

    return {
        loading,
        dismissing,
        status,
        showCard,
        completedCount,
        totalSteps,
        fetchStatus,
        dismiss,
    };
}

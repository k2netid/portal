import { computed, onMounted, ref } from 'vue';
import api from '@/engine/api/client';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import { logger } from '@/shared/utils/logger';

export interface OnboardingSteps {
    identity: boolean;
    theme: boolean;
    first_page: boolean;
}

export interface OnboardingStatusPayload {
    dismissed: boolean;
    steps: OnboardingSteps;
    active_theme_slug?: string | null;
    published_pages_count: number;
    site_name: string;
    complete: boolean;
    progress_percent: number;
}

const defaultStatus = (): OnboardingStatusPayload => ({
    dismissed: false,
    steps: { identity: false, theme: false, first_page: false },
    published_pages_count: 0,
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
        return Number(s.identity) + Number(s.theme) + Number(s.first_page);
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
                steps: { identity: true, theme: false, first_page: false },
                published_pages_count: 0,
                site_name: 'Jejakawan',
                complete: false,
                progress_percent: 33,
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

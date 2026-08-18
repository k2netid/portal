import { ref } from 'vue';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useConsoleContextStore } from '../stores/consoleContext';
import { useRouter } from 'vue-router';
import { buildConsolePath, resolveConsoleDashboardLocation } from '@/config/console';

export function useAuth() {
    const authStore = useAuthStore();
    const consoleStore = useConsoleContextStore();
    const router = useRouter();

    const loading = ref(false);
    const error = ref<string | null>(null);
    const retryAfter = ref(0);
    let retryTimer: any = null;

    const startRetryTimer = (seconds: number) => {
        retryAfter.value = seconds;
        if (retryTimer) clearInterval(retryTimer);
        retryTimer = setInterval(() => {
            retryAfter.value--;
            if (retryAfter.value <= 0) clearInterval(retryTimer);
        }, 1000);
    };

    const performLogin = async (credentials: any) => {
        loading.value = true;
        error.value = null;

        const result = await authStore.login(credentials);

        if (result.success) {
            if (result.requiresTwoFactor) {
                loading.value = false;
                return { requiresTwoFactor: true, userId: result.userId };
            }

            // Standard login success: handle redirect
            await handlePostLogin();
            return { success: true };
        }

        if (result.rateLimited) {
            startRetryTimer(result.retryAfter || 60);
        }

        error.value = result.message || 'Authentication failed';
        loading.value = false;
        return { success: false, errors: result.errors };
    };

    const handlePostLogin = async () => {
        const rank = authStore.getRoleRank();
        
        // Deterministic redirect based on rank
        if (rank >= 100) {
            consoleStore.setSystemContext();
            await router.replace(buildConsolePath('dashboard')); 
        } else {
            // Non-super: router guard picks first allowed dashboard
            await router.replace(resolveConsoleDashboardLocation());
        }
    };

    return {
        loading,
        error,
        retryAfter,
        performLogin,
        handlePostLogin
    };
}

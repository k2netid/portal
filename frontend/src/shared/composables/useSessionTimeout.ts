import { logger } from '@/shared/utils/logger';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { triggerVaporLock } from '@/engine/api/client';
import api from '@/engine/api/client';

import { authConfig } from '@/config';
import { isPublicShell } from '@/config/shell';
import {
    buildSessionExpiredQuery,
    type SessionExpiredReason,
} from '@/shared/utils/errorReturn';

export function useSessionTimeout() {
    const router = useRouter();
    const authStore = useAuthStore();
    const onPublicSite = isPublicShell();

    const isMemberConsolePath = (path = router.currentRoute.value.path): boolean => (
        path.startsWith('/member')
    );

    const goSessionExpired = (reason: SessionExpiredReason): void => {
        if (router.currentRoute.value.name === 'session-expired') {
            return;
        }

        void router.replace({
            name: 'session-expired',
            query: buildSessionExpiredQuery({
                reason,
                currentPath: router.currentRoute.value.fullPath,
            }),
        });
    };

    // Session configuration (in seconds)
    const SESSION_LIFETIME = authConfig.sessionLifetime;
    const WARNING_TIME = 300; // Show warning 5 minutes before expiry

    // State
    const isWarningVisible = ref(false);
    const timeRemaining = ref(WARNING_TIME);
    const lastActivityTime = ref(Date.now());

    // Timers
    let warningTimer: ReturnType<typeof setTimeout> | null = null;
    let countdownTimer: ReturnType<typeof setInterval> | null = null;
    let activityCheckTimer: ReturnType<typeof setInterval> | null = null;

    // Computed
    const sessionExpiryTime = computed(() => {
        return lastActivityTime.value + (SESSION_LIFETIME * 1000);
    });

    const timeUntilExpiry = computed(() => {
        return Math.max(0, Math.floor((sessionExpiryTime.value - Date.now()) / 1000));
    });

    const isAuthenticated = computed(() => authStore.isAuthenticated);

    // Activity tracking
    const trackActivity = () => {
        if (!isAuthenticated.value) return;

        lastActivityTime.value = Date.now();
        resetTimers();
    };

    // Event listeners for user activity
    const activityEvents = ['mousedown', 'keypress', 'scroll', 'touchstart', 'click'];

    const setupActivityListeners = () => {
        activityEvents.forEach(event => {
            window.addEventListener(event, trackActivity, { passive: true });
        });
    };

    const removeActivityListeners = () => {
        activityEvents.forEach(event => {
            window.removeEventListener(event, trackActivity);
        });
    };

    // Timer management
    const startWarningTimer = () => {
        clearWarningTimer();

        if (!isAuthenticated.value) return;

        const timeUntilWarning = (SESSION_LIFETIME - WARNING_TIME) * 1000;

        warningTimer = setTimeout(() => {
            showWarning();
        }, timeUntilWarning);
    };

    const clearWarningTimer = () => {
        if (warningTimer) {
            clearTimeout(warningTimer);
            warningTimer = null;
        }
    };

    const startCountdownTimer = () => {
        clearCountdownTimer();

        countdownTimer = setInterval(() => {
            if (timeRemaining.value > 0) {
                timeRemaining.value--;
            } else {
                handleTimeout();
            }
        }, 1000);
    };

    const clearCountdownTimer = () => {
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
    };

    // Heartbeat logic
    const HEARTBEAT_INTERVAL = 30 * 1000; // Check every 30 seconds for faster detection
    let heartbeatTimer: ReturnType<typeof setInterval> | null = null;

    const startHeartbeat = () => {
        clearHeartbeat();
        if (!isAuthenticated.value) return;

        heartbeatTimer = setInterval(async () => {
            if (window.__factoryResetInProgress) {
                return;
            }
            // Early exit if session is already dead to prevent redundant calls/loops
            if (window.__isSessionTerminated) {
                stopAllTimers();
                return;
            }

            try {
                // Lightweight check to validate session
                await api.get('/public/system/auth/me', { _skipManualRedirect: true } as Record<string, unknown>);
            } catch (error: unknown) {
                const err = error as { response?: { status?: number } };
                // Double check flag to prevent race conditions with api.js interceptor
                if (window.__isSessionTerminated) {
                    stopAllTimers();
                    return;
                }

                // If it's a session error (401, 403, or 419)
                const status = err.response?.status;
                if (status === 403) {
                    const roleRank = authStore.getRoleRank();
                    const memberOnly = roleRank > 0 && roleRank <= 10;
                    triggerVaporLock();
                    stopAllTimers();
                    authStore.clearAuth();
                    if (onPublicSite || isMemberConsolePath() || memberOnly) {
                        if (memberOnly && typeof window !== 'undefined') {
                            window.location.assign('/member/login');
                        }
                        return;
                    }
                    if (router.currentRoute.value.name !== 'forbidden') {
                        void router.replace({ name: 'forbidden' });
                    }
                    return;
                }

                if (status === 401 || status === 419) {
                    triggerVaporLock();
                    stopAllTimers();
                    authStore.clearAuth();
                    goSessionExpired('concurrent');
                }
            }
        }, HEARTBEAT_INTERVAL);
    };

    const clearHeartbeat = () => {
        if (heartbeatTimer) {
            clearInterval(heartbeatTimer);
            heartbeatTimer = null;
        }
    };

    const startActivityCheckTimer = () => {
        clearActivityCheckTimer();

        // Check every 30 seconds if session should show warning
        activityCheckTimer = setInterval(() => {
            if (!isAuthenticated.value) {
                stopAllTimers();
                return;
            }

            const timeLeft = timeUntilExpiry.value;

            if (timeLeft <= 0) {
                handleTimeout();
            } else if (timeLeft <= WARNING_TIME && !isWarningVisible.value) {
                showWarning();
            }
        }, 30000); // Check every 30 seconds
    };

    const clearActivityCheckTimer = () => {
        if (activityCheckTimer) {
            clearInterval(activityCheckTimer);
            activityCheckTimer = null;
        }
    };

    const stopAllTimers = () => {
        clearWarningTimer();
        clearCountdownTimer();
        clearActivityCheckTimer();
        clearHeartbeat();
    };

    const resetTimers = () => {
        if (!isWarningVisible.value) {
            startWarningTimer();
            // No need to reset heartbeat on activity, it should run independently
        }
    };

    // Warning modal
    const showWarning = () => {
        if (!isAuthenticated.value) return;

        isWarningVisible.value = true;
        timeRemaining.value = timeUntilExpiry.value;
        startCountdownTimer();
    };

    const hideWarning = () => {
        isWarningVisible.value = false;
        clearCountdownTimer();
        timeRemaining.value = WARNING_TIME;
    };

    // Actions
    const extendSession = async () => {
        try {
            // Make a lightweight API call to extend the session
            await api.get('/public/system/auth/me');

            // Reset activity time
            lastActivityTime.value = Date.now();

            // Hide warning and reset timers
            hideWarning();
            startWarningTimer();

        } catch (error) {
            logger.error('Failed to extend session:', error);
            // If extend fails, likely session already expired
            handleTimeout();
        }
    };

    const handleTimeout = () => {
        // Stop everything via Vapor Lock
        triggerVaporLock();
        stopAllTimers();
        hideWarning();

        // Logout user locally
        localStorage.removeItem('user');
        authStore.isAuthenticated = false;

        goSessionExpired('timeout');
    };

    const manualLogout = async () => {
        stopAllTimers();
        hideWarning();
        removeActivityListeners();

        await authStore.logout();
        router.push('/');
    };

    // Initialization
    const initialize = () => {
        if (onPublicSite || !isAuthenticated.value || isMemberConsolePath()) return;

        setupActivityListeners();
        startWarningTimer();
        startActivityCheckTimer();
        startHeartbeat();
        lastActivityTime.value = Date.now();
    };

    const cleanup = () => {
        stopAllTimers();
        removeActivityListeners();
        hideWarning();
    };

    // Lifecycle hooks
    onMounted(() => {
        initialize();
    });

    onUnmounted(() => {
        cleanup();
    });

    // Watch for auth changes
    const watchAuth = () => {
        const unwatch = authStore.$subscribe((_mutation, state) => {
            if (state.isAuthenticated) {
                initialize();
            } else {
                cleanup();
            }
        });

        onUnmounted(unwatch);
    };

    watchAuth();

    return {
        // State
        isWarningVisible,
        timeRemaining,
        timeUntilExpiry,

        // Actions
        extendSession,
        manualLogout,
        showWarning,
        hideWarning,
        trackActivity,

        // Utilities
        initialize,
        cleanup,
    };
}

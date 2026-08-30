import { computed, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { SECURITY_ROUTES } from '@/config/security';
import {
    defaultHomeForShell,
    errorReturnShell,
    isErrorScreenPath,
    rememberRouteBeforeError,
    resolveErrorReturnPath,
    sanitizeLoginRedirect,
    type ErrorReturnShell,
} from '@/shared/utils/errorReturn';
import { shouldBlockOnPublicSite } from '@/config/security';

export interface ErrorPageNavigationOptions {
    /** Route path of this error screen (e.g. `/403`). */
    errorPath?: string;
}

/**
 * Shared navigation for error / maintenance surfaces (SPA).
 * Security: no open redirects; probe paths blocked; sessionStorage only (per tab).
 */
export function useErrorPageNavigation(options: ErrorPageNavigationOptions = {}) {
    const router = useRouter();
    const route = useRoute();
    const authStore = useAuthStore();
    const systemStore = useSystemStore();

    const shell = computed<ErrorReturnShell>(() => errorReturnShell());
    const isAuthenticated = computed(() => authStore.isAuthenticated);
    const errorPath = computed(() => options.errorPath ?? route.path);

    const homePath = computed(() => defaultHomeForShell(
        shell.value,
        isAuthenticated.value,
        systemStore.consoleDashboardSlug,
    ));

    const hasSafeReturn = computed(() => {
        const target = resolveErrorReturnPath(shell.value, isAuthenticated.value);
        return Boolean(target && target !== route.fullPath);
    });

    const prepareErrorPage = (): void => {
        rememberRouteBeforeError(null, shell.value, isAuthenticated.value);
    };

    const buildLoginQuery = (overrides?: { timeout?: boolean; redirect?: unknown }): Record<string, string> => {
        const query: Record<string, string> = {};
        const redirect = sanitizeLoginRedirect(
            overrides?.redirect ?? route.query.redirect,
            errorPath.value,
        );
        if (redirect) {
            query.redirect = redirect;
        }
        if (overrides?.timeout || route.query.timeout === '1') {
            query.timeout = '1';
        }
        return query;
    };

    const goToLogin = async (overrides?: { timeout?: boolean; redirect?: unknown }): Promise<void> => {
        await router.push({
            path: SECURITY_ROUTES.login,
            query: buildLoginQuery(overrides),
        });
    };

    const goHome = async (): Promise<void> => {
        await router.push(homePath.value);
    };

    const goBack = async (): Promise<void> => {
        if (route.query.timeout === '1') {
            await goToLogin({ timeout: true });
            return;
        }

        if (shell.value === 'console' && !isAuthenticated.value) {
            await goToLogin();
            return;
        }

        const returnPath = resolveErrorReturnPath(shell.value, isAuthenticated.value);
        if (returnPath && returnPath !== route.fullPath) {
            await router.push(returnPath);
            return;
        }

        if (window.history.length > 1) {
            const before = route.fullPath;
            await router.back();
            await nextTick();

            const stuck = route.fullPath === before
                || isErrorScreenPath(route.path)
                || (shell.value === 'public' && shouldBlockOnPublicSite(route.path));

            if (stuck) {
                await goHome();
            }
            return;
        }

        await goHome();
    };

    return {
        shell,
        isAuthenticated,
        homePath,
        hasSafeReturn,
        prepareErrorPage,
        goHome,
        goBack,
        goToLogin,
    };
}

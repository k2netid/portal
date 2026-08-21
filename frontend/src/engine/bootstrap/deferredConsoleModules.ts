import type { Router } from 'vue-router';
import type { useNavigationStore } from '@/shared/stores/navigation';
import type { useDashboardStore } from '@/shared/stores/dashboard';

type NavStore = ReturnType<typeof useNavigationStore>;
type DashboardStore = ReturnType<typeof useDashboardStore>;

export function scheduleDeferredConsoleModules(
    _router: Router,
    _navStore: NavStore,
    _dbStore: DashboardStore,
) {
    // In Core Engine, core modules are already registered during primary bootstrap.
}

export function ensureDeferredConsoleModules(
    _router: Router,
    _navStore: NavStore,
    _dbStore: DashboardStore,
): Promise<void> {
    return Promise.resolve();
}

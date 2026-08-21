import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import type { RouteLocationNormalizedLoaded } from 'vue-router';
import { resolveLocaleKey } from '@/shared/i18n/resolveLocaleKey';
import { consoleDashboardSlug } from '@/shared/utils/consoleRoute';

/** Fallback i18n keys for first path segment under the console shell. */
const segmentConfig: Record<string, string> = {
    webhooks: 'infra.webhooks.title',
    plugins: 'infra.plugins.title',
    extensions: 'system.navigation.menu.extensions',
    'oauth-clients': 'system.oauth.title',
    'console-appearance': 'system.navigation.menu.consoleAppearance',
    models: 'infra.models.title',
    dynamic: 'infra.models.title',

    users: 'system.navigation.menu.users',
    roles: 'system.navigation.menu.roles',
    profile: 'system.profile.title',
    'journal-dashboard': 'system.navigation.menu.journalDashboard',
    'activity-journal': 'system.navigation.menu.activityJournal',
    'access-journal': 'system.navigation.menu.accessJournal',
    'system-journal': 'system.navigation.menu.systemJournal',
    languages: 'system.navigation.menu.languages',
    settings: 'system.navigation.menu.settings',
    backups: 'system.navigation.menu.backups',
    'security-journal': 'system.navigation.menu.securityJournal',
    'system/notifications': 'system.system.notifications.title',
    notifications: 'system.notifications.title',
    redis: 'system.navigation.menu.redis',
    system: 'system.navigation.menu.systemInfo',
    'scheduled-tasks': 'system.navigation.menu.scheduledTasks',
    'command-runner': 'system.command_runner.title',
};

const staticPathConfig: Record<string, string> = {
    '/login': 'system.auth.login.title',
    '/register': 'system.auth.register.title',
    '/public/system/auth/forgot-password': 'system.auth.forgotPassword.title',
};

const UUID_LIKE = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

interface ParentCrumbRule {
    test: RegExp;
    parentPath: (normalized: string, route: RouteLocationNormalizedLoaded, slug: string) => string;
    labelKey: string;
}

const parentCrumbRules: ParentCrumbRule[] = [
    {
        test: /^\/users\/[^/]+\/edit$/,
        parentPath: (_n, _r, slug) => `/${slug}/users`,
        labelKey: 'system.navigation.menu.users',
    },
    {
        test: /^\/roles\/[^/]+\/edit$/,
        parentPath: (_n, _r, slug) => `/${slug}/roles`,
        labelKey: 'system.navigation.menu.roles',
    },
    {
        test: /^\/dynamic\/[^/]+\/records\/[^/]+$/,
        parentPath: (n, _r, slug) => {
            const parts = n.split('/').filter(Boolean);
            return `/${slug}/dynamic/${parts[1]}/records`;
        },
        labelKey: 'infra.models.title',
    },
    {
        test: /^\/dynamic\/[^/]+\/records\/new$/,
        parentPath: (n, _r, slug) => {
            const parts = n.split('/').filter(Boolean);
            return `/${slug}/dynamic/${parts[1]}/records`;
        },
        labelKey: 'infra.models.title',
    },
    {
        test: /^\/models\/new$/,
        parentPath: (_n, _r, slug) => `/${slug}/models`,
        labelKey: 'infra.models.title',
    },
    {
        test: /^\/models\/[^/]+$/,
        parentPath: (_n, _r, slug) => `/${slug}/models`,
        labelKey: 'infra.models.title',
    },
];

export interface BreadcrumbItem {
    label: string;
    path: string;
}

function normalizeConsolePath(routePath: string, slug: string): string {
    const prefix = `/${slug}`;
    if (routePath.startsWith(prefix)) {
        return routePath.slice(prefix.length) || '/';
    }
    if (routePath.startsWith('/dash/')) {
        return routePath.slice('/dash'.length) || '/';
    }
    return routePath;
}

function segmentLabelKey(segment: string): string | undefined {
    return segmentConfig[segment];
}

function applyCreateParentRule(
    normalized: string,
    slug: string,
    crumbs: BreadcrumbItem[],
    translateKey: (key: string, fallback?: string) => string,
): void {
    const match = normalized.match(/^\/([^/]+)\/create$/);
    if (!match) return;
    const segment = match[1]!;
    const key = segmentLabelKey(segment);
    if (!key) return;
    const parentPath = `/${slug}/${segment}`;
    if (crumbs.some((c) => c.path === parentPath)) return;
    crumbs.push({ label: translateKey(key), path: parentPath });
}

export function useBreadcrumbs() {
    const { t, te } = useI18n();
    const customBreadcrumbs = ref<Record<string, string>>({});

    const translateKey = (key: string, fallback?: string) =>
        resolveLocaleKey(t, te, key, fallback);

    const getLabel = (path: string, route: RouteLocationNormalizedLoaded | null): string => {
        if (customBreadcrumbs.value[path]) {
            return customBreadcrumbs.value[path];
        }

        if (route?.meta?.breadcrumb) {
            return translateKey(route.meta.breadcrumb as string);
        }

        const staticMatch = staticPathConfig[path];
        if (staticMatch) {
            return translateKey(staticMatch);
        }

        const segments = path.split('/').filter(Boolean);
        if (segments.length > 1) {
            const afterSlug = segments.slice(1).join('/');
            const afterSlugMatch = segmentConfig[afterSlug];
            if (afterSlugMatch) {
                return translateKey(afterSlugMatch);
            }
            const lastSegment = segments[segments.length - 1]!;
            if (!UUID_LIKE.test(lastSegment)) {
                const lastSegmentMatch = segmentConfig[lastSegment];
                if (lastSegmentMatch) {
                    return translateKey(lastSegmentMatch);
                }
            }
        } else if (segments.length === 1) {
            const firstSegmentMatch = segmentConfig[segments[0]!];
            if (firstSegmentMatch) {
                return translateKey(firstSegmentMatch);
            }
        }

        const segment = path.split('/').pop();
        if (!segment || UUID_LIKE.test(segment)) {
            return translateKey('common.navigation.breadcrumbs.home');
        }

        if (['create', 'edit'].includes(segment)) {
            const actionKey = `common.actions.${segment}`;
            if (te(actionKey)) return translateKey(actionKey);
        }

        return segment
            .replace(/-/g, ' ')
            .replace(/\b\w/g, (l) => l.toUpperCase());
    };

    const getBreadcrumbs = (route: RouteLocationNormalizedLoaded | null): BreadcrumbItem[] => {
        if (!route?.path) return [];

        const slug =
            (route.params.dashboard_slug as string | undefined) || consoleDashboardSlug();
        const pathSegments = route.path.split('/').filter(Boolean);
        const isConsolePath =
            pathSegments[0] === slug ||
            route.path.startsWith('/dash/') ||
            route.path === '/dash';

        if (!isConsolePath) {
            if (route.path === '/') {
                return [{
                    label: translateKey('common.navigation.breadcrumbs.home'),
                    path: '/',
                }];
            }
            return [{
                label: translateKey('common.navigation.breadcrumbs.home'),
                path: '/',
            }, {
                label: getLabel(route.path, route),
                path: route.path,
            }];
        }

        const crumbs: BreadcrumbItem[] = [{
            label: translateKey('common.navigation.breadcrumbs.home'),
            path: `/${slug}`,
        }];

        const normalized = normalizeConsolePath(route.path, slug);

        for (const rule of parentCrumbRules) {
            if (!rule.test.test(normalized)) continue;
            const parentPath = rule.parentPath(normalized, route, slug);
            if (!crumbs.some((c) => c.path === parentPath)) {
                crumbs.push({
                    label: translateKey(rule.labelKey),
                    path: parentPath,
                });
            }
            break;
        }

        applyCreateParentRule(normalized, slug, crumbs, translateKey);

        const leafPath = route.path;
        const leafLabel = customBreadcrumbs.value[leafPath]
            ?? (route.meta?.breadcrumb
                ? translateKey(route.meta.breadcrumb as string)
                : route.meta?.title
                  ? translateKey(route.meta.title as string, route.meta.title as string)
                  : getLabel(leafPath, route));

        const last = crumbs[crumbs.length - 1];
        if (last?.path === leafPath) {
            last.label = leafLabel;
        } else {
            crumbs.push({ label: leafLabel, path: leafPath });
        }

        return crumbs;
    };

    const setBreadcrumb = (path: string, label: string) => {
        customBreadcrumbs.value[path] = label;
    };

    const setBreadcrumbs = (breadcrumbs: Record<string, string>) => {
        customBreadcrumbs.value = { ...customBreadcrumbs.value, ...breadcrumbs };
    };

    const clearBreadcrumbs = () => {
        customBreadcrumbs.value = {};
    };

    const clearBreadcrumb = (path: string) => {
        const next = { ...customBreadcrumbs.value };
        delete next[path];
        customBreadcrumbs.value = next;
    };

    return {
        getBreadcrumbs,
        setBreadcrumb,
        setBreadcrumbs,
        clearBreadcrumbs,
        clearBreadcrumb,
        getLabel,
    };
}

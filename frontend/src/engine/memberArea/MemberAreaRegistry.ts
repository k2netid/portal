import type { RouteRecordRaw } from 'vue-router';
import type {
    MemberAreaContribution,
    MemberAreaNavItem,
    MemberAreaWidget,
    MemberPortalContext,
} from './types';

const CORE_EXTENSION = 'member';

function satisfiesDeps(
    contribution: MemberAreaContribution,
    activeExtensions: string[],
): boolean {
    const deps = contribution.dependsOn ?? [CORE_EXTENSION];
    return deps.every((slug) => activeExtensions.includes(slug));
}

function isContributionActive(
    contribution: MemberAreaContribution,
    activeExtensions: string[],
): boolean {
    if (!activeExtensions.includes(contribution.extensionSlug)) {
        return false;
    }

    return satisfiesDeps(contribution, activeExtensions);
}

function passesCapabilityGate(
    item: { capability?: string; requiresVerified?: boolean },
    ctx: MemberPortalContext,
): boolean {
    if (item.requiresVerified && !ctx.emailVerified) {
        return false;
    }

    if (item.capability) {
        return ctx.capabilities?.includes(item.capability) ?? ctx.activeExtensions.includes(CORE_EXTENSION);
    }

    return true;
}

class MemberAreaRegistry {
    private contributions: MemberAreaContribution[] = [];

    register(contribution: MemberAreaContribution): void {
        this.contributions.push(contribution);
    }

    private widgetKey(extensionSlug: string, slug: string): string {
        return `${extensionSlug}:${slug}`;
    }

    resolveWidget(extensionSlug: string, slug: string): MemberAreaWidget | undefined {
        for (const contribution of this.contributions) {
            if (contribution.extensionSlug !== extensionSlug) {
                continue;
            }
            const match = (contribution.widgets ?? []).find((widget) => widget.slug === slug);
            if (match) {
                return match;
            }
        }

        return undefined;
    }

    getNavigation(ctx: MemberPortalContext): MemberAreaNavItem[] {
        const items: MemberAreaNavItem[] = [];

        for (const contribution of this.contributions) {
            if (!isContributionActive(contribution, ctx.activeExtensions)) {
                continue;
            }
            for (const nav of contribution.navigation ?? []) {
                if (!passesCapabilityGate(nav, ctx)) {
                    continue;
                }
                items.push(nav);
            }
        }

        return items.sort((a, b) => (a.order ?? 100) - (b.order ?? 100));
    }

    getWidgets(ctx: MemberPortalContext): MemberAreaWidget[] {
        const widgets: MemberAreaWidget[] = [];

        for (const contribution of this.contributions) {
            if (!isContributionActive(contribution, ctx.activeExtensions)) {
                continue;
            }
            for (const widget of contribution.widgets ?? []) {
                if (!passesCapabilityGate(widget, ctx)) {
                    continue;
                }
                widgets.push(widget);
            }
        }

        return widgets.sort((a, b) => (a.order ?? 100) - (b.order ?? 100));
    }

    /**
     * Resolve dashboard widgets from portal payload (server) with FE component registry fallback.
     */
    getDashboardWidgets(
        ctx: MemberPortalContext,
        portalWidgets: Array<{
            slug: string;
            slot?: string;
            order?: number;
            capability?: string | null;
            extension_slug?: string;
            requires_verified?: boolean;
        }> | undefined,
    ): MemberAreaWidget[] {
        if (portalWidgets?.length) {
            const resolved: MemberAreaWidget[] = [];

            for (const item of portalWidgets) {
                if ((item.slot ?? 'dashboard') !== 'dashboard') {
                    continue;
                }
                const extensionSlug = item.extension_slug ?? '';
                if (!extensionSlug) {
                    continue;
                }
                const widget = this.resolveWidget(extensionSlug, item.slug);
                if (!widget) {
                    continue;
                }
                if (!passesCapabilityGate({
                    capability: item.capability ?? widget.capability,
                    requiresVerified: item.requires_verified ?? widget.requiresVerified,
                }, ctx)) {
                    continue;
                }
                resolved.push({
                    ...widget,
                    extensionSlug,
                    order: item.order ?? widget.order,
                });
            }

            if (resolved.length > 0) {
                return resolved.sort((a, b) => (a.order ?? 100) - (b.order ?? 100));
            }
        }

        return this.getWidgets(ctx);
    }

    getPackRoutes(activeExtensions: string[]): RouteRecordRaw[] {
        const routes: RouteRecordRaw[] = [];

        for (const contribution of this.contributions) {
            if (contribution.extensionSlug === CORE_EXTENSION) {
                continue;
            }
            if (!isContributionActive(contribution, activeExtensions)) {
                continue;
            }
            routes.push(...(contribution.routes ?? []));
        }

        return routes;
    }
}

export const memberAreaRegistry = new MemberAreaRegistry();

export function registerMemberAreaContributions(
    contributions: MemberAreaContribution[],
): void {
    contributions.forEach((contribution) => memberAreaRegistry.register(contribution));
}

export function appendMemberPortalRoutes(
    router: { addRoute: (route: RouteRecordRaw) => void },
    activeExtensions: string[],
): void {
    for (const route of memberAreaRegistry.getPackRoutes(activeExtensions)) {
        router.addRoute('member-portal', route);
    }
}

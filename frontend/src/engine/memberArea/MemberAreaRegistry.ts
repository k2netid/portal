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

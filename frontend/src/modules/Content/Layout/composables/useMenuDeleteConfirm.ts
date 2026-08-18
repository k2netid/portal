import { useI18n } from 'vue-i18n';
import { useConfirm } from '@/shared/composables/useConfirm';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import LayoutService from '@/modules/Content/Layout/services/layoutService';
import type { MenuUsageAnalysis, MenuUsageEntry } from '@/modules/Content/Layout/types/menuUsage';

export type MenuDeleteMode = 'soft' | 'force';

export interface MenuDeleteConfirmResult {
    confirmed: boolean;
    blocked: boolean;
    usage: MenuUsageAnalysis | null;
}

function formatUsageLine(entry: MenuUsageEntry, t: (key: string, params?: Record<string, unknown>) => string): string {
    if (entry.type === 'theme_assignment') {
        const themeLabel = entry.theme_name ?? entry.theme_slug ?? 'Theme';
        const activeSuffix = entry.theme_is_active
            ? t('layout.menus.usage.activeTheme')
            : t('layout.menus.usage.inactiveTheme');
        return t('layout.menus.usage.themeAssignment', {
            theme: `${themeLabel} (${activeSuffix})`,
            location: entry.location_label ?? entry.slot_key ?? '',
        });
    }

    return t('layout.menus.usage.publicLocation', {
        location: entry.location_label ?? entry.location ?? '',
    });
}

function buildUsageDescription(
    usage: MenuUsageAnalysis | null,
    usageCheckFailed: boolean,
    t: (key: string, params?: Record<string, unknown>) => string,
): string {
    if (usageCheckFailed) {
        return t('layout.menus.usage.checkFailed');
    }

    if (!usage?.is_in_use || !usage.usages?.length) {
        return t('layout.menus.usage.notInUse');
    }

    const lines = usage.usages.map((entry) => `• ${formatUsageLine(entry, t)}`);
    return [t('layout.menus.usage.inUseLocations'), '', ...lines].join('\n');
}

export function useMenuDeleteConfirm() {
    const { t } = useI18n();
    const { confirm } = useConfirm();

    async function fetchUsage(menuId: string): Promise<{ usage: MenuUsageAnalysis | null; failed: boolean }> {
        try {
            const response = await LayoutService.getMenuUsage(menuId);
            const usage = parseSingleResponse<MenuUsageAnalysis>(response);
            return { usage, failed: usage === null };
        } catch {
            return { usage: null, failed: true };
        }
    }

    async function confirmMenuDelete(
        menuId: string,
        menuName: string,
        mode: MenuDeleteMode,
    ): Promise<MenuDeleteConfirmResult> {
        const { usage, failed: usageCheckFailed } = await fetchUsage(menuId);
        const inUse = usage?.is_in_use ?? false;
        const blocked = mode === 'force' && inUse;

        const isForce = mode === 'force';
        const title = blocked
            ? t('layout.menus.usage.forceDeleteBlockedTitle')
            : inUse
                ? t('layout.menus.usage.deleteTitleInUse', { name: menuName })
                : isForce
                    ? t('common.actions.deletePermanently')
                    : t('common.actions.delete');

        const lead = blocked
            ? t('layout.menus.usage.forceDeleteBlocked', { name: menuName })
            : isForce
                ? t('layout.menus.messages.forceDeleteConfirm', { name: menuName })
                : inUse
                    ? t('layout.menus.usage.softDeleteInUse', { name: menuName })
                    : t('layout.menus.messages.deleteConfirm', { name: menuName });

        const usageBlock = buildUsageDescription(usage, usageCheckFailed, t);
        const message = `${lead}\n\n${usageBlock}`;

        const confirmed = await confirm({
            title,
            message,
            variant: blocked ? 'warning' : 'danger',
            confirmText: blocked
                ? t('layout.menus.usage.dismiss')
                : isForce
                    ? t('common.actions.deletePermanently')
                    : t('common.actions.delete'),
        });

        return {
            confirmed: !!confirmed && !blocked,
            blocked,
            usage,
        };
    }

    return { confirmMenuDelete, fetchUsage, buildUsageDescription };
}

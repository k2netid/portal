/**
 * Core module locale bundles (eager).
 */
import system from '@/modules/Core/System/locales';
import infra from '@/modules/Core/Infra/locales';
import mail from '@/modules/Mail/locales';
import mediaPack from '@/modules/Media/locales';
import library from '@/modules/Library/locales';
import publishing from '@/modules/Publishing/locales';
import layoutPack from '@/modules/Layout/locales';

import consoleEn from '@/locales/en/console.json';
import consoleId from '@/locales/id/console.json';
import consoleSu from '@/locales/su/console.json';

function mergeSystemLocale(systemBundle: Record<string, unknown>, mailBundle: Record<string, unknown>) {
    const navigation = systemBundle.navigation as Record<string, unknown> | undefined;
    const menu = navigation?.menu as Record<string, unknown> | undefined;

    return {
        ...systemBundle,
        mail: mailBundle.mail,
        navigation: {
            ...navigation,
            menu: {
                ...menu,
                mail: mailBundle.navigationMenuMail,
            },
        },
    };
}

const coLocated = {
    system: mergeSystemLocale(system.en as Record<string, unknown>, mail.en as Record<string, unknown>),
    infra: infra.en,
} as const;

const coLocatedId = {
    system: mergeSystemLocale(system.id as Record<string, unknown>, mail.id as Record<string, unknown>),
    infra: infra.id,
} as const;

const coLocatedSu = {
    system: mergeSystemLocale(system.su as Record<string, unknown>, mail.su as Record<string, unknown>),
    infra: infra.su,
} as const;

export const moduleLocaleBundles = {
    en: {
        ...coLocated,
        sharedConsole: consoleEn,
        console: consoleEn,
        media: mediaPack.en,
        library: library.en,
        publishing: publishing.en,
        layout: layoutPack.en,
    },
    id: {
        ...coLocatedId,
        sharedConsole: consoleId,
        console: consoleId,
        media: mediaPack.id,
        library: library.id,
        publishing: publishing.id,
        layout: layoutPack.id,
    },
    su: {
        ...coLocatedSu,
        sharedConsole: consoleSu,
        console: consoleSu,
        media: mediaPack.su,
        library: library.su,
        publishing: publishing.su,
        layout: layoutPack.su,
    },
} as const;

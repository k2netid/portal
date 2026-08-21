/**
 * Core module locale bundles (eager).
 */
import system from '@/modules/Core/System/locales';
import infra from '@/modules/Core/Infra/locales';

import consoleEn from '@/locales/en/console.json';
import consoleId from '@/locales/id/console.json';
import consoleSu from '@/locales/su/console.json';

const coLocated = {
    system: system.en,
    infra: infra.en,
} as const;

const coLocatedId = {
    system: system.id,
    infra: infra.id,
} as const;

const coLocatedSu = {
    system: system.su,
    infra: infra.su,
} as const;

export const moduleLocaleBundles = {
    en: {
        ...coLocated,
        sharedConsole: consoleEn,
        console: consoleEn,
    },
    id: {
        ...coLocatedId,
        sharedConsole: consoleId,
        console: consoleId,
    },
    su: {
        ...coLocatedSu,
        sharedConsole: consoleSu,
        console: consoleSu,
    },
} as const;

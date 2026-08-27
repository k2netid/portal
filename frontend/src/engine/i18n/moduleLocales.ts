/**
 * Per-module locale bundles (eager). Each pack stays under its own root key:
 * system.*, infra.*, mail.*, publishing.*, … — never merged into another module.
 */
import system from '@/modules/Core/System/locales';
import infra from '@/modules/Core/Infra/locales';
import mail from '@/modules/Mail/locales';
import mediaPack from '@/modules/Media/locales';
import library from '@/modules/Library/locales';
import publishing from '@/modules/Publishing/locales';
import layoutPack from '@/modules/Layout/locales';
import formsPack from '@/modules/Forms/locales';
import newsletterPack from '@/modules/Newsletter/locales';
import searchPack from '@/modules/Search/locales';
import cmsAiPack from '@/modules/CmsAi/locales';

import consoleEn from '@/locales/en/console.json';
import consoleId from '@/locales/id/console.json';
import consoleSu from '@/locales/su/console.json';

function flattenMailPack(bundle: Record<string, unknown>): Record<string, unknown> {
    const inner = bundle.mail && typeof bundle.mail === 'object'
        ? (bundle.mail as Record<string, unknown>)
        : {};

    return {
        ...inner,
        navigationMenuMail: bundle.navigationMenuMail,
    };
}

export const moduleLocaleBundles = {
    en: {
        system: system.en,
        infra: infra.en,
        mail: flattenMailPack(mail.en as Record<string, unknown>),
        sharedConsole: consoleEn,
        console: consoleEn,
        media: mediaPack.en,
        library: library.en,
        publishing: publishing.en,
        layout: layoutPack.en,
        forms: formsPack.en,
        newsletter: newsletterPack.en,
        search: searchPack.en,
        ai: cmsAiPack.en,
    },
    id: {
        system: system.id,
        infra: infra.id,
        mail: flattenMailPack(mail.id as Record<string, unknown>),
        sharedConsole: consoleId,
        console: consoleId,
        media: mediaPack.id,
        library: library.id,
        publishing: publishing.id,
        layout: layoutPack.id,
        forms: formsPack.id,
        newsletter: newsletterPack.id,
        search: searchPack.id,
        ai: cmsAiPack.id,
    },
    su: {
        system: system.su,
        infra: infra.su,
        mail: flattenMailPack(mail.su as Record<string, unknown>),
        sharedConsole: consoleSu,
        console: consoleSu,
        media: mediaPack.su,
        library: library.su,
        publishing: publishing.su,
        layout: layoutPack.su,
        forms: formsPack.su,
        newsletter: newsletterPack.su,
        search: searchPack.su,
        ai: cmsAiPack.su,
    },
} as const;

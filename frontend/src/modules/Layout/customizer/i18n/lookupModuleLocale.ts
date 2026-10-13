import i18n from '@/engine/i18n';
import layungEn from '@/modules/Layout/views/themes/layung/locales/en.json';
import layungId from '@/modules/Layout/views/themes/layung/locales/id.json';
import layungSu from '@/modules/Layout/views/themes/layung/locales/su.json';
import sarangengeEn from '@/modules/Layout/views/themes/sarangenge/locales/en.json';
import sarangengeId from '@/modules/Layout/views/themes/sarangenge/locales/id.json';
import sarangengeSu from '@/modules/Layout/views/themes/sarangenge/locales/su.json';
import janariEn from '@/modules/Layout/views/themes/janari/locales/en.json';
import janariId from '@/modules/Layout/views/themes/janari/locales/id.json';
import janariSu from '@/modules/Layout/views/themes/janari/locales/su.json';
import sareupnaEn from '@/modules/Layout/views/themes/sareupna/locales/en.json';
import sareupnaId from '@/modules/Layout/views/themes/sareupna/locales/id.json';
import sareupnaSu from '@/modules/Layout/views/themes/sareupna/locales/su.json';

const themeLocales = {
    en: { theme: { layung: layungEn, sarangenge: sarangengeEn, janari: janariEn, sareupna: sareupnaEn } },
    id: { theme: { layung: layungId, sarangenge: sarangengeId, janari: janariId, sareupna: sareupnaId } },
    su: { theme: { layung: layungSu, sarangenge: sarangengeSu, janari: janariSu, sareupna: sareupnaSu } },
};

function walkPath(root: unknown, key: string): unknown {
    if (!key || root == null) return undefined;
    let cur: unknown = root;
    for (const part of key.split('.')) {
        if (!cur || typeof cur !== 'object' || !(part in cur)) return undefined;
        cur = (cur as Record<string, unknown>)[part];
    }
    return cur;
}

/** Read theme labels statically and other module labels from the active i18n catalog. */
export function lookupModuleLocale(locale: string, key: string): string | null {
    if (!key) return null;
    const code = locale in themeLocales ? (locale as keyof typeof themeLocales) : 'id';
    const messages = i18n.global.getLocaleMessage(code) as Record<string, unknown>;
    const packs = {
        ...messages,
        ...themeLocales[code],
    };
    const order = code === 'id' ? ['id'] : [code, 'id'];
    for (const loc of order) {
        const value = walkPath(loc === code ? packs : i18n.global.getLocaleMessage(loc), key);
        if (typeof value === 'string' && value.length > 0) return value;
    }
    return null;
}

export function looksLikeI18nPath(value: string): boolean {
    return /^(theme|publishing|layout|sharedConsole|system|common)\.[a-z0-9_.]+$/i.test(value);
}

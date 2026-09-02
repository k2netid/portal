import { moduleLocaleBundles } from '@/engine/i18n/moduleLocales';

const bundledLocales = new Set(['id', 'en', 'su']);

function walkPath(root: unknown, key: string): unknown {
    if (!key || root == null) return undefined;
    let cur: unknown = root;
    for (const part of key.split('.')) {
        if (!cur || typeof cur !== 'object' || !(part in cur)) return undefined;
        cur = (cur as Record<string, unknown>)[part];
    }
    return cur;
}

/** Read a dotted key from the static module locale packs (not vue-i18n runtime). */
export function lookupModuleLocale(locale: string, key: string): string | null {
    if (!key) return null;
    const code = bundledLocales.has(locale) ? locale : 'id';
    const packs = moduleLocaleBundles as Record<string, Record<string, unknown>>;
    const order = code === 'id' ? ['id'] : [code, 'id'];
    for (const loc of order) {
        const value = walkPath(packs[loc], key);
        if (typeof value === 'string' && value.length > 0) return value;
    }
    return null;
}

export function looksLikeI18nPath(value: string): boolean {
    return /^(theme|publishing|layout|sharedConsole|system|common)\.[a-z0-9_.]+$/i.test(value);
}

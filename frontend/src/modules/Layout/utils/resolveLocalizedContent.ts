import { normalizeLocaleCode } from '@/engine/i18n';

type ContentRecord = Record<string, unknown> | null | undefined;

const LOCALIZABLE_FIELDS = ['title', 'excerpt', 'intro', 'body', 'content', 'meta_title', 'meta_description'] as const;

export type LocalizableContentField = (typeof LOCALIZABLE_FIELDS)[number];

/**
 * Pick a localized Jejakawan field from API payload (already merged by backend) or meta fallbacks.
 */
export function resolveLocalizedContentField(
    record: ContentRecord,
    field: LocalizableContentField,
    locale?: string,
): string {
    if (!record) return '';

    const lang = normalizeLocaleCode(locale ?? 'id');
    const direct = record[field];
    if (typeof direct === 'string' && direct.trim() !== '') {
        return direct;
    }

    const meta = record.meta;
    if (meta && typeof meta === 'object' && !Array.isArray(meta)) {
        const metaRecord = meta as Record<string, unknown>;
        const metaKey = `${field}_${lang}`;
        const fromMeta = metaRecord[metaKey];
        if (typeof fromMeta === 'string' && fromMeta.trim() !== '') {
            return fromMeta;
        }
    }

    if (field === 'content' && typeof record.body === 'string') {
        return record.body;
    }

    return typeof direct === 'string' ? direct : '';
}

export function resolveLocalizedPageHtml(record: ContentRecord, locale?: string): string {
    const body = resolveLocalizedContentField(record, 'body', locale);
    if (body.trim() !== '') return body;
    return resolveLocalizedContentField(record, 'content', locale);
}

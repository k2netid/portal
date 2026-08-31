import { normalizeLocaleCode } from '@/engine/i18n';

type ContentRecord = Record<string, unknown> | null | undefined;

export type LocalizableContentField =
    | 'title'
    | 'excerpt'
    | 'intro'
    | 'body'
    | 'content'
    | 'meta_title'
    | 'meta_description';

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

function pageHasBuilderBlocks(record: ContentRecord): boolean {
    if (!record) return false;
    const meta = record.meta;
    if (meta && typeof meta === 'object' && !Array.isArray(meta)) {
        const blocks = (meta as Record<string, unknown>).builder_blocks;
        if (Array.isArray(blocks) && blocks.length > 0) return true;
    }
    const topLevel = record.blocks;
    return Array.isArray(topLevel) && topLevel.length > 0;
}

function isBuilderOverrideEnabled(record: ContentRecord): boolean {
    if (!record) return false;
    const meta = record.meta;
    if (!meta || typeof meta !== 'object' || Array.isArray(meta)) return false;
    return (meta as Record<string, unknown>).builder_override === true;
}

/**
 * Theme-bound pages keep Vue layout unless the editor explicitly enabled builder_override.
 * Also respects meta.use_theme_template for sample shells.
 */
export function pagePrefersThemeTemplate(record: ContentRecord): boolean {
    if (!record) return false;
    if (isBuilderOverrideEnabled(record) && pageHasBuilderBlocks(record)) return false;

    const meta = record.meta;
    if (!meta || typeof meta !== 'object' || Array.isArray(meta)) return false;
    const metaRecord = meta as Record<string, unknown>;

    if (metaRecord.use_theme_template === true) return true;

    const themePage = typeof metaRecord.theme_page === 'string' ? metaRecord.theme_page.trim() : '';
    return themePage !== '' && metaRecord.builder_override !== true;
}

/** CMS HTML for public theme pages (respects use_theme_template / builder_override). */
export function resolvePublicPageCmsBody(record: ContentRecord, locale?: string): string {
    if (pagePrefersThemeTemplate(record)) return '';
    return resolveLocalizedPageHtml(record, locale);
}

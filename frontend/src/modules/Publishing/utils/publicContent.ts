import type { Content } from '@/modules/Publishing/types/content';

/** True when API payload looks like a Jejakawan content record (not HTML / envelope noise). */
export const isPublicContentRecord = (value: unknown): value is Content => {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
        return false;
    }

    const record = value as Record<string, unknown>;
    return typeof record.slug === 'string' && record.slug.length > 0;
};

/** Published pages without body, intro, hero image, or builder blocks are treated as not found. */
export const hasSubstantivePublicContent = (content: Content): boolean => {
    const body = (content.body ?? content.content ?? '').trim();
    const intro = (content.intro ?? content.excerpt ?? '').trim();
    const image = (content.featured_image ?? '').trim();
    const meta = (content.meta || {}) as Record<string, unknown>;
    const blocks = meta.builder_blocks;
    const hasBlocks = Array.isArray(blocks) && blocks.length > 0;

    return body.length > 0 || intro.length > 0 || image.length > 0 || hasBlocks;
};

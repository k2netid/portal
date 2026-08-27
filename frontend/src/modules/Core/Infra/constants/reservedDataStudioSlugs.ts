/** Keep in sync with Modules\Core\System\Models\ContentType::RESERVED_SLUGS */
export const RESERVED_DATA_STUDIO_SLUGS = [
    'post', 'posts', 'page', 'pages', 'content', 'contents',
    'category', 'categories', 'tag', 'tags',
    'media', 'comment', 'comments',
    'member', 'members', 'user', 'users',
    'form', 'forms', 'mail', 'newsletter',
    'site', 'sites',
] as const;

export function isReservedDataStudioSlug(slug: string): boolean {
    return (RESERVED_DATA_STUDIO_SLUGS as readonly string[]).includes(slug.trim().toLowerCase());
}

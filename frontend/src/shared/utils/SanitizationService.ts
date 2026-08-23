import DOMPurify from 'dompurify';

/**
 * Shared service for HTML sanitization to prevent XSS.
 * Used by both the visual builder and the public content renderer.
 */
const SanitizationService = {
    sanitize(html: string): string {
        if (!html || typeof html !== 'string') {
            return html;
        }

        return DOMPurify.sanitize(html, {
            ADD_TAGS: ['iframe', 'embed', 'object', 'svg', 'path', 'circle', 'rect'],
            ADD_ATTR: [
                'target',
                'allowfullscreen',
                'frameborder',
                'scrolling',
                'd',
                'fill',
                'stroke',
                'viewBox',
                'rel',
            ],
            USE_PROFILES: { html: true, svg: true },
        });
    },

    sanitizeObject<T extends Record<string, unknown>>(obj: T, htmlFields: string[] = []): T {
        const result = { ...obj };

        for (const [key, value] of Object.entries(result)) {
            if (typeof value === 'string' && (htmlFields.includes(key) || key.endsWith('_html') || key === 'content' || key === 'html')) {
                (result as Record<string, unknown>)[key] = this.sanitize(value);
            } else if (value && typeof value === 'object' && !Array.isArray(value)) {
                (result as Record<string, unknown>)[key] = this.sanitizeObject(value as Record<string, unknown>, htmlFields);
            }
        }

        return result;
    },
};

export default SanitizationService;

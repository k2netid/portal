import DOMPurify from 'dompurify'

/**
 * Shared service for HTML sanitization to prevent XSS.
 * Used by both the visual builder and the public content renderer.
 */
const SanitizationService = {
    /**
     * Sanitize HTML content
     * @param html - The HTML string to sanitize
     * @returns Sanitized HTML
     */
    sanitize(html: string): string {
        if (!html || typeof html !== 'string') return html

        // Configure DOMPurify to allow specific tags and attributes
        // that are necessary for the CMS but generally considered safe.
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
                'rel'
            ],
            USE_PROFILES: { html: true, svg: true }
        })
    },

    /**
     * Deeply sanitize an object's string values (e.g., settings)
     * @param obj - Object to sanitize
     * @param htmlFields - Keys that should be treated as HTML and sanitized
     * @returns Sanitized object
     */
    sanitizeObject(obj: unknown, htmlFields: string[] = ['content', 'text', 'html', 'body', 'caption']): unknown {
        if (!obj || typeof obj !== 'object') return obj

        if (Array.isArray(obj)) {
            return obj.map(item => this.sanitizeObject(item, htmlFields))
        }

        const newObj: Record<string, unknown> = {}

        for (const [key, value] of Object.entries(obj as Record<string, unknown>)) {
            if (typeof value === 'string' && htmlFields.includes(key)) {
                newObj[key] = this.sanitize(value)
            } else if (typeof value === 'object' && value !== null) {
                newObj[key] = this.sanitizeObject(value, htmlFields)
            } else {
                newObj[key] = value
            }
        }

        return newObj
    }
}

export default SanitizationService

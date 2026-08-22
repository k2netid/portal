import DOMPurify from 'dompurify';
import type { Config } from 'dompurify';

/**
 * Default configuration for DOMPurify to prevent XSS while allowing basic formatting.
 */
export const DEFAULT_SANITIZE_CONFIG: Config = {
    ALLOWED_TAGS: [
        'address', 'article', 'aside', 'footer', 'header', 'h1', 'h2', 'h3', 'h4',
        'h5', 'h6', 'hgroup', 'main', 'nav', 'section', 'blockquote', 'dd', 'div',
        'dl', 'dt', 'figcaption', 'figure', 'hr', 'li', 'main', 'ol', 'p', 'pre',
        'ul', 'a', 'abbr', 'b', 'bdi', 'bdo', 'br', 'cite', 'code', 'data', 'dfn',
        'em', 'i', 'kbd', 'mark', 'q', 'rb', 'rp', 'rt', 'rtc', 'ruby', 's', 'samp',
        'small', 'span', 'strong', 'sub', 'sup', 'time', 'u', 'var', 'wbr', 'caption',
        'col', 'colgroup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr',
        'img', 'audio', 'video', 'source', 'track'
    ],
    ALLOWED_ATTR: [
        'class', 'id', 'style', 'href', 'title', 'alt', 'src', 'width', 'height',
        'controls', 'autoplay', 'loop', 'muted', 'poster', 'preload', 'type',
        'target', 'rel'
    ],
};

/**
 * Extended configuration specifically for CMS content that may include embeds.
 */
export const CMS_SANITIZE_CONFIG: Config = {
    ...DEFAULT_SANITIZE_CONFIG,
    ADD_TAGS: ['iframe', 'embed'],
    ADD_ATTR: [
        'allow', 'allowfullscreen', 'frameborder', 'scrolling', 
        'target', 'width', 'height', 'src', 'type', 'style', 'class'
    ],
    // Safety: ensure no scripting can sneak in through these tags
    FORBID_TAGS: ['script'],
    FORBID_ATTR: ['onerror', 'onload', 'onclick', 'onmouseover', 'onfocus']
};

/**
 * Internal helper for sanitization
 */
function sanitize(html: string | undefined | null, config: Config = DEFAULT_SANITIZE_CONFIG): string {
    if (!html) return '';
    // Ensure we return a string even if Trusted Types are enabled
    const result = DOMPurify.sanitize(html, config);
    return typeof result === 'string' ? result : String(result);
}

/**
 * Sanitizes HTML content for general use.
 */
export const sanitizeHtml = (html: string | undefined | null): string => sanitize(html);

/**
 * Sanitizes HTML content specifically for CMS/Editor content (allows iframes).
 */
export const sanitizeCmsHtml = (html: string | undefined | null): string => sanitize(html, CMS_SANITIZE_CONFIG);

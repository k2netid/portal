/**
 * Logic to evaluate if a block should be visible based on settings
 */
import type { BlockInstance } from '../types/builder';

export interface VisibilityRule {
    type: string;
    condition: string;
    value: unknown;
    key?: string;
}

export interface RenderingContext {
    user?: {
        roles?: Array<{ name: string; slug: string }>;
        [key: string]: unknown;
    };
    post?: {
        title?: string;
        excerpt?: string;
        body?: string;
        published_at?: string;
        featured_image?: string;
        post_type: string;
        categories?: Array<{ id: number | string; name: string; slug: string }>;
        category?: { id: number | string; name: string; slug: string };
        tags?: Array<{ id: number | string; name: string; slug: string }>;
        author?: { id: number | string; name: string; user_nicename: string };
        meta?: Record<string, unknown>;
        [key: string]: unknown;
    };
    site?: {
        name: string;
        tagline: string;
        [key: string]: unknown;
    };
    product?: {
        name?: string;
        sku?: string;
        price?: string | number;
        regular_price?: string | number;
        currency?: string;
        description?: string;
        rating?: string | number;
        [key: string]: unknown;
    };
    data?: Record<string, unknown>;
    [key: string]: unknown;
}

export class ConditionEvaluator {
    /**
     * Evaluate if a block should be shown
     * @param block The block object containing settings and visibility rules
     * @param context App context (user, page, etc.)
     * @returns 
     */
    static evaluate(block: BlockInstance, context: RenderingContext = {}): boolean {
        // If no visibility settings, show by default
        const rules = (block.settings?.visibility_rules || block.settings?.conditions) as VisibilityRule[];
        if (!rules || !Array.isArray(rules) || rules.length === 0) {
            return true;
        }

        // Logic mode: 'all' (AND) or 'any' (OR)
        const mode = block.settings.visibility_mode || 'all';

        const results = rules.map(rule => this.evaluateRule(rule, context));

        if (mode === 'any') {
            return results.some(r => r === true);
        }

        return results.every(r => r === true);
    }

    static evaluateRule(rule: VisibilityRule, context: RenderingContext): boolean {
        const { type, condition, value, key } = rule;
        const user = context?.user || null;
        const post = context?.post || null;

        switch (type) {
            case 'auth':
                if (condition === 'logged_in') return !!user;
                if (condition === 'guest') return !user;
                return true;

            case 'role': {
                if (!user || !user.roles) return condition === 'is_not';
                const hasRole = user.roles.some(r => r.name === value || r.slug === value);
                return condition === 'is' ? hasRole : !hasRole;
            }

            case 'post_type':
                if (!post) return false;
                return condition === 'is' ? post.post_type === value : post.post_type !== value;

            case 'post_category': {
                if (!post || !post.categories) return false;
                const hasCat = post.categories.some(c => c.slug === value || c.name === value || String(c.id) === String(value));
                return condition === 'is' ? hasCat : !hasCat;
            }

            case 'post_tag': {
                if (!post || !post.tags) return false;
                const hasTag = post.tags.some(t => t.slug === value || t.name === value || String(t.id) === String(value));
                return condition === 'is' ? hasTag : !hasTag;
            }

            case 'author': {
                if (!post || !post.author) return false;
                const isAuthor = String(post.author.id) === String(value) || post.author.user_nicename === value;
                return condition === 'is' ? isAuthor : !isAuthor;
            }

            case 'date_time': {
                const now = new Date();
                if (key === 'day_of_week') {
                    const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                    const currentDay = days[now.getDay()];
                    return condition === 'is' ? currentDay === value : currentDay !== value;
                }
                const targetDate = new Date(value as string | number | Date);
                if (condition === 'before') return now < targetDate;
                if (condition === 'after') return now > targetDate;
                return true;
            }

            case 'date_archive':
                return window.location.pathname.includes('/archive/') || !!window.location.pathname.match(/\/\d{4}\/\d{2}/);

            case 'tag_page':
                return window.location.pathname.includes('/tag/');
            case 'category_page':
                return window.location.pathname.includes('/category/');
            case 'search_results':
                return new URLSearchParams(window.location.search).has('s');

            case 'post_meta': {
                if (!post || !post.meta || !key) return false;
                const metaValue = post.meta[key];
                if (condition === 'is') return String(metaValue) === String(value);
                if (condition === 'is_not') return String(metaValue) !== String(value);
                if (condition === 'contains') return String(metaValue).includes(String(value));
                return true;
            }

            case 'url_param': {
                if (!key) return true;
                const params = new URLSearchParams(window.location.search);
                const paramValue = params.get(key);
                if (condition === 'exists') return params.has(key);
                if (condition === 'is') return paramValue === String(value);
                return true;
            }

            case 'cookie': {
                if (!key) return true;
                const cookies = document.cookie.split(';').reduce((acc: Record<string, string>, c) => {
                    const [k, v] = c.split('=').map(s => s.trim());
                    acc[k] = v;
                    return acc;
                }, {});
                const cookieValue = cookies[key];
                if (condition === 'exists') return key in cookies;
                if (condition === 'is') return cookieValue === String(value);
                return true;
            }

            case 'browser': {
                const ua = navigator.userAgent.toLowerCase();
                if (value === 'chrome') return ua.includes('chrome') && !ua.includes('edg');
                if (value === 'safari') return ua.includes('safari') && !ua.includes('chrome');
                if (value === 'firefox') return ua.includes('firefox');
                if (value === 'edge') return ua.includes('edg');
                return true;
            }

            case 'os': {
                const ua = navigator.userAgent.toLowerCase();
                const pf = (navigator as unknown as { platform: string }).platform?.toLowerCase() || '';
                if (value === 'mac') return pf.includes('mac');
                if (value === 'windows') return pf.includes('win');
                if (value === 'linux') return pf.includes('linux');
                if (value === 'ios') return /iphone|ipad|ipod/.test(ua);
                if (value === 'android') return /android/.test(ua);
                return true;
            }

            case 'form_field': {
                if (!context.data || !key) return true;
                const fieldValue = context.data[key];

                if (condition === 'is') return String(fieldValue) === String(value);
                if (condition === 'is_not') return String(fieldValue) !== String(value);
                if (condition === 'contains') return String(fieldValue).includes(String(value));
                if (condition === 'empty') return !fieldValue || (fieldValue as { length: number }).length === 0;
                if (condition === 'not_empty') return !!fieldValue && (fieldValue as { length: number }).length > 0;

                // Numeric comparisons if value is number
                const numField = Number(fieldValue);
                const numValue = Number(value);
                if (!isNaN(numField) && !isNaN(numValue)) {
                    if (condition === 'gt') return numField > numValue;
                    if (condition === 'lt') return numField < numValue;
                    if (condition === 'gte') return numField >= numValue;
                    if (condition === 'lte') return numField <= numValue;
                }

                return true;
            }

            default:
                return true;
        }
    }
}

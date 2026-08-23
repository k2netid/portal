import { logger } from '@/shared/utils/logger';
import api from '@/engine/api/client';

interface DynamicContext {
    contentId?: string | number
    loopItem?: Record<string, unknown>
}

interface CacheEntry {
    value: string
    timestamp: number
}

// Cache for resolved values
const resolvedCache = new Map<string, CacheEntry>()
const CACHE_TTL = 60000 // 1 minute cache

/**
 * Check if a value is a dynamic tag
 * @param {string} value - The value to check
 * @returns {boolean}
 */
export function isDynamicValue(value: string): boolean {
    return typeof value === 'string' && value.startsWith('@dynamic:')
}

/**
 * Extract the tag from a dynamic value
 * @param {string} value - The dynamic value like "@dynamic:{{post_title}}"
 * @returns {string} The tag like "{{post_title}}"
 */
export function extractTag(value: string): string {
    if (!isDynamicValue(value)) return value
    return value.replace('@dynamic:', '')
}

/**
 * Get a human-readable label from a tag
 * @param {string} tag - The tag like "{{post_title}}" or "post_title"
 * @returns {string} The label like "Post Title"
 */
export function getTagLabel(tag: string): string {
    const cleanTag = tag.replace(/[{}]/g, '').trim()
    return cleanTag.split('_').map((word: string) =>
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ')
}

/**
 * Resolve a single dynamic tag to its value
 * @param {string} tag - The tag to resolve like "{{site_title}}"
 * @param {Object} context - Context for resolution (contentId, loopItem)
 * @returns {Promise<string>} The resolved value
 */
export async function resolveTag(tag: string, context: DynamicContext = {}): Promise<string> {
    const cacheKey = `${tag}:${context.contentId || ''}:${JSON.stringify(context.loopItem || {})}`

    // Check cache
    const cached = resolvedCache.get(cacheKey)
    if (cached && Date.now() - cached.timestamp < CACHE_TTL) {
        return cached.value
    }

    try {
        const response = await api.post('/admin/ja/builder/resolve-dynamic', {
            tags: [tag],
            content_id: context.contentId,
            loop_item: context.loopItem
        })

        const data = (response.data?.data || {}) as Record<string, string>
        const value = data[tag] || tag

        // Cache the result
        resolvedCache.set(cacheKey, {
            value,
            timestamp: Date.now()
        })

        return value
    } catch (error: unknown) {
        logger.error('Failed to resolve dynamic tag:', error)
        return tag // Return the tag itself as fallback
    }
}

/**
 * Resolve a dynamic value (full @dynamic:{{tag}} format)
 * @param {string} value - The value which may be a dynamic tag
 * @param {Object} context - Context for resolution
 * @returns {Promise<string>} The resolved value or original if not dynamic
 */
export async function resolveDynamicValue(value: string, context: DynamicContext = {}): Promise<string> {
    if (!isDynamicValue(value)) {
        return value
    }

    const tag = extractTag(value)
    return await resolveTag(tag, context)
}

/**
 * Resolve multiple tags at once (more efficient)
 * @param {string[]} tags - Array of tags to resolve
 * @param {Object} context - Context for resolution
 * @returns {Promise<Object>} Map of tag -> resolved value
 */
export async function resolveTags(tags: string[], context: DynamicContext = {}): Promise<Record<string, string>> {
    if (!tags || tags.length === 0) return {}

    // Filter out cached ones
    const uncachedTags: string[] = []
    const results: Record<string, string> = {}

    for (const tag of tags) {
        const cacheKey = `${tag}:${context.contentId || ''}:${JSON.stringify(context.loopItem || {})}`
        const cached = resolvedCache.get(cacheKey)
        if (cached && Date.now() - cached.timestamp < CACHE_TTL) {
            results[tag] = cached.value
        } else {
            uncachedTags.push(tag)
        }
    }

    if (uncachedTags.length === 0) {
        return results
    }

    try {
        const response = await api.post('/admin/ja/builder/resolve-dynamic', {
            tags: uncachedTags,
            content_id: context.contentId,
            loop_item: context.loopItem
        })

        const data = (response.data?.data || {}) as Record<string, string>

        for (const tag of uncachedTags) {
            const value = data[tag] || tag
            results[tag] = value

            const cacheKey = `${tag}:${context.contentId || ''}:${JSON.stringify(context.loopItem || {})}`
            resolvedCache.set(cacheKey, {
                value,
                timestamp: Date.now()
            })
        }

        return results
    } catch (error: unknown) {
        logger.error('Failed to resolve dynamic tags:', error)
        // Return tags as-is
        for (const tag of uncachedTags) {
            results[tag] = tag
        }
        return results
    }
}

/**
 * Clear the resolution cache
 */
export function clearCache() {
    resolvedCache.clear()
}

export default {
    isDynamicValue,
    extractTag,
    getTagLabel,
    resolveTag,
    resolveDynamicValue,
    resolveTags,
    clearCache
}

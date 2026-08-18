/**
 * Content form validation schemas using Zod with i18n support
 */
import { z } from 'zod';

/**
 * Helper to create translatable error message
 */
const t = (key: string, params: Record<string, unknown> = {}) => JSON.stringify({ key, params });

/**
 * Content form schema
 */
export const contentSchema = z.object({
    title: z.string()
        .min(1, t('common.validation.required', { field: 'Title' }))
        .max(255, t('common.validation.max', { field: 'Title', max: 255 })),
    slug: z.string()
        .regex(/^[a-z0-9]+(?:-[a-z0-9]+)*$/, t('common.validation.slug'))
        .optional()
        .or(z.literal('')),
    body: z.string().optional(),
    excerpt: z.string()
        .max(500, t('common.validation.max', { field: 'Excerpt', max: 500 }))
        .optional()
        .or(z.literal('')),
    intro: z.string()
        .max(500, t('common.validation.max', { field: 'Intro', max: 500 }))
        .optional()
        .or(z.literal('')),
    status: z.enum(['draft', 'published', 'scheduled', 'archived']).optional(),
    type: z.string().optional(),
    category_id: z.union([z.number(), z.string()]).nullable().optional(),
    featured_image: z.string().nullable().optional(),
    featured_image_title: z.string()
        .max(120, t('common.validation.max', { field: 'Featured Image Title', max: 120 }))
        .optional()
        .or(z.literal('')),
    featured_image_caption: z.string()
        .max(255, t('common.validation.max', { field: 'Featured Image Caption', max: 255 }))
        .optional()
        .or(z.literal('')),
    featured_image_position: z.enum(['hero', 'inline-top', 'full-bleed']).optional(),
    meta_title: z.string()
        .max(60, t('common.validation.max', { field: 'Meta Title', max: 60 }))
        .optional()
        .or(z.literal('')),
    meta_description: z.string()
        .max(160, t('common.validation.max', { field: 'Meta Description', max: 160 }))
        .optional()
        .or(z.literal('')),
});

/**
 * Category form schema
 */
export const categorySchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    slug: z.string()
        .regex(/^[a-z0-9]+(?:-[a-z0-9]+)*$/, t('common.validation.slug'))
        .optional()
        .or(z.literal('')),
    description: z.string()
        .max(1000, t('common.validation.max', { field: 'Description', max: 1000 }))
        .optional()
        .or(z.literal('')),
    parent_id: z.union([z.number(), z.string()]).nullable().optional(),
    image: z.string().nullable().optional(),
    is_active: z.boolean().optional(),
    sort_order: z.number().optional()
});

/**
 * Tag form schema
 */
export const tagSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    slug: z.string()
        .regex(/^[a-z0-9]+(?:-[a-z0-9]+)*$/, t('common.validation.slug'))
        .optional()
        .or(z.literal('')),
    description: z.string()
        .max(500, t('common.validation.max', { field: 'Description', max: 500 }))
        .optional()
        .or(z.literal('')),
});

/**
 * Move category schema
 */
export const moveCategorySchema = z.object({
    parent_id: z.union([z.number(), z.string()]).nullable().optional(),
});

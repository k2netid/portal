/**
 * Common form validation schemas using Zod with i18n support
 */
import { z } from 'zod';
import { userModelSchema } from './models/core';

/**
 * Helper to create translatable error message
 */
const t = (key: string, params: Record<string, unknown> = {}) => JSON.stringify({ key, params });

/**
 * Role form schema
 */
export const roleSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    permissions: z.array(z.string()).default([]),
});

/**
 * Scheduled task form schema
 */
export const taskSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    command: z.string()
        .min(1, t('common.validation.required', { field: 'Command' })),
    schedule: z.string()
        .min(1, t('common.validation.required', { field: 'Schedule' })),
    description: z.string().optional().or(z.literal('')),
    is_active: z.boolean().optional(),
});

/**
 * Folder creation schema
 */
export const folderSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 }))
        .regex(/^[a-zA-Z0-9_\-\s]+$/, t('common.validation.alphanumeric', { field: 'Name' })),
});

/**
 * Email template form schema
 */
export const emailTemplateSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    subject: z.string()
        .min(1, t('common.validation.required', { field: 'Subject' }))
        .max(255, t('common.validation.max', { field: 'Subject', max: 255 })),
    content: z.string()
        .min(1, t('common.validation.required', { field: 'Content' })),
    type: z.string().optional().or(z.literal('')),
    is_active: z.boolean().optional(),
});

/**
 * Language schema
 */
export const languageSchema = z.object({
    code: z.string().min(2, t('common.validation.min', { field: 'Code', min: 2 })),
    name: z.string().min(1, t('common.validation.required', { field: 'Name' })),
    create_from_template: z.boolean().default(true),
});

/**
 * Auth response schema for login/register
 */
export const authResponseSchema = z.object({
    user: userModelSchema.optional(),
    token: z.string().optional(),
    requires_two_factor: z.boolean().optional(),
    user_id: z.union([z.string(), z.number()]).optional(),
    message: z.string().optional(),
    redirect_to: z.string().optional(),
});

/**
 * Category form schema (Library / Publishing)
 */
export const categorySchema = z.object({
    name: z.string().min(1, t('common.validation.required', { field: 'Name' })),
    slug: z.string().min(1, t('common.validation.required', { field: 'Slug' })),
    description: z.string().optional().nullable(),
    image: z.string().optional().nullable(),
    parent_id: z.union([z.string(), z.number()]).nullable().optional(),
    is_active: z.boolean().default(true),
    sort_order: z.number().default(0),
});

export const moveCategorySchema = z.object({
    parent_id: z.union([z.string(), z.number()]).nullable().optional(),
});

/**
 * Tag form schema (Library)
 */
export const tagSchema = z.object({
    name: z.string().min(1, t('common.validation.required', { field: 'Name' })),
    slug: z.string().optional(),
    description: z.string().optional().nullable(),
});

/**
 * Content template form schema (Publishing)
 */
export const contentTemplateSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    title: z.string().optional().or(z.literal('')),
    body: z.string().optional().or(z.literal('')),
    excerpt: z.string().optional().or(z.literal('')),
    description: z.string().optional().or(z.literal('')),
    type: z.enum(['post', 'page', 'custom'], { message: t('common.validation.required', { field: 'Type' }) }),
    is_active: z.boolean().optional(),
});

/**
 * Public comment form schema (Publishing)
 */
export const commentSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    email: z.string()
        .min(1, t('common.validation.required', { field: 'Email' }))
        .email(t('common.validation.email')),
    body: z.string()
        .min(1, t('common.validation.required', { field: 'Comment' }))
        .max(5000, t('common.validation.max', { field: 'Comment', max: 5000 })),
});


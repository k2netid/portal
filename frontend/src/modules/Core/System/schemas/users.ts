/**
 * User form validation schemas using Zod with i18n support
 */
import { z } from 'zod';

/**
 * Helper to create translatable error message
 */
const t = (key: string, params: Record<string, unknown> = {}) => JSON.stringify({ key, params });

/**
 * Create user form schema
 */
export const createUserSchema = z.object({
    name: z.string()
        .min(2, t('common.validation.min', { field: 'Name', min: 2 }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    email: z.string()
        .min(1, t('common.validation.required', { field: 'Email' }))
        .email(t('common.validation.email')),
    password: z.string()
        .min(8, t('common.validation.min', { field: 'Password', min: 8 })),
    password_confirmation: z.string()
        .min(1, t('common.validation.required', { field: 'Confirm Password' })),
    phone: z.string().optional().nullable(),
    bio: z.string().optional().nullable(),
    website: z.string().optional().nullable(),
    location: z.string().optional().nullable(),
    avatar: z.string().optional().nullable(),
    roles: z.array(z.string()).min(1, t('system.users.messages.roleRequired')),
}).refine(data => data.password === data.password_confirmation, {
    message: t('common.validation.confirmed'),
    path: ['password_confirmation'],
});

/**
 * Edit user form schema (password optional)
 */
export const editUserSchema = z.object({
    name: z.string()
        .min(2, t('common.validation.min', { field: 'Name', min: 2 }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    email: z.string()
        .min(1, t('common.validation.required', { field: 'Email' }))
        .email(t('common.validation.email')),
    password: z.string()
        .min(8, t('common.validation.min', { field: 'Password', min: 8 }))
        .optional()
        .or(z.literal('')),
    password_confirmation: z.string().optional().or(z.literal('')),
    phone: z.string().optional().nullable(),
    bio: z.string().optional().nullable(),
    website: z.string().optional().nullable(),
    location: z.string().optional().nullable(),
    avatar: z.string().optional().nullable(),
    roles: z.array(z.string()).min(1, t('system.users.messages.roleRequired')),
}).refine(data => {
    // Only validate confirmation if password is provided
    if (data.password && data.password.length > 0) {
        return data.password === data.password_confirmation;
    }
    return true;
}, {
    message: t('common.validation.confirmed'),
    path: ['password_confirmation'],
});

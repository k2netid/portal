/**
 * Authentication form validation schemas using Zod with i18n support
 * 
 * These schemas use translation keys that are resolved at runtime
 * by useFormValidation.js using i18n's `t()` function.
 * 
 * Translation key format: { key: 'translation.key', params: {} }
 */
import { z } from 'zod';

/**
 * Helper to create translatable error message
 * This creates an object that useFormValidation will translate
 */
const t = (key: string, params: Record<string, unknown> = {}) => JSON.stringify({ key, params });

/**
 * Login form schema
 */
export const loginSchema = z.object({
    email: z.string()
        .min(1, t('common.validation.required', { field: 'Email' }))
        .email(t('common.validation.email')),
    password: z.string()
        .min(1, t('common.validation.required', { field: 'Password' })),
});

/**
 * Registration form schema
 */
export const registerSchema = z.object({
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
}).refine(data => data.password === data.password_confirmation, {
    message: t('common.validation.confirmed'),
    path: ['password_confirmation'],
});

/**
 * Forgot password form schema
 */
export const forgotPasswordSchema = z.object({
    email: z.string()
        .min(1, t('common.validation.required', { field: 'Email' }))
        .email(t('common.validation.email')),
});

/**
 * Reset password form schema
 */
export const resetPasswordSchema = z.object({
    email: z.string()
        .min(1, t('common.validation.required', { field: 'Email' }))
        .email(t('common.validation.email')),
    password: z.string()
        .min(8, t('common.validation.min', { field: 'Password', min: 8 })),
    password_confirmation: z.string()
        .min(1, t('common.validation.required', { field: 'Confirm Password' })),
    token: z.string().min(1),
}).refine(data => data.password === data.password_confirmation, {
    message: t('common.validation.confirmed'),
    path: ['password_confirmation'],
});

/**
 * Login response schema (wrapping user model)
 */
import { userModelSchema } from '@/shared/schemas';
export const authResponseSchema = z.object({
    user: userModelSchema,
    redirect_to: z.string().optional()
});

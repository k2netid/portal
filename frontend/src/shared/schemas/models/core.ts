import { z } from 'zod';

/**
 * Permission Schema
 */
export const permissionModelSchema = z.object({
    id: z.union([z.string().uuid(), z.number(), z.string()]),
    name: z.string(),
    description: z.string().optional().nullable(),
});

/**
 * Role Schema
 */
export const roleModelSchema = z.object({
    id: z.union([z.string().uuid(), z.number(), z.string()]),
    name: z.string(),
    permissions: z.array(permissionModelSchema).optional(),
    users_count: z.number().optional(),
});

/**
 * User Schema
 */
export const userModelSchema = z.object({
    id: z.string().uuid(),
    name: z.string(),
    email: z.string().email(),
    avatar: z.union([
        z.string(),
        z.object({
            url: z.string().optional(),
            path: z.string().optional()
        }),
        z.null()
    ]).optional(),
    roles: z.array(roleModelSchema).optional(),
    permissions: z.array(permissionModelSchema).optional(),
    email_verified_at: z.string().nullable().optional(),
    last_login_at: z.string().nullable().optional(),
    deleted_at: z.string().nullable().optional(),
    phone: z.string().nullable().optional(),
    bio: z.string().nullable().optional(),
    website: z.string().nullable().optional(),
    location: z.string().nullable().optional(),
    created_at: z.string().optional(),
    updated_at: z.string().optional(),
});

/**
 * Type inference
 */
export type UserModel = z.infer<typeof userModelSchema>;
export type RoleModel = z.infer<typeof roleModelSchema>;
export type PermissionModel = z.infer<typeof permissionModelSchema>;

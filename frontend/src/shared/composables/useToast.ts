import { useI18n } from 'vue-i18n';
import toast from '@/shared/services/toastService';
import { isRequestAborted } from '@/shared/utils/axiosErrors';

interface ToastSuccessHandlers {
    create: (item?: string) => void;
    update: (item?: string) => void;
    delete: (item?: string) => void;
    save: () => void;
    restore: (item?: string) => void;
    duplicate: (item?: string) => void;
    action: (message?: string) => void;
    approve: (item?: string) => void;
    reject: (item?: string) => void;
    markSpam: (item?: string) => void;
    upload: () => void;
    move: () => void;
    folderCreated: () => void;
    urlCopied: () => void;
    createFromTemplate: () => void;
    default: (message: string) => void;
}

interface ToastErrorHandlers {
    create: (error: unknown, item?: string) => void;
    update: (error: unknown, item?: string) => void;
    delete: (error: unknown, item?: string) => void;
    load: (error: unknown) => void;
    action: (error: unknown) => void;
    fromResponse: (error: unknown) => void;
    validation: (message?: string) => void;
    permission: () => void;
    fileTooLarge: () => void;
    templateCreateContent: (error: unknown) => void;
    default: (message: string) => void;
}

/**
 * Unified toast composable for application-wide notifications.
 * Merges logic from previous useContentToast and useMediaToast.
 */
export function useToast() {
    const { t } = useI18n();

    // Map common backend English messages to translation keys
    // Includes general, content-specific, and media-specific errors
    const messageMap: Record<string, string> = {
        // Generic & Content Errors
        'Validation failed. Please check your input.': 'common.messages.error.validation',
        'The given data was invalid.': 'common.messages.error.validation',
        'Unauthorized': 'common.messages.error.unauthorized',
        'Forbidden': 'common.messages.error.forbidden',
        'Not Found': 'common.messages.error.notFound',
        'Server Error': 'common.messages.error.server',

        // Media Specific Errors (all variations)
        'You do not have permission to delete media': 'media.errors.noPermissionDelete',
        'You do not have permission to update media': 'media.errors.noPermissionUpdate',
        'You do not have permission to restore media': 'media.errors.noPermissionUpdate',
        'You do not have permission to permanently delete media': 'media.errors.noPermissionDelete',
        'You do not have permission to edit media': 'media.errors.noPermissionUpdate',
        'You do not have permission to view this media': 'media.errors.noPermissionUpdate',
        'You do not have permission to update this media': 'media.errors.noPermissionUpdate',
        'You do not have permission to delete this media': 'media.errors.noPermissionDelete',
        'You do not have permission to restore this media': 'media.errors.noPermissionUpdate',
        'You do not have permission to manage this media': 'media.errors.noPermissionManage',
        'You cannot update global media': 'media.errors.cannotUpdateGlobal',
        'You cannot delete global media': 'media.errors.cannotDeleteGlobal',
        'You cannot restore global media': 'media.errors.cannotDeleteGlobal',
        'Media is currently in use': 'media.errors.mediaInUse',
    };

    /**
     * Translates backend messages using the messageMap and pattern matching
     */
    const translateMessage = (message: string): string => {
        // First, try exact match in messageMap
        if (messageMap[message]) {
            return t(messageMap[message]);
        }

        // Pattern 1: "You do not have permission to {action} {resource}"
        const permissionPattern1 = /^You do not have permission to (.+)$/;
        const match1 = message.match(permissionPattern1);
        if (match1) {
            const action = match1[1];
            return t('common.messages.error.noPermission', { action });
        }

        // Pattern 2: "You cannot {action} global {resource}"
        const globalPattern = /^You cannot (.+) global (.+)$/;
        const match2 = message.match(globalPattern);
        if (match2) {
            const action = match2[1];
            const resource = match2[2];
            return t('common.messages.error.cannotActionGlobal', { action, resource });
        }

        // Pattern 3: "You can only {action}"
        const onlyPattern = /^You can only (.+)$/;
        const match3 = message.match(onlyPattern);
        if (match3) {
            const action = match3[1];
            return t('common.messages.error.canOnlyAction', { action });
        }

        // SUCCESS MESSAGE PATTERNS

        // Pattern 4: "{Resource} created successfully"
        const createdPattern = /^(.+) created successfully$/;
        const match4 = message.match(createdPattern);
        if (match4) {
            const resource = match4[1];
            return t('common.messages.success.created', { item: resource });
        }

        // Pattern 5: "{Resource} updated successfully"
        const updatedPattern = /^(.+) updated successfully$/;
        const match5 = message.match(updatedPattern);
        if (match5) {
            const resource = match5[1];
            return t('common.messages.success.updated', { item: resource });
        }

        // Pattern 6: "{Resource} deleted successfully"
        const deletedPattern = /^(.+) deleted successfully$/;
        const match6 = message.match(deletedPattern);
        if (match6) {
            const resource = match6[1];
            return t('common.messages.success.deleted', { item: resource });
        }

        // Pattern 7: "{Resource} retrieved successfully"
        const retrievedPattern = /^(.+) retrieved successfully$/;
        const match7 = message.match(retrievedPattern);
        if (match7) {
            const resource = match7[1];
            return t('common.messages.success.retrieved', { item: resource });
        }

        // Pattern 8: "{Resource} {action} successfully" (generic)
        const genericSuccessPattern = /^(.+) (.+) successfully$/;
        const match8 = message.match(genericSuccessPattern);
        if (match8) {
            const resource = match8[1];
            const action = match8[2];
            return t('common.messages.success.actionSuccess', { item: resource, action });
        }

        // If no pattern matches, return original message
        return message;
    };

    /**
     * Extracts message from error object or returns default
     */
    const getErrorMessage = (error: unknown, defaultKey?: string, params?: Record<string, unknown>): string => {
        const err = error as { response?: { status?: number; data?: { message?: string } }; message?: string };
        // If it's a validation error (422), always use the validation translation
        if (err?.response?.status === 422) {
            return t('common.messages.error.validation');
        }

        const message = err?.response?.data?.message || err?.message || (defaultKey ? t(defaultKey, params || {}) : '');
        return translateMessage(message);
    };

    return {
        // Access raw toast service
        service: toast,

        success: {
            create: (item = 'Data') => toast.success(t('common.messages.toast.success'), t('common.messages.success.created', { item })),
            update: (item = 'Data') => toast.success(t('common.messages.toast.success'), t('common.messages.success.updated', { item })),
            delete: (item = 'Data') => toast.success(t('common.messages.toast.success'), t('common.messages.success.deleted', { item })),
            save: () => toast.success(t('common.messages.toast.success'), t('common.messages.success.saved')),
            restore: (item = 'Data') => toast.success(t('common.messages.toast.success'), t('common.messages.success.updated', { item: `${item} restored` })),
            duplicate: (item = 'Data') => toast.success(t('common.messages.toast.success'), t('common.messages.success.created', { item: `${item} duplicate` })),
            action: (message) => toast.success(t('common.messages.toast.success'), message || t('common.messages.success.default')),

            // Generic status updates
            approve: (item = 'Item') => toast.success(t('common.messages.toast.success'), t('common.messages.success.approved', { item })),
            reject: (item = 'Item') => toast.success(t('common.messages.toast.success'), t('common.messages.success.rejected', { item })),
            markSpam: (item = 'Item') => toast.success(t('common.messages.toast.success'), t('common.messages.success.marked_spam', { item })),

            // Media Specific
            upload: () => toast.success(t('common.messages.toast.success'), t('media.toast.uploadSuccess') || 'File uploaded successfully'),
            move: () => toast.success(t('common.messages.toast.success'), t('media.toast.moveSuccess') || 'Item moved successfully'),
            folderCreated: () => toast.success(t('common.messages.toast.success'), t('media.toast.folderCreatedSuccess') || 'Folder created successfully'),
            urlCopied: () => toast.success(t('common.messages.toast.success'), t('media.toast.urlCopied') || 'URL copied to clipboard'),

            // Content Template Specific
            createFromTemplate: () => toast.success(t('common.messages.toast.success'), t('publishing.content_templates.messages.createContentSuccess') || 'Content created from template'),
            default: (message) => toast.success(t('common.messages.toast.success'), message),
        } as ToastSuccessHandlers,

        error: {
            create: (error, _item = 'Item') => toast.error(t('common.messages.toast.error'), getErrorMessage(error, 'common.messages.error.action')),
            update: (error, _item = 'Item') => toast.error(t('common.messages.toast.error'), getErrorMessage(error, 'common.messages.error.action')),
            delete: (error, item = 'Item') => toast.error(t('common.messages.toast.error'), getErrorMessage(error, 'common.messages.error.deleteFailed', { item })),
            load: (error) => { if (isRequestAborted(error)) return; toast.error(t('common.messages.toast.error'), t('common.messages.error.loadFailed')); },
            action: (error) => toast.error(t('common.messages.toast.error'), getErrorMessage(error, 'common.messages.error.action')),
            fromResponse: (error) => { if (isRequestAborted(error)) return; toast.error(t('common.messages.toast.error'), getErrorMessage(error, 'common.messages.error.action')); },
            validation: (message) => toast.error(t('common.messages.toast.error'), message || t('common.messages.error.validation')),

            // Media Specific
            permission: () => toast.error(t('common.messages.toast.error'), t('media.toast.permissionDenied') || 'Permission denied'),
            fileTooLarge: () => toast.warning(t('common.messages.toast.warning'), t('media.toast.fileTooLarge') || 'File is too large'),

            // Specific Domain Errors
            templateCreateContent: (error) => toast.error(t('common.messages.toast.error'), getErrorMessage(error, 'publishing.content_templates.messages.createError')),
            default: (message) => toast.error(t('common.messages.toast.error'), message),
        } as ToastErrorHandlers,

        warning: (title: string, message: string) => toast.warning(title, message),
        info: (title: string, message: string) => toast.info(title, message),
    };
}

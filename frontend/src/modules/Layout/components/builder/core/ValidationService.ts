import { getSchemaForBlock } from './schemas'
import SanitizationService from '@/shared/utils/SanitizationService'
import type { ModuleSettings } from '@/types/builder'

export interface ValidationResult {
    success: boolean;
    data: ModuleSettings;
    errors: unknown | null;
}

export const ValidationService = {
    /**
     * Validate block settings
     * @param type - Block type
     * @param settings - Settings to validate
     * @returns ValidationResult
     */
    validate(type: string, settings: ModuleSettings): ValidationResult {
        // 1. Sanitize HTML fields first
        const sanitizedSettings = SanitizationService.sanitizeObject(settings)

        // 2. Get schema
        const schema = getSchemaForBlock(type)

        if (!schema) {
            // If no schema defined, assume valid but sanitized
            return { success: true, data: sanitizedSettings as ModuleSettings, errors: null }
        }

        // Use passthrough() to ensure common settings (like design, _label) are not stripped
        // if they are not explicitly defined in the specific block schema.
        const result = schema.passthrough().safeParse(sanitizedSettings)

        if (!result.success) {
            return {
                success: false,
                data: settings,
                errors: result.error.format()
            }
        }

        return {
            success: true,
            data: result.data as ModuleSettings,
            errors: null
        }
    }
}

export default ValidationService

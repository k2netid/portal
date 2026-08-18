import { logger } from '@/shared/utils/logger';
import type { Ref } from 'vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import type { ZodSchema, ZodError } from 'zod';

type ValidationRule<T = Record<string, unknown>> = string | Record<string, unknown> | ((value: unknown, formData: T) => boolean | string);
type ValidatorFunction<T = Record<string, unknown>> = (value: unknown, formData: T) => boolean | string;

/**
 * Form validation composable with Zod schema support and i18n integration
 * Provides both client-side (Zod) and server-side (422) error handling
 */
export function useFormValidation<T extends Record<string, unknown>>(zodSchema: ZodSchema<T> | null = null) {
    const { t } = useI18n();
    const errors: Ref<Record<string, string[] | string>> = ref({});
    const touched: Ref<Record<string, boolean>> = ref({});
    const validating = ref(false);

    /**
     * Built-in validation rules (legacy support)
     */
     
    const rules: Record<string, any> = {
        required: (value: unknown) => {
            const valid = value !== null && value !== undefined && value !== '' &&
                (Array.isArray(value) ? value.length > 0 : true);
            return valid || 'common.validation.required';
        },

        email: (value: unknown) => {
            if (!value) return true;
            const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value));
            return valid || 'common.validation.email';
        },

        min: (min: number) => (value: unknown) => {
            if (!value) return true;
            const valid = String(value).length >= min;
            return valid || t('common.validation.min', { min });
        },

        max: (max: number) => (value: unknown) => {
            if (!value) return true;
            const valid = String(value).length <= max;
            return valid || t('common.validation.max', { max });
        },

        minValue: (min: number) => (value: unknown) => {
            if (!value) return true;
            const valid = Number(value) >= min;
            return valid || t('common.validation.minValue', { min });
        },

        maxValue: (max: number) => (value: unknown) => {
            if (!value) return true;
            const valid = Number(value) <= max;
            return valid || t('common.validation.maxValue', { max });
        },

        numeric: (value: unknown) => {
            if (!value) return true;
            const valid = /^\d+$/.test(String(value));
            return valid || 'common.validation.numeric';
        },

        url: (value: unknown) => {
            if (!value) return true;
            try {
                new URL(String(value));
                return true;
            } catch {
                return 'common.validation.url';
            }
        },

        confirmed: (confirmField: string) => (value: unknown, formData: T) => {
            const valid = value === formData[confirmField as keyof T];
            return valid || 'common.validation.confirmed';
        },

        slug: (value: unknown) => {
            if (!value) return true;
            const valid = /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(String(value));
            return valid || 'common.validation.slug';
        },
    };

    /**
     * Translate a Zod error message
     * Zod messages can be:
     * - JSON string with { key, params } for translation
     * - Plain string (used as-is)
     */
    const translateMessage = (message: string): string => {
        try {
            const parsed = JSON.parse(message);
            if (parsed.key) {
                return t(parsed.key, parsed.params || {});
            }
            return message;
        } catch {
            // Not JSON, return as-is
            return message;
        }
    };

    /**
     * Format Zod errors to match existing error structure
     * Translates error messages using i18n
     * { fieldName: ['translated error message 1', 'error message 2'] }
     */
    const formatZodErrors = (zodError: ZodError): Record<string, string[]> => {
        const formatted: Record<string, string[]> = {};
        for (const issue of zodError.issues) {
            const path = issue.path.join('.') || '_root';
            if (!formatted[path]) formatted[path] = [];
            // Translate the message
            formatted[path].push(translateMessage(issue.message));
        }
        return formatted;
    };

    /**
     * Validate form data using Zod schema
     * @param {T} formData - Form data to validate
     * @returns {boolean} - True if valid, false if validation errors
     */
    const validateWithZod = (formData: T): boolean => {
        if (!zodSchema) {
            logger.warning('useFormValidation: No Zod schema provided');
            return true;
        }

        validating.value = true;
        errors.value = {};

        try {
            const result = zodSchema.safeParse(formData);

            if (!result.success) {
                errors.value = formatZodErrors(result.error);
                validating.value = false;
                return false;
            }

            validating.value = false;
            return true;
        } catch (error) {
            logger.error('Zod validation error:', error);
            validating.value = false;
            return true; // Allow form submission on schema error
        }
    };

    /**
     * Validate a single field using Zod schema
     * @param {string} fieldName - Field name to validate
     * @param {string} fieldName - Field name to validate
     * @param {unknown} value - Field value
     * @param {Partial<T>} formData - Full form data (for cross-field validation)
     */
    const validateFieldWithZod = (fieldName: string, value: unknown, formData: Partial<T> = {}): boolean => {
        if (!zodSchema) return true;

        // Create partial data for validation
        const dataToValidate = { ...formData, [fieldName]: value };

        try {
            const result = zodSchema.safeParse(dataToValidate);

            if (!result.success) {
                // Only extract errors for this field
                const fieldErrors = result.error.issues
                    .filter(issue => issue.path[0] === fieldName)
                    .map(issue => translateMessage(issue.message));

                if (fieldErrors.length > 0) {
                    errors.value[fieldName] = fieldErrors;
                    return false;
                }
            }

            // Clear error for this field if valid
            delete errors.value[fieldName];
            return true;
        } catch {
            return true;
        }
    };

    /**
     * Validate a single field using legacy rules
     */
    const validateField = (fieldName: string, value: unknown, fieldRules: ValidationRule<T>[], formData: T = {} as T): boolean => {
        if (!fieldRules || fieldRules.length === 0) {
            return true;
        }

        const fieldErrors: string[] = [];

        for (const rule of fieldRules) {
             
            let validator: any;
            let result: boolean | string | undefined;

            if (typeof rule === 'string') {
                validator = rules[rule] as ValidatorFunction;
                if (validator) {
                    result = validator(value, formData);
                }
            } else if (rule && typeof rule === 'object') {
                const keys = Object.keys(rule);
                const ruleName = keys[0];
                if (ruleName) {
                    const ruleParams = (rule as Record<string, any>)[ruleName];
                    const ruleCreator = rules[ruleName];

                    if (ruleCreator) {
                        if (Array.isArray(ruleParams)) {
                            validator = ruleCreator(...ruleParams);
                        } else {
                            validator = ruleCreator(ruleParams);
                        }
                        if (validator) {
                            result = validator(value, formData);
                        }
                    }
                }
            }
            else if (typeof rule === 'function') {
                result = (rule as (value: unknown, formData: T) => boolean | string)(value, formData);
            }

            if (result !== true && result !== undefined) {
                const errorMessage = typeof result === 'string' ? t(result) : String(result);
                fieldErrors.push(errorMessage);
            }
        }

        if (fieldErrors.length > 0) {
            errors.value[fieldName] = fieldErrors;
            return false;
        } else {
            delete errors.value[fieldName];
            return true;
        }
    };

    /**
     * Validate entire form using legacy rules
     */
    const validate = (formData: T, validationRules: Record<string, ValidationRule<T>[]>): boolean => {
        validating.value = true;
        errors.value = {};

        let isValid = true;

        for (const [fieldName, fieldRules] of Object.entries(validationRules)) {
            const fieldValue = formData[fieldName];
            const fieldValid = validateField(fieldName, fieldValue, fieldRules, formData);

            if (!fieldValid) {
                isValid = false;
            }
        }

        validating.value = false;
        return isValid;
    };

    /**
     * Clear all errors
     */
    const clearErrors = () => {
        errors.value = {};
        touched.value = {};
    };

    /**
     * Clear error for specific field
     */
    const clearError = (fieldName: string) => {
        delete errors.value[fieldName];
        delete touched.value[fieldName];
    };

    /**
     * Set errors from backend (422 response)
     */
    const setErrors = (backendErrors: Record<string, string[]>) => {
        errors.value = backendErrors || {};
    };

    /**
     * Mark field as touched
     */
    const touch = (fieldName: string) => {
        touched.value[fieldName] = true;
    };

    /**
     * Check if form has any errors
     */
    const hasErrors = (): boolean => {
        return Object.keys(errors.value).length > 0;
    };

    /**
     * Get error for specific field (first error only)
     */
    const getError = (fieldName: string): string | null => {
        const fieldErrors = errors.value[fieldName];
        if (Array.isArray(fieldErrors)) return fieldErrors[0] || null;
        return (fieldErrors as string) || null;
    };

    /**
     * Check if field has error
     */
    const hasError = (fieldName: string): boolean => {
        return !!errors.value[fieldName];
    };

    return {
        // State
        errors,
        touched,
        validating,

        // Legacy rules
        rules,

        // Zod validation
        validateWithZod,
        validateFieldWithZod,

        // Legacy validation
        validate,
        validateField,

        // Error management
        clearErrors,
        clearError,
        setErrors,

        // Touch tracking
        touch,

        // Helpers
        hasErrors,
        hasError,
        getError,
    };
}

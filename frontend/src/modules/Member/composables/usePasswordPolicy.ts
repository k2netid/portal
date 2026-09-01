import { computed, type ComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { useSystemStore } from '@/modules/Core/System/stores/system';

export interface PasswordPolicy {
    min_length: number;
    require_uppercase: boolean;
    require_lowercase: boolean;
    require_number: boolean;
    require_symbol: boolean;
}

const DEFAULT_POLICY: PasswordPolicy = {
    min_length: 8,
    require_uppercase: false,
    require_lowercase: false,
    require_number: false,
    require_symbol: false,
};

export function usePasswordPolicy(): {
    passwordPolicy: ComputedRef<PasswordPolicy>;
    passwordMinLength: ComputedRef<number>;
    passwordPolicyHint: ComputedRef<string>;
} {
    const { t } = useI18n();
    const systemStore = useSystemStore();

    const passwordPolicy = computed((): PasswordPolicy => {
        const raw = systemStore.siteSettings.password_policy;
        if (!raw || typeof raw !== 'object') {
            return { ...DEFAULT_POLICY };
        }
        const policy = raw as Record<string, unknown>;
        return {
            min_length: typeof policy.min_length === 'number' && policy.min_length > 0
                ? policy.min_length
                : DEFAULT_POLICY.min_length,
            require_uppercase: Boolean(policy.require_uppercase),
            require_lowercase: Boolean(policy.require_lowercase),
            require_number: Boolean(policy.require_number),
            require_symbol: Boolean(policy.require_symbol),
        };
    });

    const passwordMinLength = computed(() => passwordPolicy.value.min_length);

    const passwordPolicyHint = computed(() => {
        const policy = passwordPolicy.value;
        const parts: string[] = [
            t('member.register.passwordPolicy.minLength', {
                length: policy.min_length,
                default: `At least ${policy.min_length} characters`,
            }),
        ];
        if (policy.require_uppercase) {
            parts.push(t('member.register.passwordPolicy.uppercase', 'one uppercase letter'));
        }
        if (policy.require_lowercase) {
            parts.push(t('member.register.passwordPolicy.lowercase', 'one lowercase letter'));
        }
        if (policy.require_number) {
            parts.push(t('member.register.passwordPolicy.number', 'one number'));
        }
        if (policy.require_symbol) {
            parts.push(t('member.register.passwordPolicy.symbol', 'one symbol'));
        }
        return parts.join(' · ');
    });

    return { passwordPolicy, passwordMinLength, passwordPolicyHint };
}

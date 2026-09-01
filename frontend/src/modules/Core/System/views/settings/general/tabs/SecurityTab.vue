<template>
  <div class="space-y-4">
    <SettingGroup
      v-for="group in securitySettingsGrouped"
      :key="group.id"
      :title="group.title"
      :description="group.description"
      :icon="group.icon"
      :color="group.color"
      :default-expanded="group.defaultExpanded"
    >
      <SettingField
        v-for="setting in group.settings"
        :key="setting.id"
        :model-value="(formData[setting.key] as any)"
        :field-key="setting.key"
        :label="$t('system.settings.labels.' + setting.key)"
        :description="$t('system.settings.descriptions.' + setting.key)"
        :type="setting.type"
        :enabled-text="$t('system.settings.enabled')"
        :disabled-text="$t('system.settings.disabled')"
        :error="errors?.[setting.key]"
        :disabled="isSettingDisabled(setting.key)"
        @update:model-value="(value) => updateField(setting.key, value)"
      />
    </SettingGroup>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import SettingGroup from '@/modules/Core/System/components/settings/SettingGroup.vue'
import SettingField from '@/modules/Core/System/components/settings/SettingField.vue'
import type { Component } from 'vue'

interface Setting {
    id: string | string;
    key: string;
    value: unknown;
    type: string;
    group: string;
}

type SettingGroupColor = 'primary' | 'blue' | 'emerald' | 'amber' | 'red' | 'purple' | 'indigo' | 'orange' | 'pink'

interface SettingGroupData {
    id: string;
    title: string;
    description: string;
    icon: Component;
    color: SettingGroupColor;
    keys: string[];
    settings: Setting[];
    defaultExpanded: boolean;
}

interface Props {
    settings: Setting[];
    formData: Record<string, unknown>;
    errors?: Record<string, string[]>;
}

const { t } = useI18n()

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, unknown>): void;
}>()

const updateField = (key: string, value: unknown) => {
    emit('update:formData', { ...props.formData, [key]: value })
}

import { ShieldCheck, Key, Clock, Lock, Bot, ShieldAlert, Bell } from 'lucide-vue-next'

const isSettingDisabled = (key: string) => {
    // Captcha dependencies
    const captchaSettings = ['captcha_method', 'captcha_on_login', 'captcha_on_register']
    if (captchaSettings.includes(key)) {
        // Disable if enable_captcha is falsy (0, false, null)
        return !props.formData['enable_captcha'] || props.formData['enable_captcha'] === '0'
    }

    // Session dependencies: Disable Max Concurrent Sessions if Single Session is enabled
    if (key === 'max_concurrent_sessions') {
        return props.formData['single_session_enabled'] === true || props.formData['single_session_enabled'] === '1'
    }

    return false
}

const securitySettingsGrouped = computed(() => {
    const securitySettings = props.settings.filter(s => s && s.group === 'security')
    
    const groups: SettingGroupData[] = [
        {
            // Password / captcha / 2FA settings apply to console operators AND reader (member) accounts.
            id: 'authentication',
            title: t('system.settings.groups.authentication.title'),
            description: t('system.settings.groups.authentication.description'),
            icon: ShieldCheck,
            color: 'emerald',
            keys: ['enable_registration', 'enable_member_registration', 'require_email_verification', 'enable_2fa', 'two_factor_method', 'two_factor_enforced_roles'],
            settings: [],
            defaultExpanded: true,
        },
        {
            id: 'password',
            title: t('system.settings.groups.password.title'),
            description: t('system.settings.groups.password.description'),
            icon: Key,
            color: 'amber',
            keys: ['password_min_length', 'password_require_uppercase', 'password_require_lowercase', 'password_require_number', 'password_require_symbol'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'session',
            title: t('system.settings.groups.session.title'),
            description: t('system.settings.groups.session.description'),
            icon: Clock,
            color: 'blue',
            keys: ['session_lifetime', 'single_session_enabled', 'max_concurrent_sessions', 'log_retention_days'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'access',
            title: t('system.settings.groups.access.title'),
            description: t('system.settings.groups.access.description'),
            icon: Lock,
            color: 'red',
            keys: ['login_attempts_limit', 'block_duration_minutes'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'captcha',
            title: t('system.settings.groups.captcha.title'),
            description: t('system.settings.groups.captcha.description'),
            icon: Bot,
            color: 'purple',
            keys: ['enable_captcha', 'captcha_method', 'captcha_on_login', 'captcha_on_register'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'bot-shield',
            title: t('system.settings.groups.botShield.title'),
            description: t('system.settings.groups.botShield.description'),
            icon: ShieldCheck,
            color: 'indigo',
            keys: ['console_dashboard_slug', 'shield_protection_mode', 'shield_protection_difficulty', 'shield_enable_ip_intelligence', 'shield_allowed_countries', 'shield_log_verification_success'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'scanner-protection',
            title: t('system.settings.groups.scannerProtection.title'),
            description: t('system.settings.groups.scannerProtection.description'),
            icon: Bot,
            color: 'orange',
            keys: ['scanner_auto_block_threshold', 'security_learned_scanner_paths'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'threat-intel',
            title: t('system.settings.groups.threatIntel.title'),
            description: t('system.settings.groups.threatIntel.description'),
            icon: ShieldAlert,
            color: 'orange',
            keys: ['abuseipdb_api_key', 'threat_intel_auto_block_threshold'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'notifications',
            title: t('system.settings.groups.securityNotifications.title'),
            description: t('system.settings.groups.securityNotifications.description'),
            icon: Bell,
            color: 'pink',
            keys: ['telegram_bot_token', 'telegram_chat_id', 'email_to', 'webhook_url'],
            settings: [],
            defaultExpanded: false,
        },
    ]
    
    groups.forEach(group => {
        // Filter settings for this group
        const groupSettings = securitySettings.filter(s => group.keys.includes(s.key))
        
        // Sort settings based on the order in the keys array
        group.settings = groupSettings.sort((a, b) => {
            return group.keys.indexOf(a.key) - group.keys.indexOf(b.key)
        })
    })
    
    return groups.filter(group => group.settings.length > 0)
})
</script>

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

const ShieldCheckIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>`
}

const KeyIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>`
}

const ClockIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
}

const LockIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>`
}

const BotIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25zm.75-12h9v9h-9v-9z" /></svg>`
}

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
            id: 'authentication',
            title: t('system.settings.groups.authentication.title'),
            description: t('system.settings.groups.authentication.description'),
            icon: ShieldCheckIcon,
            color: 'emerald',
            keys: ['enable_registration', 'require_email_verification', 'enable_2fa', 'two_factor_method', 'two_factor_enforced_roles'],
            settings: [],
            defaultExpanded: true,
        },
        {
            id: 'password',
            title: t('system.settings.groups.password.title'),
            description: t('system.settings.groups.password.description'),
            icon: KeyIcon,
            color: 'amber',
            keys: ['password_min_length', 'password_require_uppercase', 'password_require_lowercase', 'password_require_number', 'password_require_symbol'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'session',
            title: t('system.settings.groups.session.title'),
            description: t('system.settings.groups.session.description'),
            icon: ClockIcon,
            color: 'blue',
            keys: ['session_lifetime', 'single_session_enabled', 'max_concurrent_sessions', 'log_retention_days'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'access',
            title: t('system.settings.groups.access.title'),
            description: t('system.settings.groups.access.description'),
            icon: LockIcon,
            color: 'red',
            keys: ['login_attempts_limit', 'block_duration_minutes'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'captcha',
            title: t('system.settings.groups.captcha.title'),
            description: t('system.settings.groups.captcha.description'),
            icon: BotIcon,
            color: 'purple',
            keys: ['enable_captcha', 'captcha_method', 'captcha_on_login', 'captcha_on_register'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'bot-shield',
            title: t('system.settings.groups.botShield.title'),
            description: t('system.settings.groups.botShield.description'),
            icon: ShieldCheckIcon,
            color: 'indigo',
            keys: ['console_dashboard_slug', 'shield_protection_mode', 'shield_protection_difficulty', 'shield_enable_ip_intelligence', 'shield_allowed_countries', 'shield_log_verification_success'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'scanner-protection',
            title: t('system.settings.groups.scannerProtection.title'),
            description: t('system.settings.groups.scannerProtection.description'),
            icon: BotIcon,
            color: 'orange',
            keys: ['scanner_auto_block_threshold', 'security_learned_scanner_paths'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'threat-intel',
            title: t('system.settings.groups.threatIntel.title'),
            description: t('system.settings.groups.threatIntel.description'),
            icon: ShieldCheckIcon,
            color: 'orange',
            keys: ['abuseipdb_api_key', 'threat_intel_auto_block_threshold'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'notifications',
            title: t('system.settings.groups.securityNotifications.title'),
            description: t('system.settings.groups.securityNotifications.description'),
            icon: ShieldCheckIcon,
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

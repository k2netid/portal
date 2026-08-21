<template>
  <div class="space-y-4">
    <SettingGroup
      v-for="group in monitoringSettingsGrouped"
      :key="group.id"
      :title="group.title"
      :description="group.description"
      :icon="(group.icon as any)"
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

interface Setting {
    id: string | string;
    key: string;
    value: unknown;
    type: string;
    group: string;
}

interface SettingGroupData {
    id: string;
    title: string;
    description: string;
    icon: unknown;
    color: 'primary' | 'blue' | 'emerald' | 'amber' | 'red' | 'purple' | 'indigo' | 'orange' | 'pink';
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

import { Activity, ShieldCheck } from 'lucide-vue-next'

const monitoringSettingsGrouped = computed(() => {
    const monitoringSettings = props.settings.filter(s => s && s.group === 'monitoring')
    
    const groups: SettingGroupData[] = [
        {
            id: 'logs',
            title: t('system.settings.groups.logs.title'),
            description: t('system.settings.groups.logs.description'),
            icon: Activity,
            color: 'indigo',
            keys: ['log_retention_days', 'activity_log_retention_days', 'security_log_retention_days', 'login_history_retention_days', 'security_alert_failed_login_threshold'],
            settings: [],
            defaultExpanded: true,
        },
        {
            id: 'backup-monitoring',
            title: t('system.settings.groups.backupMonitoring.title'),
            description: t('system.settings.groups.backupMonitoring.description'),
            icon: ShieldCheck,
            color: 'blue',
            keys: ['backup_retention_days'],
            settings: [],
            defaultExpanded: false,
        }
    ]

    groups.forEach(group => {
        group.settings = monitoringSettings.filter(s => group.keys.includes(s.key))
        
        // Ensure order
        group.settings.sort((a, b) => group.keys.indexOf(a.key) - group.keys.indexOf(b.key))
    })

    return groups.filter(group => group.settings.length > 0)
})
</script>

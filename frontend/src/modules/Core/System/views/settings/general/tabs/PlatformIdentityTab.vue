<template>
  <div class="space-y-4">
    <SettingGroup
      v-for="group in generalSettingsGrouped"
      :key="group.id"
      :title="group.title"
      :description="group.description"
      :icon="(group.icon as any)"
      :color="group.color"
      :default-expanded="group.defaultExpanded"
    >
      <template
        v-for="setting in group.settings"
        :key="setting.id"
      >
        <SettingField
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
      </template>
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

import { Globe } from 'lucide-vue-next'

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

const generalSettingsGrouped = computed(() => {
    const generalSettings = props.settings.filter(s => s && (s.group === 'general' || s.group === 'brand' || s.group === 'identity'))
    
    const groups: SettingGroupData[] = [
        {
            id: 'site',
            title: t('system.settings.groups.siteInfo.title'),
            description: t('system.settings.groups.siteInfo.description'),
            icon: Globe,
            color: 'blue',
            keys: ['site_name', 'site_logo', 'site_favicon', 'site_description', 'site_url', 'admin_email'],
            settings: [],
            defaultExpanded: true,
        },
    ]

    groups.forEach(group => {
        group.settings = generalSettings.filter(s => group.keys.includes(s.key))
        
        // Ensure settings are in logical order
        if (group.id === 'site') {
            const order = ['site_name', 'site_logo', 'site_favicon', 'site_description', 'site_url', 'admin_email'];
            group.settings.sort((a, b) => order.indexOf(a.key) - order.indexOf(b.key));
        }
    })

    return groups.filter(group => group.settings.length > 0)
})
</script>

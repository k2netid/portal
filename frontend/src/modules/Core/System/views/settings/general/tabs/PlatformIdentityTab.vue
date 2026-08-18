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

// SVG Icon Components
const GlobeIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 2.25c-2.998 0-5.74 1.1-7.843 2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" /></svg>`
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

const generalSettingsGrouped = computed(() => {
    const generalSettings = props.settings.filter(s => s && (s.group === 'general' || s.group === 'brand' || s.group === 'identity'))
    
    const groups: SettingGroupData[] = [
        {
            id: 'site',
            title: t('system.settings.groups.siteInfo.title'),
            description: t('system.settings.groups.siteInfo.description'),
            icon: GlobeIcon,
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

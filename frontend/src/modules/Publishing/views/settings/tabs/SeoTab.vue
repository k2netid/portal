<template>
  <div class="space-y-4">
    <SettingGroup
      v-for="group in seoSettingsGrouped"
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

import { Tag, Search } from 'lucide-vue-next'

const seoSettingsGrouped = computed(() => {
    const seoSettings = props.settings.filter(s => s && s.group === 'seo')
    
    const groups: SettingGroupData[] = [
        {
            id: 'meta',
            title: t('system.settings.groups.meta.title'),
            description: t('system.settings.groups.meta.description'),
            icon: Tag,
            color: 'orange',
            keys: ['meta_title', 'meta_description', 'meta_keywords'],
            settings: [],
            defaultExpanded: true,
        },
        {
            id: 'search_engines',
            title: t('system.settings.groups.searchEngines.title'),
            description: t('system.settings.groups.searchEngines.description'),
            icon: Search,
            color: 'emerald',
            keys: ['google_analytics_id', 'google_search_console', 'enable_sitemap', 'enable_robots_txt'],
            settings: [],
            defaultExpanded: false,
        },
    ]
    
    groups.forEach(group => {
        group.settings = seoSettings.filter(s => group.keys.includes(s.key))
    })
    
    return groups.filter(group => group.settings.length > 0)
})
</script>

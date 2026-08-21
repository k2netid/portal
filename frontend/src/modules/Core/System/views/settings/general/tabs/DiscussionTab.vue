<template>
  <div class="space-y-4">
    <SettingGroup
      v-for="group in discussionSettingsGrouped"
      :key="group.id"
      :title="group.title"
      :description="group.description"
      :icon="(group.icon as any)"
      :color="group.color as any"
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

import { MessageCircle, ShieldCheck } from 'lucide-vue-next'

const discussionSettingsGrouped = computed(() => {
    const discussionSettings = props.settings.filter(s => s && s.group === 'comments')
    
    const groups: SettingGroupData[] = [
        {
            id: 'general',
            title: t('system.settings.groups.discussion.title'),
            description: t('system.settings.groups.discussion.description'),
            icon: MessageCircle,
            color: 'blue',
            keys: ['comments.security.enable_reply', 'comments.security.allow_guests'],
            settings: [],
            defaultExpanded: true,
        },
        {
            id: 'moderation',
            title: t('system.settings.groups.moderation.title'),
            description: t('system.settings.groups.moderation.description'),
            icon: ShieldCheck,
            color: 'red',
            keys: ['comments.security.moderation_enabled', 'comments.security.guest_captcha', 'comments.security.max_links', 'comments.security.banned_words'],
            settings: [],
            defaultExpanded: true,
        }
    ]
    
    groups.forEach(group => {
        // Filter settings for this group
        const groupSettings = discussionSettings.filter(s => group.keys.includes(s.key))
        
        // Sort settings based on the order in the keys array
        group.settings = groupSettings.sort((a, b) => {
            return group.keys.indexOf(a.key) - group.keys.indexOf(b.key)
        })
    })
    
    return groups.filter(group => group.settings.length > 0)
})

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, unknown>): void;
}>()

const updateField = (key: string, value: unknown) => {
    emit('update:formData', { ...props.formData, [key]: value })
}
</script>

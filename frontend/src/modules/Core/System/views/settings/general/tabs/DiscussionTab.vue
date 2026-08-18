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

const MessageCircleIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>`
}

const ShieldCheckIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>`
}

const discussionSettingsGrouped = computed(() => {
    const discussionSettings = props.settings.filter(s => s && s.group === 'comments')
    
    const groups: SettingGroupData[] = [
        {
            id: 'general',
            title: t('system.settings.groups.discussion.title'),
            description: t('system.settings.groups.discussion.description'),
            icon: MessageCircleIcon,
            color: 'blue',
            keys: ['comments.security.enable_reply', 'comments.security.allow_guests'],
            settings: [],
            defaultExpanded: true,
        },
        {
            id: 'moderation',
            title: t('system.settings.groups.moderation.title'),
            description: t('system.settings.groups.moderation.description'),
            icon: ShieldCheckIcon,
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

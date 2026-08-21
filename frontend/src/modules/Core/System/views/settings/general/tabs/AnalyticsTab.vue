<script setup lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { AnalyticsService } from '@/shared/services/analyticsService'
import { parseSingleResponse } from '@/shared/utils/responseParser'
import SettingGroup from '@/modules/Core/System/components/settings/SettingGroup.vue'
import SettingField from '@/modules/Core/System/components/settings/SettingField.vue'
import { Button } from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import type { SettingValue } from '@/engine/types/settings'

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
    formData: Record<string, SettingValue>;
    errors?: Record<string, string[]>;
}

const { t } = useI18n()
const toast = useToast();
const { confirm } = useConfirm();

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, SettingValue>): void;
}>()

const updateField = (key: string, value: SettingValue) => {
    emit('update:formData', { ...props.formData, [key]: value })
}

import { BarChart3, Trash2 as TrashIcon } from 'lucide-vue-next';

const analyticsSettingsGrouped = computed(() => {
    const analyticsSettings = props.settings.filter(s => s && s.group === 'analytics')
    
    const groups: SettingGroupData[] = [
        {
            id: 'retention',
            title: t('system.settings.groups.analytics.retentionTitle'),
            description: t('system.settings.groups.analytics.description'),
            icon: BarChart3,
            color: 'indigo',
            keys: ['analytics_retention_days', 'analytics_event_retention_days', 'analytics_visitor_retention_days'],
            settings: [],
            defaultExpanded: true,
        },
    ]

    groups.forEach(group => {
        group.settings = analyticsSettings.filter(s => group.keys.includes(s.key))
    })
    
    return groups.filter(group => group.settings.length > 0)
})

const cleaning = ref(false)
const purging = ref(false)

const handleCleanup = async () => {
    const isConfirmed = await confirm({
        title: t('system.settings.groups.analytics.cleanupTitle'),
        message: t('system.settings.groups.analytics.cleanupConfirm'),
        variant: 'destructive',
        confirmText: t('system.settings.groups.analytics.cleanupButton'),
    });

    if (!isConfirmed) {
        return;
    }

    cleaning.value = true
    try {
        const response = await AnalyticsService.cleanup()
        const payload = parseSingleResponse<{ total_deleted?: number }>(response)
        const count = typeof payload?.total_deleted === 'number' ? payload.total_deleted : 0
        toast.success.action(t('system.settings.groups.analytics.cleanupSuccess', { count }))
    } catch (error) {
        toast.error.fromResponse(error)
    } finally {
        cleaning.value = false
    }
}

const handlePurgeAll = async () => {
    const isConfirmed = await confirm({
        title: t('system.settings.groups.analytics.purgeTitle'),
        message: t('system.settings.groups.analytics.purgeConfirm'),
        variant: 'destructive',
        confirmText: t('system.settings.groups.analytics.purgeButton'),
    })

    if (!isConfirmed) {
        return
    }

    purging.value = true
    try {
        const response = await AnalyticsService.purgeAll('RESET_ALL_ANALYTICS')
        const payload = parseSingleResponse<{ total_deleted?: number }>(response)
        const count = typeof payload?.total_deleted === 'number' ? payload.total_deleted : 0
        toast.success.action(t('system.settings.groups.analytics.purgeSuccess', { count }))
    } catch (error) {
        toast.error.fromResponse(error)
    } finally {
        purging.value = false
    }
}
</script>

<template>
  <div class="space-y-4">
    <SettingGroup
      v-for="group in analyticsSettingsGrouped"
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

    <!-- Manual Cleanup Tool -->
    <div class="p-6 bg-card border border-destructive/20 rounded-lg">
      <div class="flex items-start gap-4 mb-6">
        <div class="p-2 bg-destructive/10 rounded-full">
          <TrashIcon class="w-5 h-5 text-destructive" />
        </div>
        <div>
          <h3 class="text-lg font-medium text-destructive">
            {{ $t('system.settings.groups.analytics.cleanupTitle') }}
          </h3>
          <p class="text-sm text-muted-foreground mt-1">
            {{ $t('system.settings.groups.analytics.cleanupDescription') }}
          </p>
        </div>
      </div>
            
      <div class="flex flex-wrap gap-3 justify-start">
        <Button
          type="button"
          variant="destructive"
          :disabled="cleaning || purging"
          @click="handleCleanup"
        >
          <svg
            v-if="cleaning"
            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
          </svg>
          <TrashIcon
            v-else
            class="w-4 h-4 mr-2"
          />
          {{ cleaning ? $t('system.settings.groups.analytics.cleaningNow') : $t('system.settings.groups.analytics.cleanupButton') }}
        </Button>
        <Button
          type="button"
          variant="outline"
          class="border-destructive/40 text-destructive hover:bg-destructive/10"
          :disabled="cleaning || purging"
          @click="handlePurgeAll"
        >
          <svg
            v-if="purging"
            class="animate-spin -ml-1 mr-3 h-5 w-5"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
          </svg>
          <TrashIcon
            v-else
            class="w-4 h-4 mr-2"
          />
          {{ purging ? $t('system.settings.groups.analytics.purgingNow') : $t('system.settings.groups.analytics.purgeButton') }}
        </Button>
      </div>
    </div>
  </div>
</template>

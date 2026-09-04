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
      <template #badge>
        <span
          v-if="group.id === 'brand' && !systemStore.appIdentity.has_white_label"
          class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20"
        >
          <Lock class="w-3 h-3" />
          {{ $t('system.settings.white_label_required') }}
        </span>
      </template>

      <template
        v-for="setting in group.settings"
        :key="setting.id"
      >
        <SettingField
          v-if="setting.key !== 'content.autosave_interval_seconds'"
          v-show="isMaintenanceSettingVisible(setting.key)"
          :model-value="(formData[setting.key] as any)"
          :field-key="setting.key"
          :label="$t('system.settings.labels.' + setting.key)"
          :description="$t('system.settings.descriptions.' + setting.key)"
          :type="setting.type"
          :enabled-text="$t('system.settings.enabled')"
          :disabled-text="$t('system.settings.disabled')"
          :error="errors?.[setting.key]"
          :disabled="isFieldProtected(setting.key)"
          :readonly="isFieldProtected(setting.key)"
          @update:model-value="(value) => updateField(setting.key, value)"
        />

        <div
          v-else
          class="space-y-2"
        >
          <label class="block text-sm font-medium text-foreground">
            {{ $t('system.settings.labels.content.autosave_interval_seconds') }}
          </label>
          <p class="text-xs text-muted-foreground">
            {{ $t('system.settings.descriptions.content.autosave_interval_seconds') }}
          </p>

          <Select
            :model-value="autosavePresetValue"
            @update:model-value="handleAutosavePresetChange"
          >
            <SelectTrigger>
              <SelectValue :placeholder="$t('common.actions.select')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="15">
                15 detik (aktif)
              </SelectItem>
              <SelectItem value="30">
                30 detik (seimbang)
              </SelectItem>
              <SelectItem value="60">
                60 detik (ringan server)
              </SelectItem>
              <SelectItem value="custom">
                Custom
              </SelectItem>
            </SelectContent>
          </Select>

          <Input
            v-if="autosavePresetValue === 'custom'"
            :model-value="String(autosaveIntervalSeconds)"
            type="number"
            min="5"
            max="300"
            step="1"
            :placeholder="t('common.placeholders.rangeMinMax')"
            @input="handleAutosaveCustomInput"
          />
        </div>
      </template>
    </SettingGroup>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import SettingGroup from '@/modules/Core/System/components/settings/SettingGroup.vue'
import SettingField from '@/modules/Core/System/components/settings/SettingField.vue'
import { Input, Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui'

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

import { useSystemStore } from '@/modules/Core/System/stores/system'
const { t } = useI18n()
const systemStore = useSystemStore()

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, unknown>): void;
}>()

const updateField = (key: string, value: unknown) => {
    let newData = { ...props.formData, [key]: value };

    // Auto-clear stale end time when enabling maintenance mode
    if (key === 'maintenance_mode' && value === true) {
        const endTimeStr = props.formData.maintenance_end_time as string | null | undefined;
        if (endTimeStr) {
            const endTime = new Date(endTimeStr);
            if (!isNaN(endTime.getTime()) && endTime < new Date()) {
                newData.maintenance_end_time = null;
            }
        }
    }
    
    emit('update:formData', newData);
}

const normalizeAutosaveInterval = (value: unknown): number => {
    const parsed = Number(value)
    if (!Number.isFinite(parsed)) return 30
    return Math.min(300, Math.max(5, Math.round(parsed)))
}

const autosaveIntervalSeconds = computed(() => normalizeAutosaveInterval(props.formData['content.autosave_interval_seconds']))

const autosavePresetValue = computed(() => {
    const v = autosaveIntervalSeconds.value
    return [15, 30, 60].includes(v) ? String(v) : 'custom'
})

const handleAutosavePresetChange = (value: string) => {
    if (value === 'custom') {
        return
    }
    updateField('content.autosave_interval_seconds', Number(value))
}

const handleAutosaveCustomInput = (event: Event) => {
    const value = Number((event.target as HTMLInputElement).value)
    updateField('content.autosave_interval_seconds', normalizeAutosaveInterval(value))
}

import { Clock, Wrench, Sparkles, Lock } from 'lucide-vue-next'

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

const isMaintenanceSettingVisible = (key: string) => {
    // Basic switches for master maintenance mode
    const maintenanceSubSettings = ['maintenance_title', 'maintenance_message', 'maintenance_countdown_enabled', 'maintenance_end_time'];
    
    if (maintenanceSubSettings.includes(key)) {
        if (!props.formData.maintenance_mode) return false;
        
        // Additional check for countdown end time
        if (key === 'maintenance_end_time' && !props.formData.maintenance_countdown_enabled) {
            return false;
        }
    }
    
    return true;
}

const isFieldProtected = (key: string) => {
    // Branding fields are protected if no White Label license
    const brandingKeys = ['app_name', 'app_logo', 'brand_logo', 'app_favicon', 'brand_favicon', 'branding_display'];
    if (brandingKeys.includes(key)) {
        return !systemStore.appIdentity.has_white_label;
    }
    
    // License Type is readonly
    if (key === 'app_license_tier' || key === 'license_type') {
        return true;
    }
    
    return false;
}

// General settings grouped by category
const generalSettingsGrouped = computed(() => {
    const systemSettings = props.settings.filter(s => s && (s.group === 'system' || s.group === 'brand' || s.group === 'general'))
    
    const groups: SettingGroupData[] = [
        {
            id: 'brand',
            title: t('system.settings.groups.brand.title'),
            description: t('system.settings.groups.brand.description'),
            icon: Sparkles,
            color: 'indigo',
            keys: ['app_name', 'app_logo', 'brand_logo', 'app_favicon', 'brand_favicon', 'branding_display'],
            settings: [],
            defaultExpanded: true,
        },
        {
            id: 'localization',
            title: t('system.settings.groups.localization.title'),
            description: t('system.settings.groups.localization.description'),
            icon: Clock,
            color: 'amber',
            keys: ['timezone', 'date_format', 'time_format', 'items_per_page'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'maintenance',
            title: t('system.settings.groups.maintenance.title'),
            description: t('system.settings.groups.maintenance.description'),
            icon: Wrench,
            color: 'orange',
            keys: ['maintenance_mode', 'maintenance_title', 'maintenance_message', 'maintenance_countdown_enabled', 'maintenance_end_time'],
            settings: [],
            defaultExpanded: false,
        },
    ]

    groups.forEach(group => {
        group.settings = systemSettings.filter(s => group.keys.includes(s.key))
        
        // Ensure settings are in logical order
        const orders: Record<string, string[]> = {
            'brand': ['app_name', 'app_logo', 'brand_logo', 'app_favicon', 'brand_favicon', 'branding_display'],
            'maintenance': ['maintenance_mode', 'maintenance_title', 'maintenance_message', 'maintenance_countdown_enabled', 'maintenance_end_time'],
            'localization': ['timezone', 'date_format', 'time_format', 'items_per_page']
        };

        const groupOrder = orders[group.id];
        if (groupOrder) {
            group.settings.sort((a, b) => groupOrder.indexOf(a.key) - groupOrder.indexOf(b.key));
        }
    })

    return groups.filter(group => group.settings.length > 0)
})
</script>


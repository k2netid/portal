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

// SVG Icon Components
const ClockIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
}

const ToolIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.999.922l-7.126 7.126a.908.908 0 01-1.287 0l-1.287-1.287a.908.908 0 010-1.287l7.126-7.126c.851-.735 1.013-1.923.922-2.999a4.5 4.5 0 014.484-4.884 4.5 4.5 0 014.884 4.884zM11.64 12.36L9.64 10.36" /><path stroke-linecap="round" stroke-linejoin="round" d="M7 17l-5 5" /><path stroke-linecap="round" stroke-linejoin="round" d="M12.5 12.5l5.5-5.5" /></svg>`
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

const BrandIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" /></svg>`
}

const isFieldProtected = (key: string) => {
    // Branding fields are protected if no White Label license
    const brandingKeys = ['app_name', 'app_logo', 'app_favicon', 'branding_display'];
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
            icon: BrandIcon,
            color: 'indigo',
            keys: ['app_name', 'app_logo', 'app_favicon', 'branding_display'],
            settings: [],
            defaultExpanded: true,
        },
        {
            id: 'localization',
            title: t('system.settings.groups.localization.title'),
            description: t('system.settings.groups.localization.description'),
            icon: ClockIcon,
            color: 'amber',
            keys: ['timezone', 'date_format', 'time_format', 'items_per_page'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'maintenance',
            title: t('system.settings.groups.maintenance.title'),
            description: t('system.settings.groups.maintenance.description'),
            icon: ToolIcon,
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
            'brand': ['app_name', 'brand_logo', 'brand_favicon', 'branding_display'],
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


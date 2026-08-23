<template>
  <div class="settings-group">
    <!-- Header -->
    <button class="group-header" @click="$emit('toggle')">
      <ChevronRight 
        :size="14" 
        class="group-icon"
        :class="{ 'is-open': isOpen }"
      />
      <span class="group-label">{{ translatedLabel }}</span>
      
      <!-- Design Presets Button (Hover) -->
      <DesignPresetsSelector 
        v-if="group.presets"
        size="sm"
        class="group-presets-trigger"
        :type="module.type"
        :based-on-name="translatedLabel + ' 1'"
        @action="handlePresetAction"
      />
    </button>
    
    <!-- Content -->
    <div v-show="isOpen" class="group-content">
      <div 
        v-for="item in group.fields"
        :key="getItemKey(item)"
        v-show="isItemVisible(item)"
        class="field-row"
      >
        <FieldRenderer
          v-if="isField(item)"
          :field="item"
          :value="getFieldValue(item.name!)"
          :module="module"
          :device="device"
          @update="updateField(item.name!, $event)"
        />
        <SettingsGroup
          v-else
          :group="item"
          :module="module"
          :device="device"
          :is-open="nestedStates[(item as ModuleGroup).id]"
          @toggle="toggleNested((item as ModuleGroup).id)"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { inject, computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';import FieldRenderer from '@/components/builder/fields/FieldRenderer.vue'
import DesignPresetsSelector from './DesignPresetsSelector.vue'
import SettingsGroup from './SettingsGroup.vue'
import type { BuilderInstance, ModuleGroup, ModuleField, BlockInstance, SettingDefinition } from '@/types/builder'

const props = withDefaults(defineProps<{
  group: ModuleGroup;
  module: BlockInstance;
  isOpen?: boolean;
  device?: string;
}>(), {
  isOpen: false,
  device: 'desktop'
})

defineEmits<{
  (e: 'toggle'): void
}>()

const { t, te } = useI18n()

// Inject builder
const builder = inject<BuilderInstance>('builder')

// State for nested groups
const nestedStates = ref<Record<string, boolean>>({})

const toggleNested = (id: string) => {
  nestedStates.value[id] = !nestedStates.value[id]
}

// Type Guards
const isField = (item: ModuleField): item is SettingDefinition => {
  return 'type' in item && item.type !== 'group'
}

const getItemKey = (item: ModuleField) => {
  if (isField(item)) return item.name || item.key || Math.random().toString()
  return item.id || Math.random().toString()
}

// Computed
const translatedLabel = computed(() => {
  if (te(`builder.settings.groups.${props.group.id}`)) {
    return t(`builder.settings.groups.${props.group.id}`)
  }
  return props.group.label
})

const getFieldValue = (name: string) => {
  return props.module.settings?.[name]
}

const updateField = (name: string, value: unknown) => {
  builder?.updateModuleSettings(props.module.id, { [name]: value })

  // Auto-reset loop fields if loop is enabled/disabled? 
  if (name === 'loop_enable' && value === false && props.group.id === 'loop') {
    const fieldsToReset: Record<string, unknown> = {}
    props.group.fields.forEach((field) => {
       if (isField(field) && field.name !== 'loop_enable' && field.default !== undefined) {
         fieldsToReset[field.name!] = field.default
       }
    })
    if (Object.keys(fieldsToReset).length > 0) {
      builder?.updateModuleSettings(props.module.id, fieldsToReset)
    }
  }
}

const isItemVisible = (item: ModuleField): boolean => {
  if (isField(item)) {
    return isFieldVisible(item)
  }
  
  // For groups, check if the group itself has a condition
  const groupItem = item as ModuleGroup
  if (groupItem.condition) {
    return groupItem.condition(props.module.settings)
  }
  return true
}

const isFieldVisible = (field: ModuleField): boolean => {
  if (!isField(field)) return true
  
  const setting = field as SettingDefinition
  const showIf = setting.show_if
  if (!showIf || Array.isArray(showIf) || !('field' in showIf)) return true

  const dependencyName = showIf.field as string
  
  // Find the dependency field in the same group to check its visibility recursively
  const dependencyField = props.group.fields.find((f) => isField(f) && f.name === dependencyName)
  if (dependencyField && isField(dependencyField) && !isFieldVisible(dependencyField)) {
    return false
  }

  const dependencyValue = dependencyName ? getFieldValue(dependencyName) : undefined
  const expectedValue = showIf.value
  
  if (Array.isArray(expectedValue)) {
    return (expectedValue as unknown[]).includes(dependencyValue)
  }
  
  return dependencyValue === expectedValue
}

const handlePresetAction = (payload: { type: string; data: import('@/types/builder').BuilderPreset | null }) => {
  const { type, data } = payload
  if (type === 'addNew' || type === 'newFromCurrent') {
    builder?.openSavePresetModal?.(props.module.id)
  } else if (type === 'apply' && data) {
    builder?.applyPreset(props.module.id, data)
  }
}
</script>

<style scoped>
.settings-group {
  border: none;
  border-radius: 0;
  overflow: hidden;
}

.group-header {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  width: 100%;
  padding: 12px 20px;
  background: var(--builder-bg-primary);
  border: none;
  border-bottom: 1px solid var(--builder-border);
  color: var(--builder-text-primary);
  font-size: var(--font-size-sm);
  font-weight: 600;
  cursor: pointer;
  text-align: left;
}

.group-header:hover {
  background: var(--builder-bg-primary);
  opacity: 0.8;
}

.group-icon {
  color: var(--builder-text-muted);
  transition: transform 0.2s;
}

.group-icon.is-open {
  transform: rotate(90deg);
}

.group-presets-trigger {
  margin-left: auto;
  opacity: 0;
  transition: opacity 0.2s;
}

.group-header:hover .group-presets-trigger {
  opacity: 1;
}

.presets-btn {
  color: var(--builder-text-muted);
}

.presets-btn:hover {
  color: var(--builder-accent);
}


.group-content {
  padding: var(--spacing-lg) 20px;
}

.field-row {
  margin-bottom: var(--spacing-md);
}

.field-row:last-child {
  margin-bottom: 0;
}
</style>

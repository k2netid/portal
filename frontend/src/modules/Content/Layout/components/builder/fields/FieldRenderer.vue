<template>
  <FieldWrapper 
    v-if="isVisible"
    :label="translatedLabel"
    :description="translatedDescription"
    :responsive="field.responsive"
    :show-responsive="field.responsive"
    :show-dynamic-data="supportsDynamicData"
    :show-context-menu="supportsContextMenu"
    :show-presets="supportsPresets"
    :has-custom-value="hasCustomValue"
    :active-device="currentDevice"
    @reset="resetFullModule"
    @reset-field="resetSpecificField"
    @responsive="handleResponsiveClick"
    @select-dynamic-data="handleDynamicDataClick"
    @assign-preset="handleAssignPreset"
  >
    <component
      :is="FieldComponent"
      v-bind="field"
      :field="field"
      :value="resolvedValue"
      :placeholder-value="placeholderValue"
      :module="module"
      @update:value="handleValueUpdate"
    />

    <!-- Dynamic Data Popover -->
    <BasePopover
      :is-open="isDynamicPopoverOpen"
      :trigger-rect="dynamicPopoverRect"
      :title="$t('builder.fields.actions.dynamicData')"
      :width="280"
      :no-padding="true"
      @close="isDynamicPopoverOpen = false"
    >
      <DynamicDataPopover 
        :source="loopContextSource"
        @select="handleDynamicSelection" 
      />
    </BasePopover>

    <!-- Preset Popover -->
    <BasePopover
      :is-open="isPresetPopoverOpen"
      :trigger-rect="presetPopoverRect"
      :title="$t('builder.presets.title', 'Presets')"
      :width="280"
      :no-padding="true"
      @close="isPresetPopoverOpen = false"
    >
      <PresetPopoverContent
        :type="module.type"
        :field-name="field.key || (field as any).name"
        @action="handlePresetAction"
      />
    </BasePopover>
  </FieldWrapper>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, inject, ref, type Component } from 'vue'
import { useI18n } from 'vue-i18n'
import FieldWrapper from './FieldWrapper.vue'
import BasePopover from '@/components/builder/ui/BasePopover.vue'
import DynamicDataPopover from '@/components/builder/ui/DynamicDataPopover.vue'
import PresetPopoverContent from '@/components/builder/ui/PresetPopoverContent.vue'
import type { BlockInstance, BuilderInstance, SettingDefinition, BuilderPreset } from '@/types/builder'

// Inject builder
const builder = inject<BuilderInstance>('builder')!

// Dynamic field component imports
const fieldComponents: Record<string, Component> = {
  text: defineAsyncComponent(() => import('./TextField.vue')),
  textarea: defineAsyncComponent(() => import('./TextareaField.vue')),
  richtext: defineAsyncComponent(() => import('./RichtextField.vue')),
  number: defineAsyncComponent(() => import('./NumberField.vue')),
  range: defineAsyncComponent(() => import('./RangeField.vue')),
  select: defineAsyncComponent(() => import('./SelectField.vue')),
  toggle: defineAsyncComponent(() => import('./ToggleField.vue')),
  color: defineAsyncComponent(() => import('./ColorField.vue')),
  buttonGroup: defineAsyncComponent(() => import('./ButtonGroupField.vue')),
  spacing: defineAsyncComponent(() => import('./SpacingField.vue')),
  border: defineAsyncComponent(() => import('./BorderField.vue')),
  shadow: defineAsyncComponent(() => import('./ShadowField.vue')),
  upload: defineAsyncComponent(() => import('./UploadField.vue')),
  children_manager: defineAsyncComponent(() => import('./ChildrenManagerField.vue')),
  background: defineAsyncComponent(() => import('./BackgroundField.vue')),
  meta_query: defineAsyncComponent(() => import('./MetaQueryField.vue')),
  advanced_number: defineAsyncComponent(() => import('./AdvancedNumberField.vue')),
  icon: defineAsyncComponent(() => import('./IconField.vue')),
  font: defineAsyncComponent(() => import('./FontFamilyField.vue')),
  dimension: defineAsyncComponent(() => import('./DimensionField.vue')),
  filters: defineAsyncComponent(() => import('./FilterField.vue')),
  transform: defineAsyncComponent(() => import('./TransformField.vue')),
  animation: defineAsyncComponent(() => import('./AnimationField.vue')),
  conditions: defineAsyncComponent(() => import('./ConditionField.vue')),
  attributes: defineAsyncComponent(() => import('./AttributeField.vue')),
  custom_css: defineAsyncComponent(() => import('./CSSField.vue')),
  interactions: defineAsyncComponent(() => import('./InteractionField.vue')),
  scroll_effects: defineAsyncComponent(() => import('./ScrollEffectsField.vue')),
  repeater: defineAsyncComponent(() => import('./RepeaterField.vue')),
  gradient: defineAsyncComponent(() => import('./GradientField.vue')),
  group: defineAsyncComponent(() => import('./GroupField.vue'))
}

const props = defineProps<{
  field: SettingDefinition;
  value?: unknown;
  module: BlockInstance;
  device?: string | null;
}>()

const emit = defineEmits<{
  (e: 'update', value: unknown): void;
}>()

const { t, te } = useI18n()

// Computed
const translatedLabel = computed(() => {
  const type = props.module.type
  const name = (props.field.key || props.field.name || '')

  if (te(`builder.settings.${type}.${name}.label`)) {
    return t(`builder.settings.${type}.${name}.label`)
  }

  if (te(`builder.settings.fields.${name}`)) {
    return t(`builder.settings.fields.${name}`)
  }

  return (props.field.label || name) as string
})

const translatedDescription = computed(() => {
  const type = props.module.type
  const name = (props.field.key || props.field.name || '')

  if (te(`builder.settings.${type}.${name}.description`)) {
    return t(`builder.settings.${type}.${name}.description`)
  }

  if (te(`builder.info.loop.${name}`)) {
    return t(`builder.info.loop.${name}`)
  }

  if (te(`builder.info.${type}.${name}`)) {
    return t(`builder.info.${type}.${name}`)
  }

  if (te(`builder.info.common.${name}`)) {
    return t(`builder.info.common.${name}`)
  }

  return (props.field.description || '') as string
})

const currentDevice = computed(() => props.device || builder?.device.value || 'desktop')

const resolvedValue = computed(() => {
  if (!props.field.responsive || currentDevice.value === 'desktop') {
    return props.value
  }
  
  const suffix = currentDevice.value === 'mobile' ? '_mobile' : `_${currentDevice.value}`
  const deviceKey = (props.field.key || props.field.name || '') + suffix
  return (props.module.settings as Record<string, unknown>)?.[deviceKey]
})

const placeholderValue = computed(() => {
  if (!props.field.responsive || currentDevice.value === 'desktop') {
    return null
  }

  const settings: Record<string, unknown> = (props.module.settings || {}) as Record<string, unknown>
  const name = (props.field.key || props.field.name || '')
  const desktopValue = settings[name]
  const tabletValue = settings[name + '_tablet']
  
  if (currentDevice.value === 'tablet') {
    return desktopValue
  }
  
  if (currentDevice.value === 'mobile') {
    return tabletValue || desktopValue
  }
  
  return desktopValue
})

// Visibility Logic
const isVisible = computed(() => {
    if (!props.field.show_if) return true;

    const checkCondition = (condition: Record<string, unknown>, settings: Record<string, unknown>): boolean => {
        if (condition.AND) {
            const basePass = checkSingleCondition(condition, settings);
            if (!basePass) return false;
            return checkCondition(condition.AND as Record<string, unknown>, settings);
        }
        return checkSingleCondition(condition, settings);
    }
    
    const checkSingleCondition = (condition: { field?: string, value?: unknown, operator?: string }, settings: Record<string, unknown>): boolean => {
        if (!condition || !condition.field) return true;
        const targetField = condition.field;
        const targetValue = condition.value;
        const operator = condition.operator || 'eq';
        const currentValue = settings[targetField];
        
        switch (operator) {
            case 'eq': 
                if (Array.isArray(targetValue)) return targetValue.includes(currentValue);
                return currentValue === targetValue;
            case 'neq': return currentValue !== targetValue;
            case 'in': return Array.isArray(targetValue) && targetValue.includes(currentValue);
            case 'not_in': return Array.isArray(targetValue) && !targetValue.includes(currentValue);
            case 'contains': return Array.isArray(currentValue) && currentValue.includes(targetValue);
            default: return currentValue === targetValue;
        }
    }
    
    const conditions = Array.isArray(props.field.show_if) ? props.field.show_if : [props.field.show_if];
    const settings = props.module.settings || {};
    
    return conditions.every(condition => checkCondition(condition, settings));
})

// Dynamic Data State
const isDynamicPopoverOpen = ref(false)
const dynamicPopoverRect = ref<DOMRect | null>(null)

// Preset Popover State
const isPresetPopoverOpen = ref(false)
const presetPopoverRect = ref<DOMRect | null>(null)

const loopContextSource = computed(() => 'all')

const FieldComponent = computed(() => {
  return fieldComponents[props.field.type] || fieldComponents.text
})

const supportsDynamicData = computed(() => {
   const excludedTypes = ['background', 'select', 'toggle', 'buttonGroup', 'range', 'children_manager', 'shadow', 'border', 'spacing', 'icon', 'number', 'repeater']
   return !excludedTypes.includes(props.field.type)
})

const supportsContextMenu = computed(() => {
   const excludedTypes = ['background', 'children_manager']
   return !excludedTypes.includes(props.field.type)
})

const hasCustomValue = computed(() => {
    if (props.value === null || props.value === undefined || props.value === '') return false
    
    const moduleDef = builder?.getModuleDefinition(props.module.type)
    const name = (props.field.key || props.field.name || '')
    const defaultValue = moduleDef?.defaults?.[name] ?? props.field.default
    
    return JSON.stringify(props.value) !== JSON.stringify(defaultValue)
})

const handleValueUpdate = (val: unknown) => {
  if (!props.field.responsive || currentDevice.value === 'desktop') {
    emit('update', val)
    return
  }
  
  const suffix = currentDevice.value === 'mobile' ? '_mobile' : `_${currentDevice.value}`
  const deviceKey = (props.field.key || props.field.name || '') + suffix
  builder?.updateModuleSettings(props.module.id, { [deviceKey]: val })
}

const handleResponsiveClick = () => {
    builder?.openResponsiveModal({
        label: translatedLabel.value,
        baseKey: (props.field.key || props.field.name || ''),
        type: props.field.type,
        options: props.field.options as unknown[] | Record<string, unknown>,
        module: props.module,
        settings: (props.module.settings || {}) as Record<string, unknown>
    })
}

const resetFullModule = () => {
    resetSpecificField()
}

const resetSpecificField = () => {
    const moduleDef = builder?.getModuleDefinition(props.module.type)
    const name = (props.field.key || props.field.name || '')
    const defaultValue = moduleDef?.defaults?.[name] ?? props.field.default
    
    let valueToSet = defaultValue
    if (valueToSet === undefined) {
        const fallbacks: Record<string, unknown> = {
            text: '',
            number: 0,
            toggle: false,
            select: ''
        }
        valueToSet = fallbacks[props.field.type] ?? null
    }

    handleValueUpdate(valueToSet)
}

const supportsPresets = computed(() => {
    const designFieldTypes = ['color', 'spacing', 'border', 'shadow', 'typography', 'background', 'filters', 'transform', 'animation']
    return designFieldTypes.includes(props.field.type)
})

const handleAssignPreset = (target: HTMLElement | { target: HTMLElement }) => {
    const el = target instanceof HTMLElement 
        ? target 
        : (target?.target instanceof HTMLElement ? target.target : null)

    if (el) {
        presetPopoverRect.value = el.getBoundingClientRect()
        isPresetPopoverOpen.value = true
    }
}

const handlePresetAction = (payload: { type: string, data: BuilderPreset | null }) => {
    const { type, data } = payload
    if (type === 'addNew' || type === 'newFromCurrent') {
        builder?.openSavePresetModal?.(props.module.id)
    } else if (type === 'apply' && data) {
        const name = (props.field.key || props.field.name || '')
        if (data.settings && data.settings[name] !== undefined) {
            handleValueUpdate(data.settings[name])
        }
    }
    isPresetPopoverOpen.value = false
}

const handleDynamicDataClick = (target: HTMLElement | { target: HTMLElement }) => {
    const el = target instanceof HTMLElement 
        ? target 
        : (target.target instanceof HTMLElement ? target.target : null)

    if (el) {
        dynamicPopoverRect.value = el.getBoundingClientRect()
        isDynamicPopoverOpen.value = true
    }
}

const handleDynamicSelection = (item: { tag: string }) => {
    handleValueUpdate(`@dynamic:${item.tag}`)
    isDynamicPopoverOpen.value = false
}
</script>

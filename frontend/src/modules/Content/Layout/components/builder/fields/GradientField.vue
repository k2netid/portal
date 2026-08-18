<template>
  <div class="gradient-field">
    <!-- Gradient Preview -->
    <div 
      v-if="!hidePreview"
      class="bg-preview-box gradient-preview-box"
      :class="{ 
        'is-inherited': !value && placeholderValue,
        'is-empty': !value && !placeholderValue 
      }"
      :style="{ '--preview-bg': (value || placeholderValue) ? (gradientCSS || placeholderCSS) : 'none' }"
      @click="!value && !placeholderValue ? initDefaultGradient() : null"
      @mouseenter="isHovered = true"
      @mouseleave="isHovered = false"
    >
      <div v-if="!value && placeholderValue" class="inherited-badge">{{ t('builder.fields.background.color.inherited') }}</div>
      
      <!-- Empty/Add State -->
      <div v-if="!value && !placeholderValue" class="empty-preview" @click.stop="initDefaultGradient">
          <Plus :size="20" />
          <span>{{ t('builder.fields.background.gradient.add') }}</span>
      </div>

      <!-- Action Buttons Overlay -->
      <div v-if="isHovered && (value || placeholderValue)" class="preview-actions-overlay">
          <div class="action-btn-group">
              <div class="action-icon-btn" :title="t('builder.contextMenu.duplicate')" @click.stop="duplicateGradient">
                  <Copy :size="14" />
              </div>
              <div class="action-icon-btn remove" :title="t('builder.contextMenu.delete')" @click.stop="clearGradient">
                  <Trash2 :size="14" />
              </div>
          </div>
      </div>
    </div>

    <!-- Gradient Slider (Only show if we have a value) -->
    <div v-if="value || placeholderValue" class="gradient-slider-container" ref="sliderRef" @click="addStopAtClick">
      <div class="gradient-track" :style="{ backgroundImage: trackCSS }"></div>
      
      <!-- Handles -->
      <div 
        v-for="(stop, index) in localStops" 
        :key="index"
        class="gradient-stop-handle"
        :class="{ 'active': activeStopIndex === index }"
        :style="{ left: `${stop.position}%`, backgroundColor: stop.color }"
        @mousedown.stop="startDrag($event, index)"
        @click.stop="handleStopClick(index)"
      >
        <div class="handle-inner"></div>
      </div>
    </div>

    <!-- Active Stop Settings -->
    <div v-if="(value || placeholderValue) && activeStop" class="active-stop-settings mt-4">
      <div class="flex items-center justify-between mb-3 border-b border-white/5 pb-2">
        <label class="builder-label mb-0">{{ t('builder.fields.background.gradient.selectedStop') }}</label>
        <IconButton 
          :icon="Trash2" 
          size="sm" 
          variant="ghost" 
          class="text-red-500 hover:bg-red-500/10"
          v-if="localStops.length > 2"
          @click="removeStop(activeStopIndex)"
        />
      </div>
      
      <div class="space-y-4">
        <!-- ... (lines 69-487 skipped/omitted in prompt, assuming separate edits is safer) -->
        <div>
          <label class="builder-label mb-1.5">{{ t('builder.fields.common.color') }}</label>
          <ColorField 
            ref="activeColorFieldRef"
            :value="activeStop.color"
            @update:value="updateStopColor"
          />
        </div>
        
        <div class="position-control">
           <label class="builder-label mb-1.5">{{ t('builder.fields.common.position') }}</label>
           <div class="flex items-center gap-3">
             <input 
               type="range" 
               v-model.number="activeStop.position" 
               min="0" 
               max="100"
               class="flex-1"
               @input="emitUpdate"
             />
             
             <div class="flex items-center gap-2">
                <div class="position-input-wrapper">
                    <input 
                      type="number" 
                      v-model.number="activeStop.position" 
                      min="0" 
                      max="100"
                      class="position-num-input"
                      @input="emitUpdate"
                    />
                    <div class="position-spinners">
                        <button class="spinner-btn" @click.stop="updatePosition(1)">
                            <ChevronUp :size="10" />
                        </button>
                        <button class="spinner-btn" @click.stop="updatePosition(-1)">
                            <ChevronDown :size="10" />
                        </button>
                    </div>
                </div>
                <span class="text-[11px] text-muted">%</span>
             </div>
           </div>
        </div>
      </div>
    </div>

    <!-- Gradient Controls -->
    <div v-if="!minimal" class="gradient-controls mt-6 space-y-4">
      <div class="gradient-type-control">
        <div class="flex items-center justify-between mb-2">
           <label class="builder-label mb-0">{{ t('builder.fields.background.gradient.type.label') }}</label>
           <FieldActions 
                :label="t('builder.fields.background.gradient.type.label')"
                show-info
                show-responsive
                :show-reset="localSettings.type !== 'linear'"
                :show-context-menu="true"
                :show-dynamic-data="false"
                @toggle-info="showTypeInfo = !showTypeInfo"
                @reset="updateControl('type', 'linear')"
                @responsive="$emit('responsive', { 
                    label: t('builder.fields.background.gradient.type.label'), 
                    key: 'type', 
                    type: 'select',
                    options: [
                      { label: 'Linear', value: 'linear' },
                      { label: 'Circular', value: 'circular' },
                      { label: 'Elliptical', value: 'elliptical' },
                      { label: 'Conical', value: 'conical' }
                    ]
                })"
           />
        </div>
        <div v-if="showTypeInfo" class="field-info-text mb-2">
            {{ t('builder.fields.background.gradient.typeInfo') }}
        </div>
        <SelectField 
          :field="{ 
            name: 'type', 
            type: 'select',
            label: 'Gradient Type', 
            options: [
              { label: 'Linear', value: 'linear' },
              { label: 'Circular', value: 'circular' },
              { label: 'Elliptical', value: 'elliptical' },
              { label: 'Conical', value: 'conical' }
            ]
          }"
          :value="localSettings.type || 'linear'"
          @update:value="updateControl('type', $event)"
        />
      </div>

      <div v-if="localSettings.type === 'linear' || localSettings.type === 'conical'" class="direction-control">
         <div class="flex items-center justify-between mb-2">
           <label class="builder-label mb-0">{{ t('builder.fields.background.gradient.direction') }}</label>
           <FieldActions 
                :label="t('builder.fields.background.gradient.direction')"
                show-info
                show-responsive
                :show-reset="isDirectionDirty"
                :show-duplicate="false"
                :show-dynamic-data="true"
                @toggle-info="showDirectionInfo = !showDirectionInfo"
                @reset="updateControl('direction', '180deg')"
                @responsive="$emit('responsive', { label: t('builder.fields.background.gradient.direction'), key: 'direction', type: 'text' })"
           />
         </div>
         <div v-if="showDirectionInfo" class="field-info-text mb-2">
            {{ t('builder.fields.background.gradient.directionInfo') }}
         </div>

         <div class="flex items-center gap-2 mb-3">
             <div class="flex-1 relative flex items-center separate-input-box">
                <input 
                  type="number" 
                  :value="directionValue" 
                  @input="handleDirectionNumberInput"
                  class="base-input w-full pr-8"
                />
                <div class="input-spinners-inline">
                    <button class="spinner-inline-btn" @click="updateDirectionValue(1)">
                        <ChevronUp :size="12" />
                    </button>
                    <button class="spinner-inline-btn" @click="updateDirectionValue(-1)">
                        <ChevronDown :size="12" />
                    </button>
                </div>
             </div>
             
             <div class="separate-unit-dropdown">
                <BaseDropdown align="right" width="auto" :min-width="80">
                    <template #trigger="{ open }">
                        <div class="unit-trigger" :class="{ 'is-open': open }">
                            <span>{{ directionUnit }}</span>
                            <ChevronDown :size="12" />
                        </div>
                    </template>
                    
                    <template #default="{ close }">
                        <div 
                          v-for="unit in ['deg', 'rad', 'grad', 'turn']" 
                          :key="unit"
                          class="unit-item"
                          :class="{ active: directionUnit === unit }"
                          @click="setDirectionUnit(unit); close()"
                        >
                            {{ unit }}
                        </div>
                        <div class="menu-divider"></div>
                        <div 
                          v-for="unit in ['inherit', 'unset', 'css var']" 
                          :key="unit"
                          class="unit-item"
                          :class="{ active: directionUnit === unit }"
                          @click="setDirectionUnit(unit); close()"
                        >
                            {{ unit }}
                        </div>
                    </template>
                </BaseDropdown>
             </div>
         </div>

         <div class="px-1">
             <BaseSlider 
               :min="0" 
               :max="directionMax" 
               :model-value="directionValue" 
               @update:model-value="updateDirectionValueFromSlider"
             />
         </div>
      </div>

      <div class="repeat-control">
          <div class="flex items-center justify-between mb-2">
            <label class="builder-label mb-0">{{ t('builder.fields.background.gradient.repeat') }}</label>
            <FieldActions 
                :label="t('builder.fields.background.gradient.repeat')"
                show-info
                show-responsive
                :show-reset="localSettings.repeat !== false"
                :show-context-menu="true"
                :show-dynamic-data="false"
                @toggle-info="showRepeatInfo = !showRepeatInfo"
                @reset="updateControl('repeat', false)"
                @responsive="$emit('responsive', { label: t('builder.fields.background.gradient.repeat'), key: 'repeat', type: 'toggle' })"
            />
          </div>
          <div v-if="showRepeatInfo" class="field-info-text mb-2">
            {{ t('builder.fields.background.gradient.repeatInfo') }}
          </div>
        <ToggleField 
            :field="{ name: 'repeat', type: 'boolean', label: t('builder.fields.background.gradient.repeat') }"
            :value="localSettings.repeat || false"
            @update:value="updateControl('repeat', $event)"
        />
      </div>

      <div class="length-control">
        <div class="flex items-center justify-between mb-2">
            <label class="builder-label mb-0">{{ t('builder.fields.background.gradient.length') }}</label>
            <FieldActions 
                :label="t('builder.fields.background.gradient.length')"
                show-info
                show-responsive
                :show-reset="localSettings.length !== '100%'"
                :show-context-menu="true"
                :show-dynamic-data="false"
                @toggle-info="showLengthInfo = !showLengthInfo"
                @reset="updateControl('length', '100%')"
                @responsive="$emit('responsive', { label: t('builder.fields.background.gradient.length'), key: 'length', type: 'dimension', options: ['%'] })"
            />
        </div>
        <div v-if="showLengthInfo" class="field-info-text mb-2">
            {{ t('builder.fields.background.gradient.lengthInfo') }}
        </div>
        <div class="flex items-center gap-2 mb-3">
            <div class="flex-1 relative flex items-center separate-input-box">
                <input 
                  type="number" 
                  :value="parseInt(localSettings.length || '100%') || 100" 
                  @input="updateControl('length', ($event.target as HTMLInputElement).value + '%')"
                  class="base-input w-full pr-8"
                />
                <div class="input-spinners-inline">
                    <button class="spinner-inline-btn" @click="updateLengthValue(1)">
                        <ChevronUp :size="12" />
                    </button>
                    <button class="spinner-inline-btn" @click="updateLengthValue(-1)">
                        <ChevronDown :size="12" />
                    </button>
                </div>
            </div>
            <div class="separate-unit-box">
                %
            </div>
        </div>
        
        <div class="px-1">
            <BaseSlider 
               :min="0" 
               :max="100" 
               :model-value="parseInt(localSettings.length || '100%') || 100" 
               @update:model-value="updateControl('length', $event + '%')"
               class="flex-1"
             />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, inject, watch, nextTick } from 'vue'
import type { BuilderInstance, SettingDefinition, BlockInstance } from '@/types/builder'
import type { Gradient, GradientStop } from '@/shared/utils/styleUtils'
import { useI18n } from 'vue-i18n'
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import ChevronUp from 'lucide-vue-next/dist/esm/icons/chevron-up.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import ColorField from './ColorField.vue'
import SelectField from './SelectField.vue'
import ToggleField from './ToggleField.vue'
import { IconButton, BaseSlider, BaseDropdown } from '../ui'
import FieldActions from './FieldActions.vue'
import { generateGradientCSS, getHarmoniousGradientColors } from '../../../shared/utils/styleUtils'

const props = defineProps<{
  field?: SettingDefinition;
  value?: Gradient;
  placeholderValue?: Gradient;
  module?: BlockInstance;
  minimal?: boolean;
  hidePreview?: boolean;
}>()

const { t } = useI18n()
const emit = defineEmits(['update:value', 'responsive'])
const builder = inject<BuilderInstance>('builder')

const sliderRef = ref(null)
const activeColorFieldRef = ref<{ openPicker: () => void } | null>(null)
const activeStopIndex = ref(0)
const isDragging = ref(false)
const isHovered = ref(false)
const showTypeInfo = ref(false)
const showDirectionInfo = ref(false)
const showRepeatInfo = ref(false)
const showLengthInfo = ref(false)

const directionValue = computed(() => {
    const val = localSettings.value.direction || '180deg'
    const num = parseFloat(val)
    return isNaN(num) ? 0 : num
})

const directionUnit = computed(() => {
    const val = localSettings.value.direction || '180deg'
    if (val.includes('rad')) return 'rad'
    if (val.includes('grad')) return 'grad'
    if (val.includes('turn')) return 'turn'
    if (val === 'inherit') return 'inherit'
    if (val === 'unset') return 'unset'
    if (val.includes('var')) return 'css var'
    return 'deg'
})

const directionMax = computed(() => {
    const u = directionUnit.value
    if (u === 'turn') return 1
    if (u === 'rad') return 6.28
    if (u === 'grad') return 400
    return 360
})

const isDirectionDirty = computed(() => {
    return (localSettings.value.direction || '180deg') !== '180deg'
})

const handleDirectionNumberInput = (e: Event) => {
    const target = e.target as HTMLInputElement
    updateControl('direction', target.value + directionUnit.value)
}

const updateDirectionValue = (delta: number) => {
    updateControl('direction', (directionValue.value + delta) + directionUnit.value)
}

const updateDirectionValueFromSlider = (val: number) => {
    updateControl('direction', val + directionUnit.value)
}

const setDirectionUnit = (unit: string) => {
    let finalUnit = unit === 'css var' ? 'var(--direction)' : unit
    if (['inherit', 'unset'].includes(unit)) {
        updateControl('direction', unit)
    } else {
        updateControl('direction', directionValue.value + finalUnit)
    }
}

const updateLengthValue = (delta: number) => {
    let current = parseInt(localSettings.value.length || '100') || 100
    let newVal = Math.max(0, Math.min(100, current + delta))
    updateControl('length', newVal + '%')
}

// Default settings for reset
const defaultSettings: Gradient = {
  type: 'linear',
  direction: '180deg',
  repeat: false,
  length: '100%',
  stops: [
    { color: '#2b87da', position: 0 },
    { color: '#29c4a9', position: 100 }
  ]
}

// Internal state
const localSettings = ref<Gradient>({ ...defaultSettings })

// Sync from props
watch(() => props.value, (newVal) => {
  if (newVal && typeof newVal === 'object') {
    // replace entirely for clean state
    localSettings.value = { 
        ...defaultSettings, 
        ...newVal,
        stops: newVal.stops ? [...newVal.stops] : [...defaultSettings.stops]
    }
  } else {
    // Reset to default or placeholder if deleted
    if (props.placeholderValue && typeof props.placeholderValue === 'object') {
        localSettings.value = { ...defaultSettings, ...props.placeholderValue }
    } else {
        localSettings.value = { ...defaultSettings }
    }
  }
}, { immediate: true, deep: true })

const localStops = computed(() => localSettings.value.stops)
const activeStop = computed(() => localStops.value[activeStopIndex.value])

const gradientCSS = computed(() => {
    if (!props.value) return ''
    return generateGradientCSS(localSettings.value)
})
const placeholderCSS = computed(() => {
    if (props.placeholderValue && typeof props.placeholderValue === 'object') {
        return generateGradientCSS(props.placeholderValue)
    }
    return ''
})

const trackCSS = computed(() => {
    const stops = [...localStops.value].sort((a: GradientStop, b: GradientStop) => a.position - b.position)
    const stopString = stops.map((s: GradientStop) => `${s.color} ${s.position}%`).join(', ')
    return `linear-gradient(to right, ${stopString})`
})

const emitUpdate = () => {
  emit('update:value', { ...localSettings.value })
}

const updateStopColor = (color: string) => {
  if (activeStop.value) {
    activeStop.value.color = color
    emitUpdate()
  }
}

const updateControl = <K extends keyof Gradient>(key: K, val: Gradient[K]) => {
  localSettings.value[key] = val
  emitUpdate()
}

const clearGradient = () => {
    localSettings.value = { ...defaultSettings }
    emit('update:value', null)
}

const initDefaultGradient = () => {
    // Standard addition logic (copying from BackgroundField)
    const colors = builder?.globalVariables?.globalColors.value || []
    const baseColor = (colors as { name: string; value: string }[]).find((c) => c.name.toLowerCase().includes('primary'))?.value || '#2EA3F2'
    
    const [c1, c2] = getHarmoniousGradientColors(baseColor)

    const newGradient = {
        type: 'linear',
        direction: '180deg',
        repeat: false,
        length: '100%',
        stops: [
            { color: c1, position: 0 },
            { color: c2, position: 100 }
        ]
    }

    localSettings.value = newGradient
    emit('update:value', newGradient)
}

const duplicateGradient = () => {
    // Implement Duplication as Mirroring for immediate visual change in the same box
    const duplicated = JSON.parse(JSON.stringify(localSettings.value))
    duplicated.stops = (duplicated.stops as GradientStop[]).map((s: GradientStop) => ({
        ...s,
        position: 100 - s.position
    })).sort((a: GradientStop, b: GradientStop) => a.position - b.position)
    
    // Update local state immediately for instant visual feedback
    localSettings.value = duplicated
    
    emit('update:value', duplicated)
}

const handleStopClick = (index: number) => {
    activeStopIndex.value = index
    nextTick(() => {
        activeColorFieldRef.value?.openPicker()
    })
}

const updatePosition = (delta: number) => {
    if (!activeStop.value) return
    let newPos = (activeStop.value.position || 0) + delta
    newPos = Math.max(0, Math.min(100, newPos))
    activeStop.value.position = newPos
    emitUpdate()
}

const addStopAtClick = (e: MouseEvent) => {
  if (isDragging.value) return
  
  const rect = (sliderRef.value as unknown as HTMLElement).getBoundingClientRect()
  const x = (e as MouseEvent).clientX - rect.left
  const position = Math.round((x / rect.width) * 100)
  
  // Find color at this position (linear interpolation)
  const newColor = getColorAtPosition(position)
  
  localSettings.value.stops.push({
    color: newColor,
    position: position
  })
  
  activeStopIndex.value = localSettings.value.stops.length - 1
  emitUpdate()
}

const removeStop = (index: number) => {
  if (localStops.value.length <= 2) return
  localSettings.value.stops.splice(index, 1)
  activeStopIndex.value = Math.max(0, index - 1)
  emitUpdate()
}

const getColorAtPosition = (pos: number) => {
    // Very simple interpolation for now or just take nearest?
    // Let's just find the two nearest stops
    const stops = [...localStops.value].sort((a, b) => a.position - b.position)
    let leftStop = stops[0]
    let rightStop = stops[stops.length - 1]

    for (let i = 0; i < stops.length; i++) {
        if (stops[i].position <= pos) leftStop = stops[i]
        if (stops[i].position >= pos) {
            rightStop = stops[i]
            break
        }
    }

    if (leftStop === rightStop) return leftStop.color
    
    // For now returning left stop color is simplified
    return leftStop.color
}

// Drag logic
let dragStartIndex = -1

const startDrag = (e: MouseEvent, index: number) => {
  isDragging.value = true
  dragStartIndex = index
  activeStopIndex.value = index
  
  window.addEventListener('mousemove', onDrag)
  window.addEventListener('mouseup', endDrag)
}

const onDrag = (e: MouseEvent) => {
  if (!isDragging.value) return
  
  const rect = (sliderRef.value as unknown as HTMLElement).getBoundingClientRect()
  let x = (e as MouseEvent).clientX - rect.left
  let position = Math.round((x / rect.width) * 100)
  
  position = Math.max(0, Math.min(100, position))
  
  localSettings.value.stops[dragStartIndex].position = position
  emitUpdate()
}

const endDrag = () => {
  isDragging.value = false
  dragStartIndex = -1
  window.removeEventListener('mousemove', onDrag)
  window.removeEventListener('mouseup', endDrag)
}

</script>

<style scoped>
.gradient-field {
  width: 100%;
}

.gradient-preview-box {
  margin-bottom: 24px;
}

.gradient-slider-container {
  position: relative;
  height: 24px;
  margin: 10px 0 30px;
  cursor: crosshair;
}

.gradient-track {
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 8px;
  transform: translateY(-50%);
  border-radius: 4px;
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
  border: 1px solid rgba(255,255,255,0.1);
}

.gradient-stop-handle {
  position: absolute;
  top: 50%;
  width: 14px;
  height: 14px;
  transform: translate(-50%, -50%);
  border: 2px solid white;
  border-radius: 2px;
  cursor: grab;
  box-shadow: 0 1px 3px rgba(0,0,0,0.3);
  z-index: 5;
  transition: transform 0.1s;
}

.gradient-stop-handle:active {
  cursor: grabbing;
  transform: translate(-50%, -50%) scale(1.2);
}

.gradient-stop-handle.active {
  z-index: 6;
  border-color: var(--builder-accent);
  box-shadow: 0 0 0 2px rgba(var(--builder-accent-rgb), 0.3), 0 2px 4px rgba(0,0,0,0.3);
}

.handle-inner {
  width: 100%;
  height: 100%;
  border-radius: 1px;
}

.separate-input-box {
    display: flex;
    align-items: center;
    border: 1px solid var(--builder-field-border-clean);
    background: var(--builder-bg-primary);
    border-radius: 4px;
    height: 32px;
}

.separate-unit-dropdown {
    height: 32px;
}

.separate-unit-dropdown .unit-trigger {
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 10px;
    font-size: 12px;
    color: var(--builder-text-secondary);
    cursor: pointer;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-field-border-clean);
    border-radius: 4px;
    min-width: 70px;
    box-sizing: border-box;
}

.separate-unit-box {
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 10px;
    font-size: 12px;
    color: var(--builder-text-secondary);
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-field-border-clean);
    border-radius: 4px;
    min-width: 40px;
    box-sizing: border-box;
}

.input-spinners-inline {
    display: flex;
    flex-direction: column;
    height: 100%;
    border-right: 1px solid var(--builder-field-border-clean);
    padding: 0 4px;
}

.spinner-inline-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin: 0;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--builder-text-muted);
}

.spinner-inline-btn:hover {
    color: var(--builder-accent);
}



.unit-trigger:hover {
    color: var(--builder-text-primary);
}

.unit-menu {
    width: 110px;
    right: 0;
    position: absolute;
    top: 100%;
    background: var(--builder-bg-background);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    margin-top: 1px;
    z-index: 100;
    padding: 4px 0;
    box-shadow: var(--shadow-lg);
}

.unit-item {
    padding: 6px 12px;
    font-size: 11px;
    color: var(--builder-text-secondary);
    cursor: pointer;
}

.unit-item:hover, .unit-item.active {
    background: var(--builder-bg-primary);
    color: var(--builder-text-primary);
}

.active-stop-settings {
  padding: 16px;
  background: transparent;
  border-radius: 8px;
  border: 1px solid var(--builder-field-border-clean);
}

.base-input {
  background: transparent !important;
  border: none !important;
  height: 100%;
  font-size: 13px;
  color: var(--builder-text-primary);
  outline: none;
  padding: 0 8px;
}

/* Hide Native Spinners */
.base-input {
  -moz-appearance: textfield;
  appearance: textfield;
}
.base-input::-webkit-outer-spin-button,
.base-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
  display: none;
}

.position-input-wrapper {
    display: flex;
    align-items: center;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    padding: 0 4px 0 8px;
    height: 32px;
    width: 60px;
}

.position-num-input {
    background: transparent;
    border: none;
    color: var(--builder-text-primary);
    width: 100%;
    outline: none;
    font-size: 13px;
    padding: 0;
    -moz-appearance: textfield;
    appearance: textfield;
}

.position-num-input::-webkit-outer-spin-button,
.position-num-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.position-spinners {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.spinner-btn {
    background: none;
    border: none;
    padding: 0;
    margin: 0;
    cursor: pointer;
    line-height: 0;
    color: var(--builder-text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    height: 12px;
    width: 14px;
    border-radius: 2px;
}

.spinner-btn:hover {
    color: var(--builder-accent);
    background: rgba(255,255,255,0.05);
}

.unit-label {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 11px;
    color: var(--builder-text-muted);
    pointer-events: none;
}

.unit-box {
  background: var(--builder-bg-primary);
  color: var(--builder-text-muted);
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 4px;
}

.mt-6 { margin-top: 24px; }

.field-info-text {
    font-size: 12px;
    color: var(--builder-text-secondary);
    line-height: 1.5;
    padding: 0 2px;
}
</style>

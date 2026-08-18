<template>
  <div class="color-field">
    <!-- Large Preview (Optional, for Responsive Modal Consistency) -->
    <div 
      v-if="showLargePreview"
      class="bg-preview-box color-preview-box" 
      :class="{ 
        'is-inherited': !hasColorValue && placeholderValue,
        'is-empty': !hasColorValue && !placeholderValue 
      }"
      :style="{ backgroundColor: hasColorValue ? value : (placeholderValue || 'transparent') }"
      @click="openPicker"
      @mouseenter="isHovered = true"
      @mouseleave="isHovered = false"
    >
        <div v-if="!hasColorValue && placeholderValue" class="inherited-badge">{{ t('builder.fields.background.color.inherited') }}</div>
        <div v-if="isHovered && (hasColorValue || placeholderValue)" class="preview-actions-overlay">
            <div class="action-btn-group">
                <div class="action-icon-btn" :title="t('builder.contextMenu.reset')" @click.stop="resetColor">
                    <RotateCcw :size="14" />
                </div>
                <div class="action-icon-btn remove" :title="t('builder.contextMenu.delete')" @click.stop="clearColor">
                    <Trash2 :size="14" />
                </div>
            </div>
        </div>
        <div v-if="!hasColorValue && !placeholderValue" class="empty-preview">
            <Plus :size="20" />
            <span>{{ t('builder.fields.background.color.add') }}</span>
        </div>
    </div>

    <!-- Inline Color Input Group (Matches Modal) -->
    <div class="color-input-group">
<!-- Preview & Picker Trigger -->
        <div v-if="!hidePreview" class="color-trigger" @click="openPicker" :title="hasColorValue ? t('builder.fields.color.openPicker') : (placeholderValue ? t('builder.fields.color.inherited') + placeholderValue : t('builder.fields.color.openPicker'))">
             <div class="mini-preview" :class="{ 'has-color': hasColorValue || placeholderValue, 'is-inherited': !hasColorValue && placeholderValue }">
                <div v-if="hasColorValue || placeholderValue" class="mini-color" :style="{ backgroundColor: cssPreviewValue }"></div>
                <Pipette :size="14" class="eyedrop-icon" />
            </div>
        </div>
        


        <!-- Main Input (Hex or Var) -->
        <div class="input-main relative-input-container" ref="mainInputRef">
            <input 
                v-if="inputMode === 'hex'"
                ref="hexInputRef"
                type="text" 
                :value="hexDisplayValue"
                @input="handleHexInput"
                class="text-input"
                spellcheck="false"
            />
            <div v-else class="css-var-input-wrapper">
                <input 
                    type="text" 
                    v-model="cssVarValue"
                    :placeholder="placeholderValue || t('builder.fields.color.placeholder')"
                    class="text-input"
                    :class="{ 'placeholder-is-value': !cssVarValue && placeholderValue }"
                    @focus="showVarSuggestions = true"
                    @blur="handleVarBlur"
                    @input="handleVarInput"
                />
                
                <!-- Suggestions Dropdown (No Teleport) -->
                <div 
                  v-if="showVarSuggestions" 
                  class="var-suggestions-dropdown field-dropdown"
                  :style="varSuggestionsStyle"
                >
                    <div 
                      v-for="suggestion in filteredSuggestions" 
                      :key="suggestion" 
                      class="var-suggestion-item"
                      @mousedown="selectVar(suggestion)"
                    >
                        {{ suggestion }}
                    </div>
                    <div v-if="filteredSuggestions.length === 0" class="var-suggestion-empty">
                        {{ t('builder.fields.color.noMatches') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Mode Toggle Dropdown (Matches Modal) -->
        <div class="mode-toggle" ref="modeDropdownRef">
            <button class="mode-btn" @click.stop="toggleModeDropdown" style="color: var(--builder-accent)">
                {{ inputMode === 'hex' ? t('builder.fields.color.hex') : t('builder.fields.color.var') }}
            </button>
            
            <div 
              v-if="showModeDropdown" 
              class="mode-dropdown-menu field-dropdown"
              :style="modeDropdownStyle"
            >
                <div 
                    class="mode-item" 
                    :class="{ active: inputMode === 'hex' }"
                    @click="setMode('hex')"
                >
                    {{ t('builder.fields.color.hex') }}
                </div>
                <div class="divider" style="margin:0"></div>
                <div 
                    class="mode-item" 
                    :class="{ active: inputMode === 'css_var' }"
                    @click="setMode('css_var')"
                >
                    {{ t('builder.fields.color.var') }}
                </div>
            </div>
        </div>

        <!-- Opacity Input -->
        <div class="opacity-input">
             <input 
                type="number" 
                :value="opacityPercentage" 
                @input="handleOpacityInput"
                @blur="handleOpacityBlur"
                class="opacity-num-input"
                min="0"
                max="100"
             />
             <span class="unit" style="color: var(--builder-text-muted)">%</span>
             <div class="opacity-spinners">
                <button class="spinner-btn" @click.stop="updateOpacity(1)">
                    <ChevronUp :size="10" />
                </button>
                <button class="spinner-btn" @click.stop="updateOpacity(-1)">
                    <ChevronDown :size="10" />
                </button>
             </div>
        </div>
    </div>
    
    <!-- Separated Alpha Slider (Below Input) -->
    <div class="alpha-slider-row">
        <BaseColorSlider 
          v-model="opacityValue"
          variant="alpha"
          :color="alphaTrackColor"
          :min="0"
          :max="100"
        />
    </div>

    <!-- Modal (Optional, if we keep the picker) -->
    <Teleport to="body">
      <ColorPickerModal 
        v-if="showPicker"
        :model-value="value || '#ffffff'"
        :initial-mode="modalMode"
        @update:model-value="handleModalUpdate"
        @close="showPicker = false"
      />
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, defineAsyncComponent, inject, onMounted, onUnmounted } from 'vue'
import type { BuilderInstance, SettingDefinition } from '@/types/builder'
import { useI18n } from 'vue-i18n'
import { parseColor, rgbToHex } from '@/components/builder/core/colorUtils'
import { themeVariables, toCssVarName } from '@/components/builder/core/cssVariables'
import ChevronUp from 'lucide-vue-next/dist/esm/icons/chevron-up.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import Pipette from 'lucide-vue-next/dist/esm/icons/pipette.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import { BaseColorSlider } from '@/components/builder/ui'

const ColorPickerModal = defineAsyncComponent(() => import('../modals/ColorPickerModal.vue'))
const { t } = useI18n()
const builder = inject<BuilderInstance>('builder')
const { globalColors } = builder?.globalVariables || { globalColors: ref([]) }

const props = defineProps<{
  field?: SettingDefinition;
  value?: string;
  defaultValue?: string;
  placeholderValue?: string | null;
  hidePreview?: boolean;
  showLargePreview?: boolean;
}>()

const emit = defineEmits(['update:value'])

const isHovered = ref(false)
const showPicker = ref(false)
const inputMode = ref('hex')
const hexDisplayValue = ref('')
const cssVarValue = ref('')
const showVarSuggestions = ref(false)
const showModeDropdown = ref(false)
const modeDropdownRef = ref<HTMLElement | null>(null)
const mainInputRef = ref<HTMLElement | null>(null)
const hexInputRef = ref<HTMLElement | null>(null)

// Computed Props
const opacityPercentage = computed(() => {
    const val = props.value || ''
    if (val.startsWith('color-mix(')) {
        const match = val.match(/color-mix\(in srgb,\s*.+?,\s*transparent\s*(\d+(\.\d+)?)%\)/)
        if (match) {
            return 100 - parseFloat(match[1]) // if transparent 20%, alpha is 80%
        }
    }
    const { a } = parseColor(val || '#ffffff')
    // Hex parsing returns a from 0-1
    return Math.round(a * 100)
})

const cssPreviewValue = computed(() => {
    // Basic preview logic
    return props.value || props.placeholderValue || '#ffffff'
})

const modalMode = computed(() => inputMode.value as 'hex' | 'css_var' | undefined)

// Check if color has a value
const hasColorValue = computed(() => {
    const val = props.value
    return !!(val && val.trim() !== '' && val !== 'transparent')
})

// Initialize local state from prop
const initFromProp = () => {
    const val = props.value || ''
    if (val.startsWith('var(') || val.startsWith('--') || val.startsWith('color-mix(')) {
        inputMode.value = 'css_var'
        if (val.startsWith('color-mix(')) {
            // Extract variable and opacity from color-mix(in srgb, var(--foo), transparent 20%)
            const match = val.match(/color-mix\(in srgb,\s*(.+?),\s*transparent\s*(\d+(\.\d+)?)%\)/)
            if (match) {
                const inner = match[1].trim()
                cssVarValue.value = inner.startsWith('var(') ? inner.replace(/^var\(\s*(.+?)\s*\)$/, '$1') : inner
            } else {
                cssVarValue.value = val 
            }
        } else {
            cssVarValue.value = val.replace(/^var\(\s*(.+?)\s*\)$/, '$1')
        }
        hexDisplayValue.value = ''
    } else {
        inputMode.value = 'hex'
        cssVarValue.value = ''
        hexDisplayValue.value = val
    }
}

watch(() => props.value, initFromProp, { immediate: true })

// Opacity Logic
const opacityValue = computed({
    get: () => opacityPercentage.value,
    set: (val) => processOpacityUpdate(val)
})

const alphaTrackColor = computed(() => {
   const val = props.value || '#ffffff'
   
   if (inputMode.value === 'hex') {
       const { r, g, b } = parseColor(val)
       return `rgb(${r},${g},${b})`
   }
   // For variables, use white as fallback
   return '#ffffff'
})

// Suggestions Logic
const allSuggestions = computed(() => {
    const staticVars = themeVariables || []
    const colors = globalColors?.value || []
    const dynamicVars = (colors as { name: string }[]).map((c) => toCssVarName(c?.name || ''))
    return [...new Set([...staticVars, ...dynamicVars])]
})

const filteredSuggestions = computed(() => {
    if (!cssVarValue.value) return allSuggestions.value
    const val = cssVarValue.value.toLowerCase()
    return allSuggestions.value.filter(v => v.includes(val))
})

const selectVar = (val: string) => {
    cssVarValue.value = val
    showVarSuggestions.value = false
    emitUpdateVar()
}

const handleVarBlur = () => {
    setTimeout(() => {
        showVarSuggestions.value = false
    }, 200)
}

// Handlers
const openPicker = () => showPicker.value = true
const resetColor = () => emit('update:value', null)
const clearColor = () => emit('update:value', 'transparent')

const handleModalUpdate = (newValue: string) => {
    emit('update:value', newValue)
    // Update local state based on new value
    if (newValue && (newValue.startsWith('var(') || newValue.startsWith('--') || newValue.startsWith('color-mix('))) {
        inputMode.value = 'css_var'
        if (newValue.startsWith('color-mix(')) {
            // Extract variable from color-mix(in srgb, var(--foo), transparent 20%)
            const match = newValue.match(/color-mix\(in srgb,\s*(.+?),\s*transparent\s*(\d+(\.\d+)?)%\)/)
            if (match) {
                const inner = match[1].trim()
                cssVarValue.value = inner.startsWith('var(') ? inner.replace(/^var\(\s*(.+?)\s*\)$/, '$1') : inner
            } else {
                cssVarValue.value = newValue
            }
        } else {
            cssVarValue.value = newValue.replace(/^var\(\s*(.+?)\s*\)$/, '$1')
        }
        hexDisplayValue.value = ''
    } else {
        inputMode.value = 'hex'
        hexDisplayValue.value = newValue
        cssVarValue.value = ''
    }
}

const toggleModeDropdown = () => showModeDropdown.value = !showModeDropdown.value

const setMode = (mode: string) => {
    if (inputMode.value === mode) {
        showModeDropdown.value = false
        return
    }

    inputMode.value = mode
    showModeDropdown.value = false
    
    // Clear the opposite value when switching modes
    if (mode === 'hex') {
        cssVarValue.value = ''
        // Keep hexDisplayValue as is or empty
    } else {
        hexDisplayValue.value = ''
        cssVarValue.value = ''
    }
    
    // Open picker when switching to Var mode
    if (mode === 'css_var') {
        showPicker.value = true
    }
}

// Dropdown Positioning (Simplified without Teleport)
const modeDropdownStyle = ref({})
const varSuggestionsStyle = ref({})

// Click Outside for Mode Dropdown
const handleClickOutside = (event: MouseEvent) => {
    if (showModeDropdown.value && modeDropdownRef.value && !(modeDropdownRef.value as HTMLElement).contains(event.target as Node)) {
        showModeDropdown.value = false
    }
    if (showVarSuggestions.value && mainInputRef.value && !(mainInputRef.value as HTMLElement).contains(event.target as Node) && !(event.target as HTMLElement).closest('.var-suggestions-dropdown')) {
        showVarSuggestions.value = false
    }
}

onMounted(() => {
    window.addEventListener('mousedown', handleClickOutside)
})
onUnmounted(() => {
    window.removeEventListener('mousedown', handleClickOutside)
})

const handleHexInput = (e: Event) => {
    const target = e.target as HTMLInputElement
    hexDisplayValue.value = target.value
    emit('update:value', target.value)
}

const handleVarInput = (_e: Event) => {
    emitUpdateVar()
}

const emitUpdateVar = () => {
    let val = cssVarValue.value
    if (val && val.startsWith('--')) {
        val = `var(${val})`
    }
    
    emit('update:value', val)
}

const handleOpacityInput = (e: Event) => {
    const target = e.target as HTMLInputElement
    let val = parseInt(target.value)
    if (isNaN(val)) return 
    processOpacityUpdate(val)
}

const handleOpacityBlur = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.value === '') {
        target.value = (opacityPercentage.value).toString()
    }
}

const updateOpacity = (delta: number) => {
    const current = opacityPercentage.value
    processOpacityUpdate(current + delta)
}

const processOpacityUpdate = (alpha: number) => {
    if (isNaN(alpha)) alpha = 100
    if (alpha < 0) alpha = 0
    if (alpha > 100) alpha = 100
    
    if (inputMode.value === 'hex') {
        const { r, g, b } = parseColor(props.value || '#ffffff')
        const a = alpha / 100
        let newVal = rgbToHex(r, g, b)
        if (a < 1) {
            newVal = `rgba(${r}, ${g}, ${b}, ${a})`
        }
        emit('update:value', newVal)
    } else {
        // Var mode
        let v = cssVarValue.value
        if (v && v.startsWith('--')) v = `var(${v})`
        
        if (v) {
            let newVal = v
            if (alpha < 100) {
                 newVal = `color-mix(in srgb, ${v}, transparent ${100 - alpha}%)`
            }
            emit('update:value', newVal)
        }
    }
}


defineExpose({ openPicker })
</script>

<style scoped>
.color-field {
    flex-direction: column;
    width: 100%;
}

.color-input-group {
    display: flex;
    align-items: center;
    height: 32px;
    background: transparent;
    transition: all 0.2s;
    position: relative;
    z-index: 10;
    width: 100%;
    margin-bottom: 4px;
}

.color-trigger {
    padding: 0 8px 0 0;
    height: 100%;
    display: flex;
    align-items: center;
    cursor: pointer;
}

.mini-preview {
    width: 32px; 
    height: 32px;
    border-radius: 4px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.mini-preview:hover {
    border-color: var(--builder-accent);
}

.mini-preview.has-color {
    background-image: 
      linear-gradient(45deg, #555 25%, transparent 25%), 
      linear-gradient(-45deg, #555 25%, transparent 25%), 
      linear-gradient(45deg, transparent 75%, #555 75%), 
      linear-gradient(-45deg, transparent 75%, #555 75%);
    background-size: 8px 8px;
    background-position: 0 0, 0 4px, 4px -4px, -4px 0px;
}

.eyedrop-icon {
    color: var(--builder-text-muted);
    opacity: 0.6;
    position: relative;
    z-index: 2;
    transition: all 0.2s;
}

.mini-preview.has-color .eyedrop-icon {
    color: #fff;
    opacity: 0.9;
    filter: drop-shadow(0 1px 2px rgba(0,0,0,0.6));
}

.mini-preview:hover .eyedrop-icon {
    opacity: 1;
    transform: scale(1.1);
}

.mini-preview:hover.has-color .eyedrop-icon {
     color: #fff;
}

.mini-color {
    position: absolute;
    inset: 0;
    border-radius: 3px;
}

.mini-preview.is-inherited .mini-color {
    opacity: 0.5;
}

.mini-preview.is-inherited::after {
    content: '';
    position: absolute;
    inset: 0;
    border: 1px dashed rgba(255,255,255,0.3);
    border-radius: 3px;
    pointer-events: none;
}

.input-main {
    flex: 1;
    height: 32px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-right: none;
    border-radius: 4px 0 0 4px;
    display: flex;
    align-items: center;
    position: relative;
    padding: 0 8px;
}

.relative-input-container { position: relative; }

.text-input {
    width: 100%;
    height: 100%;
    background: transparent;
    border: none;
    padding: 0;
    font-size: 13px;
    font-family: inherit;
    color: var(--builder-text-primary);
    outline: none;
}

.text-input.placeholder-is-value::placeholder {
    color: var(--builder-text-muted);
    opacity: 0.7;
}

.css-var-input-wrapper {
    width: 100%;
    height: 100%;
}

.mode-toggle {
    position: relative;
    width: 60px;
    height: 32px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-right: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.mode-btn {
    width: 100%;
    height: 100%;
    background: transparent;
    border: none;
    color: var(--builder-accent);
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mode-btn:hover {
    background: var(--builder-bg-primary);
}

.mode-dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%; /* Match parent width */
    background: var(--builder-bg-background);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    margin-top: 4px;
    z-index: 100;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.mode-item {
    padding: 6px 8px;
    font-size: 11px;
    color: var(--builder-text-secondary);
    cursor: pointer;
    text-align: center;
}

.mode-item:hover, .mode-item.active {
    background: var(--builder-bg-primary);
    color: var(--builder-text-primary);
}

.opacity-input {
    width: 70px;
    height: 32px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 0 4px 4px 0;
    padding: 0 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.opacity-num-input {
    background: transparent;
    border: none;
    color: var(--builder-text-primary);
    width: 100%;
    text-align: right;
    outline: none;
    font-size: 13px;
    padding: 0;
}

/* Hide Native Spinners */
.opacity-num-input {
  -moz-appearance: textfield;
  appearance: textfield;
}
.opacity-num-input::-webkit-outer-spin-button,
.opacity-num-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
  display: none;
}

.unit {
    font-size: 11px;
    color: var(--builder-text-muted);
    padding: 0 2px;
}

.opacity-spinners {
    display: flex;
    flex-direction: column;
    justify-content: center;
    margin-left: 2px;
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
    height: 10px;
    width: 14px;
}

.spinner-btn:hover {
    color: var(--builder-accent);
    background-color: var(--builder-bg-secondary);
}

/* Suggestions Dropdown */
.var-suggestions-dropdown {
  position: absolute;
  top: 100%; 
  left: 0; 
  width: 100%; /* Match input width */
  min-width: 200px;
  background-color: var(--builder-bg-background);
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  max-height: 160px;
  overflow-y: auto;
  z-index: 100;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  margin-top: 4px;
}

.var-suggestion-item {
  padding: 6px 10px;
  font-size: 12px;
  cursor: pointer;
  color: var(--builder-text-secondary);
  white-space: nowrap;
}

.var-suggestion-item:hover {
  background-color: var(--builder-bg-secondary);
  color: var(--builder-text-primary);
}

.var-suggestion-empty {
  padding: 8px;
  font-size: 11px;
  color: var(--builder-text-muted);
  text-align: center;
}

/* Alpha Slider Row - wrapper for BaseColorSlider */
.alpha-slider-row {
    padding: 8px 0 12px 40px; /* Align with input after larger preview box */
}

/* Large Preview Box Styles (Copied/Adapted from BackgroundField) */
.bg-preview-box {
    position: relative;
    width: 100%;
    min-height: 80px; /* Bit smaller than panel to fit modal better */
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    margin-bottom: 8px;
    cursor: crosshair;
}

.bg-preview-box:hover {
    border-color: var(--builder-accent);
}

.bg-preview-box.is-empty {
    border-style: dashed;
}

.bg-preview-box.is-inherited::after {
    content: '';
    position: absolute;
    inset: 0;
    border: 1px dashed rgba(0,0,0,0.2);
    pointer-events: none;
}

.inherited-badge {
    position: absolute;
    bottom: 6px;
    left: 6px;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    pointer-events: none;
    z-index: 5;
}

.preview-actions-overlay {
    position: absolute;
    top: 6px;
    right: 6px;
    z-index: 10;
}

.action-btn-group {
    display: flex;
    background: white;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.action-icon-btn {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #64748b;
}

.action-icon-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.action-icon-btn.remove:hover {
    background: #fee2e2;
    color: #ef4444;
}

.empty-preview {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: var(--builder-text-muted);
    font-size: 11px;
}
</style>

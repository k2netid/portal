<template>
  <BaseModal 
    :is-open="true" 
    :title="t('builder.modals.colorPicker.title')" 
    :width="320" 
    draggable 
    no-backdrop
    placement="right-sidebar"
    :close-on-backdrop="false"
    @close="$emit('close')"
    class="color-picker-modal-overrides"
  >
      <!-- View Switcher Tabs -->
      <div class="tabs-header">
          <button 
              class="tab-btn" 
              :class="{ active: colorView === 'custom' }"
              @click="colorView = 'custom'"
          >
              <Layout :size="14" />
              <span>{{ t('builder.modals.colorPicker.custom') }}</span>
          </button>
          <button 
              class="tab-btn" 
              :class="{ active: colorView === 'presets' }"
              @click="colorView = 'presets'"
          >
              <Sparkles :size="14" />
              <span>{{ t('builder.modals.colorPicker.material') }}</span>
          </button>
      </div>

      <!-- Custom Color View -->
      <div v-if="colorView === 'custom'" class="tab-content custom-view">
          <!-- Saturation / Value Area -->
          <div 
            v-if="inputMode === 'hex'"
            class="sv-area" 
            :style="{ backgroundColor: hueColor }"
            ref="svArea"
            @mousedown="startDragSV"
          >
            <div class="sv-white"></div>
            <div class="sv-black"></div>
            <div 
              class="sv-handle"
              :style="{ left: s + '%', top: (100 - v) + '%' }"
            ></div>
          </div>

          <!-- Controls Row (Hue Only) -->
          <div v-if="inputMode === 'hex'" class="picker-controls-row">
            <!-- Current Color Preview Box -->
            <div 
              class="current-color-preview"
              :style="{ backgroundColor: currentColor }"
            ></div>
            
            <!-- Sliders Column (Hue Only) -->
            <div class="sliders-col">
              <!-- Hue Slider -->
              <BaseColorSlider 
                v-model="hue"
                variant="hue"
                :min="0"
                :max="360"
                @update:model-value="updateHue"
              />
            </div>
          </div>

          <!-- Input Group (Hex / CSS Var) -->
          <div class="input-group-row generic-relative-container">
              <!-- Main Input -->
              <div class="input-main relative-input-container">
                  <input 
                     ref="mainInputRef"
                     v-if="inputMode === 'hex'"
                     type="text" 
                     :value="hexValue"
                     @input="handleHexInput" 
                     class="text-input"
                     spellcheck="false"
                  />
                  <div v-else class="css-var-input-wrapper">
                      <input 
                         type="text" 
                         v-model="cssVarValue"
                         :placeholder="t('builder.fields.color.placeholder')"
                         class="text-input"
                         @focus="showVarSuggestions = true"
                         @blur="handleVarBlur"
                      />
                      <!-- Suggestions Dropdown -->
                      <div v-if="showVarSuggestions" class="var-suggestions-dropdown">
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

              <!-- Mode Toggle -->
              <div class="mode-toggle" ref="modeDropdownRef">
                  <button class="mode-btn" @click="toggleModeDropdown">
                      {{ inputMode === 'hex' ? t('builder.fields.color.hex') : t('builder.fields.color.var') }}
                  </button>
                  
                  <div v-if="showModeDropdown" class="mode-dropdown-menu">
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
              <div class="opacity-input-wrapper">
                 <input 
                    type="number" 
                    min="0" 
                    max="100"
                    :value="Math.round(alpha * 100)"
                    @input="updateAlphaFromInput"
                    class="opacity-num-input"
                 />
                 <span class="unit">%</span>
                 <div class="opacity-spinners">
                      <button class="spinner-btn" @click="incrementAlpha">
                          <ChevronUp :size="10" />
                      </button>
                      <button class="spinner-btn" @click="decrementAlpha">
                          <ChevronDown :size="10" />
                      </button>
                  </div>
             </div>
          </div>

          <!-- Separated Alpha Slider (Below Input) -->
          <div class="alpha-slider-row">
              <BaseColorSlider 
                v-model="alphaPercent"
                variant="alpha"
                :color="hueColor"
                :min="0"
                :max="100"
                @update:model-value="updateAlphaFromSlider"
              />
          </div>

          <div class="divider"></div>

          <!-- Collapsible Color Filters (Inside Custom View) -->
          <div class="accordion-section">
              <button class="accordion-header" @click="toggleFilters">
                  <span>{{ t('builder.modals.colorPicker.filters') }}</span>
                   <component :is="showFilters ? ChevronUp : ChevronDown" :size="14" />
              </button>
              
              <div v-if="showFilters" class="filters-content">
                  <div class="filter-row">
                      <span class="filter-label">{{ t('builder.modals.colorPicker.hsv.h') }}</span>
                      <div class="filter-range-wrapper">
                          <BaseColorSlider 
                            v-model="hue"
                            variant="hue"
                            :min="0"
                            :max="360"
                            @update:model-value="updateHue"
                          />
                      </div>
                       <div class="filter-value">{{ Math.round(hue) }}°</div>
                  </div>
                  <div class="filter-row">
                      <span class="filter-label">{{ t('builder.modals.colorPicker.hsv.s') }}</span>
                      <div class="filter-range-wrapper">
                          <BaseColorSlider 
                            v-model="s"
                            variant="saturation"
                            :min="0"
                            :max="100"
                            @update:model-value="emitUpdate"
                          />
                      </div>
                       <div class="filter-value">{{ Math.round(s) }}%</div>
                  </div>
                  <div class="filter-row">
                      <span class="filter-label">{{ t('builder.modals.colorPicker.hsv.v') }}</span>
                      <div class="filter-range-wrapper">
                          <BaseColorSlider 
                            v-model="v"
                            variant="value"
                            :min="0"
                            :max="100"
                            @update:model-value="emitUpdate"
                          />
                      </div>
                       <div class="filter-value">{{ Math.round(v) }}%</div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Material Presets View -->
      <div v-if="colorView === 'presets'" class="tab-content presets-view">
          <div class="presets-content-main">
              <!-- Family Selector (Horizontal Scroll) -->
              <div class="family-selector">
                  <div 
                      v-for="family in MATERIAL_COLORS" 
                      :key="family.id"
                      class="family-swatch"
                      :class="{ active: selectedFamilyId === family.id }"
                      :style="{ backgroundColor: family.base }"
                      @click="selectedFamilyId = family.id"
                  ></div>
              </div>

              <!-- Variants Grid -->
              <div class="variants-grid">
                  <div 
                      v-for="(hex, shade) in selectedFamily.variants" 
                      :key="shade"
                      class="variant-item"
                      @click="selectPreset(hex as string)"
                  >
                      <div class="variant-preview" :style="{ backgroundColor: hex }"></div>
                      <span class="variant-label">{{ shade }}</span>
                  </div>
              </div>

              <!-- Smart Pairings -->
              <div v-if="smartPairings.length" class="pairings-section">
                  <span class="pairing-title">Smart Pairings</span>
                  <div class="pairings-list">
                      <div 
                          v-for="pairing in smartPairings" 
                          :key="pairing.id"
                          class="pairing-item"
                          @click="selectedFamilyId = pairing.id"
                      >
                          <div class="pairing-preview" :style="{ backgroundColor: pairing.base }"></div>
                          <span class="pairing-label">{{ pairing.name }}</span>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <div class="divider" style="margin: 0"></div>

      <!-- Global Colors Section -->
      <div class="global-colors-section">
          <!-- ... content ... -->
          <!-- ... content ... -->
          <div class="section-header">
              <span class="section-title">{{ t('builder.modals.colorPicker.globalColors') }}</span>
              <div class="view-toggles">
                  <button :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'">
                      <LayoutGrid :size="14" />
                  </button>
                  <button :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'">
                      <List :size="14" />
                  </button>
              </div>
          </div>

          <!-- Grid View -->
          <div v-if="viewMode === 'grid'" class="global-grid">
              <div 
                  v-for="color in globalColors" 
                  :key="color.id"
                  class="color-sq"
                  :style="{ backgroundColor: (color.value as string) }"
                  :title="color.name"
                  @click="selectPreset(color.value as string)"
              >
                  <div class="global-corner"></div>
              </div>
              
              <button class="add-global-btn" @click="addGlobalColor">
                  <Plus :size="14" />
              </button>
          </div>

          <!-- List View -->
          <div v-else class="global-list">
              <div 
                  v-for="color in globalColors" 
                  :key="color.id"
                  class="global-list-item"
                  @click="selectPreset(color.value as string)"
              >
                  <div class="color-preview-sm" :style="{ backgroundColor: (color.value as string) }">
                      <div class="global-corner-sm"></div>
                  </div>
                  <span class="color-name">{{ color.name }}</span>
                  <span class="color-hex">{{ color.value }}</span>
              </div>
              <div class="global-list-item add-item" @click="addGlobalColor">
                  <div class="add-icon-sm"><Plus :size="12" /></div>
                  <span>{{ t('builder.modals.colorPicker.addGlobal') }}</span>
              </div>
          </div>
      </div>


      <!-- Bottom Link -->
      <div class="bottom-actions">
          <button class="manage-link" @click="openGlobalManager">
              {{ t('builder.fields.actions.manageGlobal') }}
          </button>
      </div>
</BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, inject } from 'vue';
import { useI18n } from 'vue-i18n';

import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import ChevronUp from 'lucide-vue-next/dist/esm/icons/chevron-up.js';
import LayoutGrid from 'lucide-vue-next/dist/esm/icons/layout-grid.js';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import { parseColor, hsvToRgb, rgbToHex, rgbToHsv, hexToRgb } from '@/components/builder/core/colorUtils';
import { themeVariables, toCssVarName } from '@/components/builder/core/cssVariables';
import { BaseModal, BaseColorSlider } from '@/components/builder/ui';
import { MATERIAL_COLORS } from '@/components/builder/core/MaterialColors';
import type { BuilderInstance, GlobalVariable } from '@/types/builder';

// Props
interface Props {
  modelValue?: string;
  initialMode?: 'hex' | 'css_var';
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '#ffffff',
  initialMode: 'hex'
});

// Emits
const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
  (e: 'close'): void;
}>();

const { t } = useI18n();

// Inject Builder
const builder = inject<BuilderInstance>('builder');

// State
const hue = ref(0);
const s = ref(0);
const v = ref(100);
const alpha = ref(1);
const isDragging = ref(false);
const svArea = ref<HTMLElement | null>(null);

const showFilters = ref(false);
const viewMode = ref<'grid' | 'list'>('grid');

// Tab/View State
const colorView = ref<'custom' | 'presets'>('custom');

// Material Presets State
const selectedFamilyId = ref('indigo');
const selectedFamily = computed(() => MATERIAL_COLORS.find(f => f.id === selectedFamilyId.value) || MATERIAL_COLORS[0]);
const smartPairings = computed(() => {
    const pairings = selectedFamily.value?.pairings || [];
    return pairings.map(pid => MATERIAL_COLORS.find(f => f.id === pid)).filter((p): p is typeof MATERIAL_COLORS[0] => !!p);
});

// Input Mode State - initialize from prop
const inputMode = ref<'hex' | 'css_var'>(props.initialMode);
const cssVarValue = ref('');
const showModeDropdown = ref(false);
const modeDropdownRef = ref<HTMLElement | null>(null);

// Shared Global Colors
const globalColors = computed(() => builder?.globalVariables.globalColors.value || []);

// Suggestions State
const showVarSuggestions = ref(false);

const allSuggestions = computed(() => {
    // 1. Static Theme Variables
    const staticVars = themeVariables || [];
    
    // 2. Dynamic Global Colors (converted to --slug)
    const colors = globalColors.value || [];
    const dynamicVars = colors.map((c: GlobalVariable) => toCssVarName(c?.name || ''));
    
    // Merge and deduplicate
    return [...new Set([...staticVars, ...dynamicVars])];
});

const filteredSuggestions = computed(() => {
    if (!cssVarValue.value) return allSuggestions.value;
    const val = cssVarValue.value.toLowerCase();
    return allSuggestions.value.filter(v => v.includes(val));
});

const selectVar = (val: string) => {
    // Switch to CSS Var mode and clear hex state
    inputMode.value = 'css_var';
    cssVarValue.value = val;
    showVarSuggestions.value = false;
    // Reset alpha to 1 when selecting new variable
    alpha.value = 1;
    emitUpdate();
};

const handleVarBlur = () => {
    setTimeout(() => {
        showVarSuggestions.value = false;
    }, 200);
};

const toggleModeDropdown = () => showModeDropdown.value = !showModeDropdown.value;

const setMode = (mode: 'hex' | 'css_var') => {
    inputMode.value = mode;
    showModeDropdown.value = false;
    emitUpdate();
};

// Click Outside for Mode Dropdown
const handleClickOutside = (event: Event) => {
    if (showModeDropdown.value && modeDropdownRef.value && !modeDropdownRef.value.contains(event.target as Node)) {
        showModeDropdown.value = false;
    }
};

const toggleFilters = () => showFilters.value = !showFilters.value;

// Computed
const hueColor = computed(() => `hsl(${hue.value}, 100%, 50%)`);

const currentColor = computed(() => {
  if (inputMode.value === 'css_var') {
      // Cannot easily preview var without context, return transparent or approximation
      // If we could resolve it, we would.
      return 'transparent';
  }
  const { r, g, b } = hsvToRgb(hue.value / 360, s.value / 100, v.value / 100);
  if (alpha.value < 1) {
    return `rgba(${r}, ${g}, ${b}, ${alpha.value})`;
  }
  return rgbToHex(r, g, b);
});

const hexValue = computed(() => {
  const { r, g, b } = hsvToRgb(hue.value / 360, s.value / 100, v.value / 100);
  return rgbToHex(r, g, b).replace('#', '');
});
// Track if this is the first initialization
const isFirstInit = ref(true);

// Initialize
const initFromProp = () => {
  const val = props.modelValue || '';
  
  // On first init, prioritize initialMode prop over value detection
  if (isFirstInit.value) {
    isFirstInit.value = false;
    
    // If initialMode is css_var, use it regardless of value
    if (props.initialMode === 'css_var') {
      inputMode.value = 'css_var';
      // Parse cssVarValue and alpha from value
      parseVarValue(val);
      return;
    }
  }
  
  // Detect color-mix format (CSS var with alpha)
  if (val.startsWith('color-mix(')) {
    inputMode.value = 'css_var';
    parseVarValue(val);
    return;
  }
  
  // Normal detection based on value
  if (val.startsWith('var(') || val.startsWith('--')) {
      inputMode.value = 'css_var';
      cssVarValue.value = val.replace(/^var\(\s*(.+?)\s*\)$/, '$1');
      alpha.value = 1;
      return;
  }

  inputMode.value = 'hex';
  const { r, g, b, a } = parseColor(val);
  const hsv = rgbToHsv(r, g, b);
  hue.value = hsv.h;
  s.value = hsv.s;
  v.value = hsv.v;
  alpha.value = a;
};

// Helper to parse CSS var value with potential color-mix
const parseVarValue = (val: string) => {
  if (val.startsWith('color-mix(')) {
    // Extract variable and opacity from color-mix(in srgb, var(--foo), transparent 20%)
    const match = val.match(/color-mix\(in srgb,\s*(.+?),\s*transparent\s*(\d+(\.\d+)?)%\)/);
    if (match) {
      const inner = match[1].trim();
      cssVarValue.value = inner.startsWith('var(') ? inner.replace(/^var\(\s*(.+?)\s*\)$/, '$1') : inner;
      const transparentPercent = parseFloat(match[2]);
      alpha.value = (100 - transparentPercent) / 100;
    } else {
      cssVarValue.value = '';
      alpha.value = 1;
    }
  } else if (val.startsWith('var(') || val.startsWith('--')) {
    cssVarValue.value = val.replace(/^var\(\s*(.+?)\s*\)$/, '$1');
    alpha.value = 1;
  } else {
    cssVarValue.value = '';
    alpha.value = 1;
  }
};

watch(() => props.modelValue, initFromProp, { immediate: true });

// Drag Logic (SV)
const handleDrag = (e: MouseEvent) => {
  if (!isDragging.value || !svArea.value) return;
  const rect = svArea.value.getBoundingClientRect();
  const x = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
  const y = Math.max(0, Math.min(e.clientY - rect.top, rect.height));
  s.value = (x / rect.width) * 100;
  v.value = 100 - ((y / rect.height) * 100);
  emitUpdate();
};

const startDragSV = (e: MouseEvent) => {
  isDragging.value = true;
  handleDrag(e);
  window.addEventListener('mousemove', handleDrag);
  window.addEventListener('mouseup', stopDragSV);
};

const stopDragSV = () => {
  isDragging.value = false;
  window.removeEventListener('mousemove', handleDrag);
  window.removeEventListener('mouseup', stopDragSV);
};

// Updates
const updateHue = () => emitUpdate();

// Alpha as percentage for BaseColorSlider (0-100)
const alphaPercent = computed({
  get: () => Math.round(alpha.value * 100),
  set: (val: number) => {
    alpha.value = val / 100;
    emitUpdate();
  }
});

const updateAlphaFromSlider = (val: number) => {
  alpha.value = val / 100;
  emitUpdate();
};

const updateAlphaFromInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  const val = parseFloat(target.value);
  if (!isNaN(val)) {
    alpha.value = Math.max(0, Math.min(100, val)) / 100;
    emitUpdate();
  }
};

const handleHexInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    let val = target.value;
    if (!val.startsWith('#')) val = '#' + val;
    const rgb = hexToRgb(val);
    if (rgb) {
        const hsv = rgbToHsv(rgb.r, rgb.g, rgb.b);
        hue.value = hsv.h;
        s.value = hsv.s;
        v.value = hsv.v;
        emitUpdate();
    }
};

const selectPreset = (color: string) => {
  // Switch to Hex mode and clear CSS var state
  inputMode.value = 'hex';
  cssVarValue.value = '';
  
  const { r, g, b, a } = parseColor(color);
  const hsv = rgbToHsv(r, g, b);
  hue.value = hsv.h;
  s.value = hsv.s;
  v.value = hsv.v;
  alpha.value = a;

  // Switch back to custom view for fine-tuning
  colorView.value = 'custom';
  
  emitUpdate();
};

const emitUpdate = () => {
  if (inputMode.value === 'css_var') {
      let val = cssVarValue.value;
      // Wrap in var() if it starts with --
      if (val && val.startsWith('--')) {
          val = `var(${val})`;
      }
      // Apply alpha using color-mix if alpha < 1
      if (val && alpha.value < 1) {
          const transparentPercent = Math.round((1 - alpha.value) * 100);
          val = `color-mix(in srgb, ${val}, transparent ${transparentPercent}%)`;
      }
      emit('update:modelValue', val);
      return;
  }

  const { r, g, b } = hsvToRgb(hue.value / 360, s.value / 100, v.value / 100);
  let val = rgbToHex(r, g, b);
  if (alpha.value < 1) {
      val = `rgba(${r}, ${g}, ${b}, ${alpha.value})`;
  }
  emit('update:modelValue', val);
};

// Global Actions
const addGlobalColor = () => {
    // Open Global Manager to add a new color
    openGlobalManager();
};

const openGlobalManager = () => {
    if (builder && builder.activePanel) {
        builder.activePanel.value = 'global_variables';
        if (builder.globalAction) {
            builder.globalAction.value = { type: 'add_color', payload: { timestamp: Date.now() } };
        }
    }
    emit('close');
};

// Opacity Spinners
const incrementAlpha = () => {
    alpha.value = Math.min(1, Math.round((alpha.value + 0.01) * 100) / 100);
    emitUpdate();
};

const decrementAlpha = () => {
    alpha.value = Math.max(0, Math.round((alpha.value - 0.01) * 100) / 100);
    emitUpdate();
};

onMounted(() => {
    window.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener('mousemove', handleDrag);
  window.removeEventListener('mouseup', stopDragSV);
  window.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Override BaseModal body padding */
.color-picker-modal-overrides :deep(.base-modal-body) {
    padding: 0;
    display: flex;
    flex-direction: column;
}

/* Header styles handled by BaseModal */

/* SV Area */
.sv-area {
  position: relative;
  width: 100%;
  height: 180px;
  cursor: crosshair;
}

.sv-white {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: linear-gradient(to right, #fff, rgba(255,255,255,0));
}

.sv-black {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: linear-gradient(to top, #000, rgba(0,0,0,0));
}

.sv-handle {
  position: absolute;
  width: 16px;
  height: 16px;
  border: 4px solid white;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0,0,0,0.5);
  transform: translate(-50%, -50%);
  pointer-events: none;
  background: transparent;
}

/* Controls Row */
.picker-controls-row {
    padding: 16px 16px 12px 16px;
    display: flex;
    gap: 12px;
    align-items: center;
}

.current-color-preview {
    width: 24px;
    height: 24px;
    border-radius: 3px; 
    background-color: blue; /* dynamic */
    flex-shrink: 0;
}

.sliders-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Custom Sliders */
.slider-track {
    height: 12px;
    border-radius: 6px;
    position: relative;
    width: 100%;
}

.hue-track {
    background: linear-gradient(to right, #f00 0%, #ff0 17%, #0f0 33%, #0ff 50%, #00f 67%, #f0f 83%, #f00 100%);
}

.alpha-track {
      background: 
    linear-gradient(45deg, var(--builder-border) 25%, transparent 25%), 
    linear-gradient(-45deg, var(--builder-border) 25%, transparent 25%),
    linear-gradient(45deg, transparent 75%, var(--builder-border) 75%),
    linear-gradient(-45deg, transparent 75%, var(--builder-border) 75%);
  background-size: 8px 8px;
  background-color: var(--builder-bg-primary);
}

.alpha-gradient {
    position: absolute;
    inset: 0;
    border-radius: 6px;
}

.invisible-slider {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    margin: 0;
}

.slider-handle-c {
    position: absolute;
    top: 50%;
    width: 14px;
    height: 14px;
    background: white;
    border: 2px solid white;
    border-radius: 50%;
    transform: translate(-50%, -50%);
    box-shadow: 0 1px 2px rgba(0,0,0,0.3);
    pointer-events: none;
    background: var(--builder-accent); /* inner blue */
}

/* Hex Opacity Row */
.hex-opacity-row {
    padding: 16px;
    display: flex;
    gap: 0; /* Connected inputs */
}

.hex-input-wrapper {
    flex: 1;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-right: none;
    border-radius: 4px 0 0 4px;
    padding: 6px 12px;
    display: flex;
    align-items: center;
}

.hex-text-input {
    background: transparent;
    border: none;
    color: var(--builder-text-primary);
    width: 100%;
    font-family: monospace;
    font-weight: 500;
    outline: none;
    text-transform: uppercase;
}

.opacity-input-wrapper {
    width: 85px; /* Increased from 60px to give room for spinners */
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-left: 1px solid var(--builder-border); /* Fix separate border color */
    border-radius: 0 4px 4px 0;
    padding: 2px 8px; /* Reduce vertical padding to fit spinners if needed */
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.opacity-num-input {
    background: transparent;
    border: none;
    color: var(--builder-text-secondary);
    width: 100%;
    text-align: right;
    outline: none;
}

.unit {
    color: var(--builder-text-secondary);
    font-size: 12px;
    margin-left: 2px;
}

/* Filters */
.accordion-section {
    padding: 0 16px;
}

.accordion-header {
    width: 100%;
    background: none;
    border: none;
    color: var(--builder-accent);
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    padding: 8px 0;
}

.filters-content {
    padding-bottom: 12px;
}

.filter-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 12px;
}

.filter-label {
    width: 70px;
    color: var(--builder-text-primary);
}

.filter-range-wrapper {
    flex: 1;
    height: 16px;
    display: flex;
    align-items: center;
}

.mock-range-track {
    width: 100%;
    height: 4px;
    background: var(--builder-border);
    position: relative;
    border-radius: 2px;
}
.mock-range-track::after {
    /* Handle */
    content: '';
    position: absolute;
    left: 0; top: 50%; transform: translateY(-50%);
    width: 30px;
    height: 8px;
    border: 2px solid white;
    background: transparent;
    border-radius: 4px;
    /* Screenshot 3 shows complex handle: White box with vertical divider. 
       Let's just put a styled div. */
    border: 1px solid var(--builder-text-primary);
    background: var(--builder-bg-primary);
}

.mock-range-track::before {
    /* Center line in handle */
    content: '';
    position: absolute;
    left: 15px; top: 50%; transform: translate(-50%, -50%);
    width: 1px; height: 10px; background: var(--builder-text-secondary);
    z-index: 2;
}

.filter-value {
    width: 40px;
    text-align: right;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 3px;
    padding: 2px 4px;
    color: var(--builder-text-secondary);
}

.divider {
    height: 1px;
    background: var(--builder-border);
    margin: 8px 0;
}

/* Global Colors */
.global-colors-section {
    padding: 12px 16px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.section-title {
    font-size: 12px;
    font-weight: 600;
    color: var(--builder-text-primary);
}

.view-toggles {
    display: flex;
    background: var(--builder-bg-primary);
    border-radius: 4px;
    border: 1px solid var(--builder-border);
    padding: 2px;
}

.view-toggles button {
    background: none;
    border: none;
    color: var(--builder-text-secondary);
    padding: 4px;
    cursor: pointer;
    border-radius: 2px;
}

.view-toggles button.active {
    background: var(--builder-accent);
    color: white;
}

/* Grid View */
.global-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.color-sq {
    width: 28px;
    height: 28px;
    border-radius: 2px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.global-corner {
    position: absolute;
    bottom: 0; right: 0;
    width: 0; height: 0;
    border-style: solid;
    border-width: 0 0 10px 10px;
    border-color: transparent transparent white transparent;
}

.add-global-btn {
    width: 28px;
    height: 28px;
    border: 1px solid var(--builder-border);
    background: var(--builder-bg-primary);
    color: var(--builder-text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 2px;
    cursor: pointer;
}

.add-global-btn:hover {
    color: white; /* In light mode this might need check, but for add btn hover, usually white on dark bg or inverse. Use text-primary for safety? Or accent? Let's use hover bg color */
    background: var(--builder-border);
    color: var(--builder-text-primary);
}

/* List View */
.global-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.global-list-item {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
}

.global-list-item:hover {
    background: var(--builder-bg-primary);
}

.color-preview-sm {
    width: 18px;
    height: 18px;
    border-radius: 2px;
    position: relative;
    overflow: hidden;
}

.global-corner-sm {
    position: absolute;
    bottom: 0; right: 0;
    width: 0; height: 0;
    border-style: solid;
    border-width: 0 0 6px 6px;
    border-color: transparent transparent white transparent;
}

.color-name {
    flex: 1;
    font-size: 12px;
    color: var(--builder-text-primary);
}

.color-hex {
    font-size: 11px;
    color: var(--builder-text-secondary);
}

.add-item {
    color: var(--builder-text-secondary);
}

.add-icon-sm {
    width: 18px; height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Bottom Actions */
.bottom-actions {
    padding: 12px 16px;
    border-top: 1px solid var(--builder-border);
}

.manage-link {
    background: none;
    border: none;
    color: var(--builder-accent);
    font-size: 12px;
    padding: 0;
    cursor: pointer;
}

.manage-link:hover {
    text-decoration: underline;
}

</style>

<style scoped>
/* Input Group Row */
.input-group-row {
    padding: 16px;
    display: flex;
    gap: 0;
}

.input-main {
    flex: 1; /* Takes remaining space */
    background: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-right: none;
    border-radius: 4px 0 0 4px;
    padding: 6px 12px;
    display: flex;
    align-items: center;
}

.text-input {
    background: transparent;
    border: none;
    color: var(--builder-text-primary);
    width: 100%;
    font-family: inherit;
    font-size: 13px;
    outline: none;
}

.mode-toggle {
    position: relative;
    width: 70px; /* Increased slightly */
    background: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-right: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mode-btn {
    width: 100%;
    height: 100%;
    background: transparent;
    border: none;
    color: var(--builder-accent);
    font-size: 12px;
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
    width: 100%;
    background: var(--builder-bg-background);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    margin-top: 4px;
    z-index: 10;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.mode-item {
    padding: 6px 8px;
    font-size: 12px;
    color: var(--builder-text-secondary);
    cursor: pointer;
    text-align: center;
}

.mode-item:hover, .mode-item.active {
    background: var(--builder-bg-primary);
    color: var(--builder-text-primary);
}

/* Hide native spin buttons */
.opacity-num-input::-webkit-outer-spin-button,
.opacity-num-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  appearance: none;
  margin: 0;
}
.opacity-num-input {
  -moz-appearance: textfield;
  appearance: textfield;
}

.opacity-input-wrapper {
    width: 85px; /* Increased from 60px to give room for spinners */
    background: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-left: 1px solid var(--builder-border); /* Fix separate border color */
    border-radius: 0 4px 4px 0;
    padding: 2px 8px; /* Reduce vertical padding to fit spinners if needed */
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.opacity-num-input {
    background: transparent;
    border: none;
    color: var(--builder-text-secondary);
    width: 100%;
    text-align: right;
    outline: none;
}

.unit {
    color: var(--builder-text-secondary);
    font-size: 12px;
    margin-left: 2px;
}

/* Suggestions Dropdown */
.css-var-input-wrapper {
  position: relative;
  width: 100%;
}

.var-suggestions-dropdown {
  position: absolute;
  top: 100%; 
  left: 0; 
  min-width: 240px; /* Ensure wide enough */
  width: max-content;
  max-width: 280px;
  background-color: var(--builder-bg-background, #1f2937); /* Fallback to dark */
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 100; /* Higher z-index */
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5); /* Stronger shadow */
  margin-top: 4px;
}

.var-suggestion-item {
  padding: 8px 12px;
  font-size: 13px;
  cursor: pointer;
  color: var(--builder-text-secondary);
  white-space: nowrap; /* Prevent wrapping */
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

/* Draggable Header */
.picker-header {
    cursor: grab;
    user-select: none;
}
.picker-header:active {
    cursor: grabbing;
}

/* Spinners */
.opacity-spinners {
    display: flex;
    flex-direction: column;
    margin-left: 4px;
    height: 24px;
}

.spinner-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    color: var(--builder-text-secondary);
    cursor: pointer;
    padding: 0;
    line-height: 0;
}

.spinner-btn:hover {
    color: var(--builder-text-primary);
    background: var(--builder-bg-primary);
}

/* Filters */
.accordion-section {
    padding: 0 16px;
}

.accordion-header {
    width: 100%;
    background: none;
    border: none;
    color: var(--builder-accent);
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
    cursor: pointer;
    padding: 8px 0;
}

/* Layout Adjustments */
.tabs-header {
    display: flex;
    padding: 8px 16px;
    background: transparent;
    border-bottom: 1px solid var(--builder-border);
    gap: 12px;
}

.tab-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 0;
    background: none;
    border: none;
    color: var(--builder-text-secondary);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    position: relative;
    transition: color 0.2s;
}

.tab-btn:hover {
    color: var(--builder-text-primary);
}

.tab-btn.active {
    color: var(--builder-accent);
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--builder-accent);
}

.tab-content {
    min-height: 200px;
}

.presets-content-main {
    padding: 12px 0;
}

.alpha-slider-row {
    padding: 0 16px 12px 16px;
}

/* Override for single slider layout */
.sliders-col {
    justify-content: center;
}

.current-color-preview {
    width: 24px;
    height: 24px;
    border-radius: 3px; 
}

.picker-controls-row {
    align-items: center;
    padding-bottom: 12px;
}

/* Material Presets Premium Styling */
.presets-content {
    padding-bottom: 12px;
}

.family-selector {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 8px 16px 16px 16px;
    margin-bottom: 4px;
}

.family-swatch {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    cursor: pointer;
    flex-shrink: 0;
    border: 2px solid transparent;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.family-swatch:hover {
    transform: scale(1.1);
}

.family-swatch.active {
    border-color: var(--builder-accent);
    transform: scale(1.2);
    box-shadow: 0 0 0 2px var(--builder-bg-primary), 0 0 0 4px var(--builder-accent);
}

.variants-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
    margin-bottom: 16px;
    padding: 0 16px;
}

.variant-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    transition: transform 0.1s;
}

.variant-item:hover {
    transform: translateY(-2px);
}

.variant-preview {
    width: 100%;
    aspect-ratio: 1;
    border-radius: 4px;
    border: 1px solid rgba(0,0,0,0.05);
}

.variant-label {
    font-size: 9px;
    color: var(--builder-text-muted);
    font-weight: 500;
}

.pairings-section {
    margin-top: 12px;
    margin-left: 16px;
    margin-right: 16px;
    padding: 10px;
    background: transparent;
    border-radius: 8px;
    border: 1px solid var(--builder-border);
}

.pairing-title {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: var(--builder-text-secondary);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pairings-list {
    display: flex;
    gap: 8px;
}

.pairing-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.2s;
}

.pairing-item:hover {
    border-color: var(--builder-accent);
    background: var(--builder-bg-secondary);
}

.pairing-preview {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.pairing-label {
    font-size: 11px;
    color: var(--builder-text-primary);
    white-space: nowrap;
}
</style>

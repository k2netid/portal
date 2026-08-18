<template>
  <div class="global-variables-panel">
    <div class="variables-content">
      <BaseCollapsible 
        v-for="category in categories" 
        :key="category.id" 
        :title="t(`builder.panels.globalVariables.${category.id}`)"
        class="variable-category"
        icon-position="right"
        :model-value="activeCategoryId === category.id"
        @update:model-value="(val) => toggleCategory(category.id)"
      >
<!-- Numbers Specific Layout -->
           <div v-if="category.id === 'numbers'" class="numbers-list">
               <div class="list-header">
                   <div class="col-name" style="padding-left: 20px;">{{ t('builder.panels.globalVariables.table.name') }}</div>
                   <div class="col-value">{{ t('builder.panels.globalVariables.table.value') }}</div>
                   <div style="width: 24px;"></div>
               </div>
               
               <draggable 
                   v-model="globalNumbers" 
                   item-key="id"
                   handle=".drag-handle"
                   ghost-class="ja-builder-ghost"
                   animation="200"
               >
                   <template #item="{ element: number, index }">
                       <div class="variable-row top-align-row">
                           <div class="drag-handle">
                               <GripVertical :size="14" class="text-muted" />
                           </div>
                           
                           <BaseInput 
                               v-model="number.name" 
                               class="name-input"
                               :placeholder="t('builder.panels.globalVariables.placeholders.name')"
                           />
                           
                           <div class="number-control-group">
                               <div class="value-input-group">
                                     <input 
                                         v-model="number.value" 
                                         type="number"
                                         class="value-input flex-grow"
                                         :placeholder="t('builder.panels.globalVariables.placeholders.none')"
                                     />
                                    
                                    <!-- Unit Selector (Native) -->
                                    <select v-model="number.unit" class="unit-select-native flex-shrink-0">
                                        <option 
                                            v-for="unit in ['px', '%', 'em', 'rem', 'vw', 'vh', 'vmin', 'vmax', 'deg', 'grad', 'ms', 'rad', 's', 'turn', 'calc', 'min', 'max', 'clamp', 'auto', 'none', 'css var']" 
                                            :key="unit" 
                                            :value="unit"
                                        >
                                            {{ unit }}
                                        </option>
                                    </select>
                               </div>
                               
                               <!-- Scrubber/Slider -->
                               <div class="scrubber-container">
                                   <BaseSlider 
                                       v-model="number.value"
                                       :min="0"
                                       :max="1000"
                                       class="opacity-slider"
                                   />
                               </div>
                           </div>

                           <IconButton 
                               :icon="Trash2" 
                               variant="ghost" 
                               size="sm" 
                               @click="handleDeleteVariable(index, 'numbers')" 
                               class="delete-btn"
                           />
                       </div>
                   </template>
               </draggable>

               <BaseButton variant="ghost" size="sm" class="add-variable-btn" @click="addNumberVariable">
                   <Plus :size="12" />
                   {{ t('builder.panels.globalVariables.actions.addNumber') }}
               </BaseButton>
           </div>
           
           <!-- Text Specific Layout -->
           <div v-else-if="category.id === 'text'" class="text-list">
               <div class="list-header">
                   <div class="col-name" style="padding-left: 20px;">{{ t('builder.panels.globalVariables.table.name') }}</div>
                   <div class="col-value">{{ t('builder.panels.globalVariables.table.string') }}</div>
                   <div style="width: 24px;"></div>
               </div>
               
               <draggable 
                   v-model="globalText" 
                   item-key="id"
                   handle=".drag-handle"
                   ghost-class="ja-builder-ghost"
                   animation="200"
               >
                   <template #item="{ element: item, index }">
                       <div class="variable-row top-align-row">
                           <div class="drag-handle">
                               <GripVertical :size="14" class="text-muted" />
                           </div>
                           
                           <BaseInput 
                               v-model="item.name" 
                               class="name-input"
                           />
                           
                           <BaseInput
                               type="textarea"
                               v-model="item.value"
                               :rows="2"
                               class="text-area"
                           />

                           <IconButton 
                               :icon="Trash2" 
                               variant="ghost" 
                               size="sm" 
                               @click="handleDeleteVariable(index, 'text')" 
                               class="delete-btn"
                           />
                       </div>
                   </template>
               </draggable>

               <BaseButton variant="ghost" size="sm" class="add-variable-btn" @click="addTextVariable">
                   <Plus :size="12" />
                   {{ t('builder.panels.globalVariables.actions.addString') }}
               </BaseButton>
           </div>

           <!-- Images Specific Layout -->
           <div v-else-if="category.id === 'images'" class="images-list">
               <div class="list-header">
                   <div class="col-name" style="padding-left: 20px;">{{ t('builder.panels.globalVariables.table.name') }}</div>
                   <div class="col-value">{{ t('builder.panels.globalVariables.table.image') }}</div>
                   <div style="width: 24px;"></div>
               </div>
               
               <draggable 
                   v-model="globalImages" 
                   item-key="id"
                   handle=".drag-handle"
                   ghost-class="ja-builder-ghost"
                   animation="200"
               >
                   <template #item="{ element: item, index }">
                       <div class="variable-row top-align-row">
                           <div class="drag-handle">
                               <GripVertical :size="14" class="text-muted" />
                           </div>
                           
                           <BaseInput 
                               v-model="item.name" 
                               class="name-input"
                           />
                           
                           <div class="image-control-group">
                               <div class="image-preview-box">
                                   <img v-if="item.value" :src="item.value" class="preview-img" />
                                   <ImageIcon v-else :size="16" class="text-muted" />
                               </div>
                               <MediaPicker 
                                   :label="t('builder.panels.globalVariables.actions.selectImage')"
                                   @selected="(media) => handleMediaSelect(index, media)"
                               >
                                   <template #trigger="{ open }">
                                       <BaseButton variant="secondary" size="sm" @click="open" class="upload-btn">
                                           <ImageIcon :size="12" />
                                           {{ t('builder.panels.globalVariables.actions.select') }}
                                       </BaseButton>
                                   </template>
                               </MediaPicker>
                           </div>

                           <IconButton 
                               :icon="Trash2" 
                               variant="ghost" 
                               size="sm" 
                               @click="handleDeleteVariable(index, 'images')" 
                               class="delete-btn"
                           />
                       </div>
                   </template>
               </draggable>

               <BaseButton variant="ghost" size="sm" class="add-variable-btn" @click="addImageVariable">
                   <Plus :size="12" />
                   {{ t('builder.panels.globalVariables.actions.addImage') }}
               </BaseButton>
</div>

           <!-- Links Specific Layout -->
           <div v-else-if="category.id === 'links'" class="links-list">
               <div class="list-header">
                   <div class="col-name" style="padding-left: 20px;">{{ t('builder.panels.globalVariables.table.name') }}</div>
                   <div class="col-value">{{ t('builder.panels.globalVariables.table.url') }}</div>
                   <div style="width: 24px;"></div>
               </div>
               
               <draggable 
                   v-model="globalLinks" 
                   item-key="id"
                   handle=".drag-handle"
                   ghost-class="ja-builder-ghost"
                   animation="200"
               >
                   <template #item="{ element: item, index }">
                       <div class="variable-row top-align-row">
                           <div class="drag-handle">
                               <GripVertical :size="14" class="text-muted" />
                           </div>
                           
                           <BaseInput 
                               v-model="item.name" 
                               class="name-input"
                           />
                           
                           <BaseInput 
                               v-model="item.value" 
                               class="url-input"
                           />

                           <IconButton 
                               :icon="Trash2" 
                               variant="ghost" 
                               size="sm" 
                               @click="handleDeleteVariable(index, 'links')" 
                               class="delete-btn"
                           />
                       </div>
                   </template>
               </draggable>

               <BaseButton variant="ghost" size="sm" class="add-variable-btn" @click="addLinkVariable">
                   <Plus :size="12" />
                   {{ t('builder.panels.globalVariables.actions.addLink') }}
               </BaseButton>
           </div>
           
           <!-- Colors Specific Layout -->
           <div v-else-if="category.id === 'colors'" class="colors-list">
               <div class="list-header">
                   <div class="col-name">{{ t('builder.panels.globalVariables.table.name') }}</div>
                   <div class="col-value">{{ t('builder.panels.globalVariables.table.color') }}</div>
               </div>
               
               <draggable 
                   v-model="globalColors" 
                   item-key="id"
                   handle=".drag-handle"
                   ghost-class="ja-builder-ghost"
                   animation="200"
               >
                   <template #item="{ element: color, index }">
                       <div class="variable-row color-row">
                           <div class="drag-handle">
                               <GripVertical :size="14" class="text-muted" />
                           </div>
                           <BaseInput 
                               v-model="color.name" 
                               class="name-input"
                           />
                           
                           <div class="color-control-group">
                                <div class="color-input-group">
                                    <div 
                                        class="color-swatch" 
                                        :style="{ backgroundColor: color.value }"
                                        @click="openColorPicker(index)"
                                    ></div>
                                    <span class="color-hash">#</span>
                                    <input 
                                        v-model="color.hex" 
                                        class="hex-input"
                                        @input="(e) => handleHexInput(index, e)"
                                    />
                                    <div class="opacity-separator"></div>
                                    <input 
                                        type="number" 
                                        v-model="color.opacity" 
                                        class="opacity-input"
                                        :min="0"
                                        :max="100"
                                    />
                                    <span class="opacity-unit">%</span>
                                </div>
                                <!-- Slider for Opacity -->
                                <div class="opacity-slider-container">
                                    <BaseColorSlider 
                                        v-model="color.opacity" 
                                        variant="alpha"
                                        :color="color.value"
                                        :min="0"
                                        :max="100"
                                    />
                                </div>
                            </div>
                                                      <IconButton 
                                :icon="Trash2" 
                                variant="ghost" 
                                size="sm" 
                                @click="handleDeleteVariable(index, 'colors')" 
                                class="delete-btn"
                            />
                       </div>
                   </template>
               </draggable>

               <BaseButton variant="ghost" size="sm" class="add-variable-btn" @click="addColorVariable">
                   <Plus :size="12" />
                   {{ t('builder.panels.globalVariables.actions.addColor') }}
               </BaseButton>
           </div>
           
           <!-- Fonts Specific Layout -->
           <div v-else-if="category.id === 'fonts'" class="fonts-list">
               <div class="list-header">
                   <div class="col-name">{{ t('builder.panels.globalVariables.table.name') }}</div>
                   <div class="col-value">{{ t('builder.panels.globalVariables.table.string') }}</div>
               </div>
               
               <draggable 
                   v-model="globalFonts" 
                   item-key="id"
                   handle=".drag-handle"
                   ghost-class="ja-builder-ghost"
                   animation="200"
               >
                   <template #item="{ element: font, index }">
                       <div class="variable-row top-align-row">
                           <div class="drag-handle">
                               <GripVertical :size="14" class="text-muted" />
                           </div>
                           <BaseInput 
                               v-model="font.name" 
                               class="name-input"
                           />
                           
                           <div class="font-select-wrapper">
                               <select v-model="font.family" class="var-input font-select">
                                   <option v-for="f in fontOptions" :key="f" :value="f">{{ f }}</option>
                               </select>
                           </div>
                           
                           <IconButton 
                               :icon="Trash2" 
                               variant="ghost" 
                               size="sm" 
                               @click="handleDeleteVariable(index, 'fonts')" 
                               class="delete-btn"
                           />
                       </div>
                   </template>
               </draggable>
               
               <BaseButton variant="ghost" size="sm" class="add-variable-btn" @click="addFontVariable">
                   <Plus :size="12" />
                   {{ t('builder.panels.globalVariables.actions.addFont') }}
               </BaseButton>
           </div>

           <!-- Default Placeholder for others -->
           <div v-else>
               <div class="variable-item-placeholder">
                   {{ t('builder.panels.globalVariables.placeholders.empty', { category: t(`builder.panels.globalVariables.${category.id}`) }) }}
               </div>
               <button class="add-variable-btn">
                   <Plus :size="12" />
                   {{ t('builder.panels.globalVariables.actions.addGeneric', { category: t(`builder.panels.globalVariables.${category.id}`).slice(0, -1) }) }}
               </button>
           </div>
        </BaseCollapsible>
    </div>
    
    <div class="panel-footer">
        <BaseButton variant="secondary" @click="cancelChanges">{{ t('builder.panels.globalVariables.actions.cancel') }}</BaseButton>
        <BaseButton variant="primary" @click="saveVariables">{{ t('builder.panels.globalVariables.actions.save') }}</BaseButton>
    </div>

    <!-- Color Picker Modal -->
    <teleport to="body">
        <ColorPickerModal 
            v-if="isColorPickerOpen" 
            :model-value="editingColorValue"
            @update:model-value="updateColorValue"
            @close="isColorPickerOpen = false"
        />
    </teleport>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, computed, nextTick, onMounted, inject, watch, defineAsyncComponent } from 'vue';
import type { Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import GripVertical from 'lucide-vue-next/dist/esm/icons/grip-vertical.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';
import draggable from 'vuedraggable';
import { 
    BaseCollapsible, BaseInput, BaseSlider, IconButton, BaseButton, BaseColorSlider
} from '@/components/builder/ui';
import MediaPicker from '@/components/media/MediaPicker.vue';
import type { BuilderInstance } from '@/types/builder';

const ColorPickerModal = defineAsyncComponent(() => import('@/components/builder/modals/ColorPickerModal.vue'));

// Using the imported GlobalVariable now from builder.ts
import type { GlobalVariable } from '@/types/builder';

const { t } = useI18n();
const builder = inject<BuilderInstance>('builder');

const globalVariables = builder?.globalVariables;
const globalAction = builder?.globalAction;
const globalNumbers = globalVariables?.globalNumbers;
const globalText = globalVariables?.globalText;
const globalImages = globalVariables?.globalImages;
const globalLinks = globalVariables?.globalLinks;
const globalColors = globalVariables?.globalColors;
const globalFonts = globalVariables?.globalFonts;
const addVariable = globalVariables?.addVariable;
const deleteVariable = globalVariables?.deleteVariable;
const loadVariables = globalVariables?.loadVariables;

const categories = [
    { id: 'numbers', label: 'Numbers' },
    { id: 'text', label: 'Text' },
    { id: 'images', label: 'Images' },
    { id: 'links', label: 'Links' },
    { id: 'colors', label: 'Colors' },
    { id: 'fonts', label: 'Fonts' }
];

const activeCategoryId = ref<string | null>('numbers');

const fontOptions = [
    'Instrument Sans',
    'Open Sans',
    'Inter',
    'Roboto',
    'Lato',
    'Montserrat',
    'Poppins'
];

const toggleCategory = (id: string) => {
    if (activeCategoryId.value === id) {
        activeCategoryId.value = null;
    } else {
        activeCategoryId.value = id;
    }
};

// Unit Dropdown Logic


// Validation helper
const hasEmptyName = (arr: GlobalVariable[] | undefined) => Array.isArray(arr) && arr.some(item => !item.name || item.name.trim() === '');

const checkAndAddVariable = (type: string, list: Ref<GlobalVariable[] | undefined> | undefined) => {
    if (list && hasEmptyName(list.value)) {
        // Determine singular name for alert
        const typeName = t(`builder.panels.globalVariables.${type}`).slice(0, -1); // simple singularization
        alert(t('builder.panels.globalVariables.messages.fillName', { type: typeName }));
        return;
    }
    if (addVariable) addVariable(type);
};

const addNumberVariable = () => checkAndAddVariable('numbers', globalNumbers);
const addTextVariable = () => checkAndAddVariable('text', globalText);
const addImageVariable = () => checkAndAddVariable('images', globalImages);
const addLinkVariable = () => checkAndAddVariable('links', globalLinks);
const addColorVariable = () => checkAndAddVariable('colors', globalColors);
const addFontVariable = () => checkAndAddVariable('fonts', globalFonts);

// Media Picker Logic
const handleMediaSelect = (index: number, media: { url?: string }) => {
    if (media && media.url && globalImages?.value) {
        globalImages.value[index].value = media.url;
    }
};

// Color Picker Logic
const isColorPickerOpen = ref(false);
const editingColorIndex = ref<number | null>(null);

const editingColorValue = computed(() => {
    if (editingColorIndex.value !== null && globalColors?.value && globalColors.value[editingColorIndex.value]) {
        return globalColors.value[editingColorIndex.value].value as string;
    }
    return '';
});

const openColorPicker = (index: number) => {
    editingColorIndex.value = index;
    isColorPickerOpen.value = true;
};

const updateColorValue = (newValue: string) => {
    if (editingColorIndex.value !== null && globalColors?.value) {
        const color = globalColors.value[editingColorIndex.value];
        color.value = newValue;
        if (newValue.startsWith('#')) {
            color.hex = newValue.replace('#', '');
        }
    }
};

const handleDeleteVariable = (index: number, type: string) => {
    if (deleteVariable) {
        deleteVariable(index, type);
    }
};

const handleHexInput = (index: number, event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target) {
        updateColorFromHex(index, target.value);
    }
};

const updateColorFromHex = (index: number, hex: string) => {
    if (globalColors?.value) {
        const color = globalColors.value[index];
        if (!hex.startsWith('#')) hex = '#' + hex;
        color.value = hex;
    }
};

// Snapshots for Cancel
const originalState = ref<Record<string, unknown>>({});

const getSnapshot = () => {
    return JSON.parse(JSON.stringify({
        globalNumbers: globalNumbers?.value,
        globalText: globalText?.value,
        globalImages: globalImages?.value,
        globalLinks: globalLinks?.value,
        globalColors: globalColors?.value,
        globalFonts: globalFonts?.value
    }));
};

onMounted(() => {
    originalState.value = getSnapshot();
    
    // Check for pending action
    if (builder) {
        builder.activePanel.value = 'global_variables';
        builder.globalAction.value = { type: 'add_color', payload: { timestamp: Date.now() } };
    }
});

// Watch for global actions
if (globalAction) {
    watch(() => globalAction.value, (action) => {
        if (action) handleGlobalAction(action);
    });
}

const handleGlobalAction = (action: { type: string; payload?: unknown }) => {
    if (action.type === 'add_color') {
        activeCategoryId.value = 'colors';
        nextTick(() => {
            addColorVariable();
            // We use our local wrapper logic to ensure validation
        });
    }
};

const validateInputs = () => {
    const errors: string[] = [];
    
    // Validate Names
    const allVars = [
        ...(globalNumbers?.value || []).map((i: GlobalVariable) => ({...i, type: 'Number'})),
        ...(globalText?.value || []).map((i: GlobalVariable) => ({...i, type: 'Text'})),
        ...(globalImages?.value || []).map((i: GlobalVariable) => ({...i, type: 'Image'})),
        ...(globalLinks?.value || []).map((i: GlobalVariable) => ({...i, type: 'Link'})),
        ...(globalColors?.value || []).map((i: GlobalVariable) => ({...i, type: 'Color'})),
        ...(globalFonts?.value || []).map((i: GlobalVariable) => ({...i, type: 'Font'}))
    ];
    
    allVars.forEach((v) => {
        if (!v.name || v.name.trim() === '') {
            errors.push(t('builder.panels.globalVariables.messages.fillName', { type: v.type }));
        }
    });

    // Validate Colors
    const hexRegex = /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/;
    (globalColors?.value || []).forEach((c) => {
        if (typeof c.value === 'string' && !hexRegex.test(c.value)) {
            errors.push(t('builder.panels.globalVariables.messages.invalidHex', { value: c.value, name: c.name }));
        }
    });

    if (errors.length > 0) {
        alert(errors[0]);
        return false;
    }
    return true;
};

const cancelChanges = async () => {
    const confirmed = await builder?.confirm?.({
        title: t('builder.modals.confirm.discardChanges'),
        message: t('builder.modals.confirm.discardChangesDesc'),
        confirmText: t('builder.modals.confirm.discard'),
        cancelText: t('builder.modals.confirm.cancel'),
        type: 'warning'
    });
    if (confirmed) {
        // Use loadVariables to restore state
        if (loadVariables) loadVariables(originalState.value as Record<string, GlobalVariable[]>);
    }
};

const saveVariables = async () => {
    if (!validateInputs()) return;
    originalState.value = getSnapshot(); // Update snapshot
    
    try {
        if (builder?.saveGlobalVariables) await builder.saveGlobalVariables();
        alert(t('builder.panels.globalVariables.messages.saveSuccess'));
    } catch (error) {
        logger.error('Failed to save variables:', error);
        alert(t('builder.panels.globalVariables.messages.saveError'));
    }
};
</script>

<style scoped>
/* Standardize file input */
.hidden-file-input {
    display: none;
}

/* Spin Buttons: Show on Hover */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  opacity: 0;
  transition: opacity 0.2s;
  cursor: pointer;
}

input[type=number]:hover::-webkit-inner-spin-button, 
input[type=number]:hover::-webkit-outer-spin-button { 
  opacity: 1;
}

input[type=number] {
  -moz-appearance: textfield;
  appearance: textfield;
}

input[type=number]:hover {
    -moz-appearance: auto;
    appearance: auto;
}

/* ... existing styles ... */

.color-input-group {
    display: flex;
    align-items: center;
    background-color: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    padding: 4px;
    height: 32px;
    box-sizing: border-box;
    width: 100%;
}

.hex-input {
    border: none;
    background: transparent;
    padding: 0 4px;
    flex: 1; /* Reduce flex share */
    min-width: 0;
    font-family: monospace;
    height: 22px;
}

.opacity-input-wrapper {
    display: flex;
    align-items: center;
    position: relative;
}

.opacity-input {
    border: none;
    background: transparent;
    width: 40px; 
    padding: 0 4px;
    text-align: right;
    height: 22px;
    flex: 0 0 auto;
    /* Hide native spinners completely */
    -webkit-appearance: none;
    -moz-appearance: textfield;
    appearance: textfield;
}

.opacity-input::-webkit-inner-spin-button,
.opacity-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.spinner-buttons {
    display: flex;
    flex-direction: column;
    margin-left: 2px;
}

.spinner-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 14px;
    height: 11px;
    background: var(--builder-bg-tertiary);
    border: none;
    color: var(--builder-text-muted);
    cursor: pointer;
    padding: 0;
}

.spinner-btn:first-child {
    border-radius: 2px 2px 0 0;
}

.spinner-btn:last-child {
    border-radius: 0 0 2px 2px;
}

.spinner-btn:hover {
    background: var(--builder-bg-tertiary);
    color: var(--builder-text-primary);
}

.opacity-unit {
    color: var(--builder-text-muted);
    font-size: 11px;
    margin-left: 4px;
    padding-right: 4px;
}
/* ... */
.variables-content {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}

.variable-category {
    border-bottom: 1px solid var(--builder-border);
}

.category-header {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 12px 16px;
    background: none;
    border: none;
    color: var(--builder-text-primary);
    cursor: pointer;
    transition: background-color 0.15s;
    text-align: left;
}

.category-header:hover {
    background-color: var(--builder-bg-secondary);
}

.category-arrow {
    margin-right: 8px;
    color: var(--builder-text-muted);
    transition: transform 0.2s;
}

.category-arrow.is-open {
    transform: rotate(90deg);
}

.category-title {
    font-size: 13px;
    font-weight: 500;
}

.category-content {
    padding: 8px 16px 16px 16px;
    background-color: var(--builder-bg-primary);
}

/* Colors List Styling */
.colors-list, .fonts-list, .numbers-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.list-header {
    display: flex;
    margin-bottom: 4px;
}

.col-name, .col-value {
    font-size: 11px;
    color: var(--builder-text-muted);
    font-weight: 500;
}

.col-name {
    flex: 1;
}

.col-value {
    flex: 1.2;
}

.variable-row {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--builder-border);
}

.variable-row:last-of-type {
    margin-bottom: 0;
    border-bottom: none;
}

.top-align-row {
    align-items: flex-start;
}

/* Color Row Specifics */
.color-row {
    align-items: flex-start; /* Align top because control group has slider below */
}

.var-input {
    background-color: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    color: var(--builder-text-primary);
    border-radius: 4px;
    font-size: 12px;
    padding: 6px 8px;
    outline: none;
}

.var-input:focus {
    border-color: var(--builder-accent);
}

.name-input {
    flex: 1;
    height: 32px; /* Fixed height for alignment */
}

.color-control-group {
    flex: 1.2;
    display: flex;
    flex-direction: column;
}

.color-input-group {
    display: flex;
    align-items: center;
    background-color: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-radius: 6px;
    padding: 2px 8px;
    height: 34px;
    box-sizing: border-box;
    transition: all 0.2s;
}

.color-input-group:focus-within {
    border-color: var(--builder-accent);
    box-shadow: 0 0 0 1px var(--builder-accent-light);
}

.color-swatch {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    margin-right: 8px;
    flex-shrink: 0;
    cursor: pointer;
    border: 1px solid rgba(0,0,0,0.1);
}

.color-hash {
    color: var(--builder-text-muted);
    font-size: 12px;
    font-weight: 500;
}

.hex-input {
    border: none;
    background: transparent;
    padding: 0 4px;
    width: 60px;
    font-family: inherit;
    font-size: 13px;
    color: var(--builder-text-primary);
    height: 100%;
    outline: none;
}

.opacity-separator {
    width: 1px;
    height: 16px;
    background-color: var(--builder-border);
    margin: 0 8px;
}

.opacity-input {
    border: none;
    background: transparent;
    padding: 0;
    width: 32px;
    text-align: right;
    height: 100%;
    font-size: 13px;
    color: var(--builder-text-primary);
    outline: none;
    -webkit-appearance: none;
    -moz-appearance: textfield;
    appearance: none;
}

.opacity-input::-webkit-inner-spin-button,
.opacity-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    appearance: none;
    margin: 0;
}

.opacity-unit {
    color: var(--builder-text-muted);
    font-size: 12px;
    margin-left: 2px;
    font-weight: 500;
}

/* Opacity Slider Scrubber */
.opacity-slider-container {
    margin-top: 6px;
    padding: 0 2px;
}

/* Fonts Select */
.font-select-wrapper {
    flex: 1.2;
}

.font-select {
    width: 100%;
    cursor: pointer;
    height: 32px;
}

/* Numbers Layout */
.drag-handle {
    width: 14px;
    margin-top: 8px;
    cursor: grab;
    display: flex;
    justify-content: center;
    flex-shrink: 0;
}

.drag-handle:active {
    cursor: grabbing;
}

/* Drag Ghost Styling */
.drag-ghost {
    opacity: 0.5;
    background: var(--builder-bg-tertiary);
    border: 2px dashed #60a5fa;
    border-radius: 6px;
}

.text-muted {
    color: var(--builder-text-muted);
}

.number-control-group {
    flex: 1.2;
    display: flex;
    flex-direction: column;
}

.value-input-group {
    display: flex;
    align-items: center;
    background-color: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-radius: 6px;
    height: 34px;
    box-sizing: border-box;
    overflow: hidden;
    transition: all 0.2s;
}

.value-input-group:focus-within {
    border-color: var(--builder-accent);
    box-shadow: 0 0 0 1px var(--builder-accent-light);
}

.value-input {
    flex: 1;
    min-width: 0;
    height: 32px;
    padding: 0 12px;
    font-size: 13px;
    color: var(--builder-text-primary);
    background: transparent;
    border: none;
    outline: none;
}

.value-input::placeholder {
    color: var(--builder-text-muted);
    opacity: 0.6;
}

/* Native Unit Select Styles */
.unit-select-native {
    height: 100%;
    min-width: 60px;
    padding: 0 8px;
    background: var(--builder-bg-tertiary);
    border: none;
    border-left: 1px solid var(--builder-border);
    border-radius: 0 5px 5px 0;
    color: var(--builder-text-primary);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 6px center;
    padding-right: 24px;
}

.unit-select-native:hover {
    background: var(--builder-bg-secondary);
}

.unit-select-native:focus {
    border-color: var(--builder-accent);
}

.unit-select-native option {
    background: var(--builder-bg-primary);
    color: var(--builder-text-primary);
    padding: 8px;
}

.delete-btn {
    width: 24px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    color: var(--builder-text-muted);
    cursor: pointer;
}

.delete-btn:hover {
    color: #ef4444; /* red-500 */
}

.scrubber-container {
    margin-top: 8px;
}

.variable-item-placeholder {
    font-size: 12px;
    color: var(--builder-text-muted);
    margin-bottom: 8px;
    font-style: italic;
    padding-left: 22px;
}

.add-variable-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    background: none;
    border: none;
    color: var(--builder-accent);
    font-size: 12px;
    cursor: pointer;
    padding: 4px 0;
    margin-top: 4px;
}

.add-variable-btn:hover {
    text-decoration: underline;
}

/* Text specific */
.text-area {
    flex: 1.2;
    background: transparent;
    min-height: 50px;
    resize: vertical;
    font-family: inherit;
    line-height: normal;
}

/* Image specific */
.image-control-group {
    flex: 1.2;
    display: flex;
    gap: 8px;
    align-items: center;
}

.image-preview-box {
    width: 32px;
    height: 32px;
    background-color: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-width: 80px;
    padding: 0 12px;
    height: 32px;
    background-color: #2563eb;
    border: none;
    border-radius: 4px;
    color: #ffffff;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
    white-space: nowrap;
}

.upload-btn:hover {
    background-color: #1d4ed8;
}

/* Link specific */
.url-input {
    flex: 1.2;
    height: 32px;
}

/* Footer */
.panel-footer {
    display: flex;
    gap: 12px;
    padding: 16px;
    border-top: 1px solid var(--builder-border);
    margin-top: auto;
}
</style>

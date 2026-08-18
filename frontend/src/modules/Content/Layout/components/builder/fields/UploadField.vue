<template>
  <div class="upload-field">
    <!-- Image/Video Preview -->
    <div v-if="!hidePreview && (hasValue || placeholderValue) && isValidMedia" class="preview-container" :class="{ 'is-inherited': !hasValue && placeholderValue }">
      <video v-if="isVideo" :src="resolvedPreviewValue" class="preview-image" :style="previewStyle" muted playsinline></video>
      <div v-else-if="isImage" class="preview-image" :style="{ backgroundImage: `url(${resolvedPreviewValue})`, ...previewStyle }"></div>
      <div v-else class="preview-file-icon">
        <File :size="32" />
      </div>
      
      <div v-if="!hasValue && placeholderValue" class="inherited-badge">{{ $t('builder.fields.inherited') }}</div>
      <button v-if="hasValue" class="remove-btn" @click="clearValue" :title="$t('builder.fields.actions.remove')">
        <X :size="14" />
      </button>
    </div>

    <!-- Controls Row -->
    <div class="controls-row">
        <!-- Input Mode Toggle -->
        <div class="mode-toggle" ref="modeDropdownRef">
            <button class="mode-btn" @click.stop="toggleModeDropdown" :title="inputMode === 'manual' ? $t('builder.fields.upload.mode.manual') : $t('builder.fields.upload.mode.var')">
                <LinkIcon v-if="inputMode === 'manual'" :size="14" />
                <Variable v-else :size="14" />
            </button>
            
            <div 
              v-if="showModeDropdown" 
              class="mode-dropdown-menu"
            >
                <div 
                    class="mode-item" 
                    :class="{ active: inputMode === 'manual' }"
                    @click="setMode('manual')"
                >
                    <LinkIcon :size="12" class="mr-2" />
                    {{ $t('builder.fields.upload.mode.manual') }}
                </div>
                <div 
                    class="mode-item" 
                    :class="{ active: inputMode === 'var' }"
                    @click="setMode('var')"
                >
                    <Variable :size="12" class="mr-2" />
                     {{ $t('builder.fields.upload.mode.var') }}
                </div>
            </div>
        </div>

        <!-- Inputs -->
        <div class="input-wrapper flex-1">
             <!-- Manual Mode: Input + Media Picker -->
            <div v-if="inputMode === 'manual'" class="flex gap-2 w-full">
                <BaseInput 
                    :model-value="manualValue" 
                    @update:model-value="handleManualInput"
                    :placeholder="placeholderValue || 'https://'"
                    class="flex-1"
                />
                
                <MediaPicker @selected="handleSelection" :constraints="{ allowedExtensions, maxSize }">
                    <template #trigger="{ open }">
                    <IconButton 
                        :icon="Upload" 
                        @click="open" 
                        :title="$t('builder.fields.actions.select')"
                        variant="secondary"
                    />
                    </template>
                </MediaPicker>
            </div>

            <!-- Variable Mode: Suggestion Input -->
            <div v-else class="relative w-full" ref="varInputRef">
                 <input 
                    type="text" 
                    v-model="varValue"
                    :placeholder="$t('builder.fields.color.placeholder')"
                    class="var-input"
                    @focus="showVarSuggestions = true"
                    @blur="handleVarBlur"
                    @input="handleVarInput"
                />
                
                <!-- Suggestions Dropdown -->
                <div 
                  v-if="showVarSuggestions" 
                  class="var-suggestions-dropdown"
                >
                    <div 
                      v-for="suggestion in filteredSuggestions" 
                      :key="suggestion.value" 
                      class="var-suggestion-item"
                      @mousedown="selectVar(suggestion.value)"
                    >
                        <div class="suggestion-preview">
                             <img v-if="suggestion.url" :src="suggestion.url" class="suggestion-thumb" />
                             <div v-else class="suggestion-thumb-placeholder"></div>
                        </div>
                        <span class="suggestion-text">{{ suggestion.label }}</span>
                    </div>
                    <div v-if="filteredSuggestions.length === 0" class="var-suggestion-empty">
                        {{ $t('builder.fields.color.noMatches') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script setup lang="ts">
import { ref, computed, watch, inject, onMounted, onUnmounted } from 'vue'
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import Upload from 'lucide-vue-next/dist/esm/icons/upload.js';
import File from 'lucide-vue-next/dist/esm/icons/file.js';

import LinkIcon from 'lucide-vue-next/dist/esm/icons/link.js';
import Variable from 'lucide-vue-next/dist/esm/icons/variable.js';
import MediaPicker from '@/components/media/MediaPicker.vue'
import { BaseInput, IconButton } from '@/components/builder/ui'
import { useToast } from '@/composables/useToast'
import type { BuilderInstance, SettingDefinition, GlobalVariable, BlockInstance } from '@/types/builder'
import { toCssVarName } from '../core/cssVariables'


const props = withDefaults(defineProps<{
  field: SettingDefinition;
  value?: string;
  placeholderValue?: string;
  hidePreview?: boolean;
  allowedExtensions?: string[];
  maxSize?: number | null;
  module?: BlockInstance;
  previewStyle?: Record<string, string | number>;
}>(), {
  value: '',
  placeholderValue: '',
  hidePreview: false,
  allowedExtensions: () => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
  maxSize: null,
  module: undefined,
  previewStyle: () => ({})
})

const emit = defineEmits(['update:value'])
const toast = useToast()

const builder = inject<BuilderInstance>('builder')
const globalImages = builder?.globalVariables?.globalImages || ref<GlobalVariable[]>([])

// State
const inputMode = ref('manual') // 'manual' | 'var'
const showModeDropdown = ref(false)
const modeDropdownRef = ref(null)
const manualValue = ref('')
const varValue = ref('')
const showVarSuggestions = ref(false)
const varInputRef = ref(null)

// Computed
const hasValue = computed(() => {
    return props.value && props.value !== ''
})

const resolvedPreviewValue = computed(() => {
    const val = props.value || props.placeholderValue
    if (!val) return ''
    
    if (val.startsWith('var(')) {
        // Resolve variable
        const varName = val.replace(/^var\(\s*(.+?)\s*\)$/, '$1')
        const found = globalImages.value.find((g: GlobalVariable) => toCssVarName(g.name) === varName)
        return found ? (found.value as string) : '' // Return matched url or empty if not found
    }
    
    return val
})

const isImage = computed(() => {
  const val = resolvedPreviewValue.value
  if (!val) return false
  const parts = val.split('.')
  if (parts.length < 2) return val.startsWith('data:image')
  const ext = parts.pop()?.toLowerCase().split('?')[0] || '' // Added fallback for pop()
  
  const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']
  const isImg = imageExts.includes(ext) || val.startsWith('data:image')
  
  if (props.allowedExtensions && props.allowedExtensions.length > 0) {
    return isImg && props.allowedExtensions.includes(ext)
  }
  return isImg
})

const isVideo = computed(() => {
  const val = resolvedPreviewValue.value
  if (!val) return false
  const parts = val.split('.')
  if (parts.length < 2) return val.startsWith('data:video')
  const ext = parts.pop()?.toLowerCase().split('?')[0] || '' // Added fallback for pop()
  
  const videoExts = ['mp4', 'webm', 'ogv', 'mov', 'm4v']
  const isVid = videoExts.includes(ext) || val.startsWith('data:video')
  
  if (props.allowedExtensions && props.allowedExtensions.length > 0) {
    return isVid && props.allowedExtensions.includes(ext)
  }
  return isVid
})

const isValidMedia = computed(() => {
  const val = resolvedPreviewValue.value
  if (!val) return true 
  
  const parts = val.split('.')
  if (parts.length < 2) return true 
  
  const ext = parts.pop()?.toLowerCase().split('?')[0] || '' // Added fallback for pop()
  if (props.allowedExtensions && props.allowedExtensions.length > 0) {
    return props.allowedExtensions.includes(ext)
  }
  return true
})

// Suggestions
const allSuggestions = computed(() => {
    const images = globalImages.value || []
    return images.map((img: GlobalVariable) => ({
        label: toCssVarName(img.name),
        value: toCssVarName(img.name),
        url: img.value as string
    }))
})

const filteredSuggestions = computed(() => {
    if (!varValue.value) return allSuggestions.value
    const val = varValue.value.toLowerCase()
    return allSuggestions.value.filter((s: { value: string }) => s.value.includes(val))
})

// Watchers
const initFromProp = () => {
    const val = props.value || ''
    if (val.startsWith('var(') || val.startsWith('--')) {
        inputMode.value = 'var'
        varValue.value = val.startsWith('var(') ? val.replace(/^var\(\s*(.+?)\s*\)$/, '$1') : val
        manualValue.value = ''
    } else {
        inputMode.value = 'manual'
        manualValue.value = val
        varValue.value = ''
    }
}

watch(() => props.value, initFromProp, { immediate: true })

// Methods
const handleManualInput = (val: string | number) => {
  manualValue.value = val as string
  emit('update:value', val)
}

const handleVarInput = () => {
    let val = varValue.value
    if (val && val.startsWith('--')) {
        emit('update:value', `var(${val})`)
    } else {
        // Maybe user is typing "brand", we don't emit valid var yet or we emit raw?
        // Let's stick to valid css var syntax for emit
        // But if they type "blue", we can't emit "blue".
        // Use emit only if valid?
    }
}

const selectVar = (val: string) => {
    varValue.value = val
    showVarSuggestions.value = false
    emit('update:value', `var(${val})`)
}

const handleSelection = (media: { url: string; extension?: string }) => {
  if (media && media.url) {
    const ext = media.extension || (media.url.split('.').pop() || '').toLowerCase().split('?')[0]
    if (props.allowedExtensions && props.allowedExtensions.length > 0 && !props.allowedExtensions.includes(ext)) {
      toast.error.validation(`File type .${ext} is not allowed for this field.`)
      return
    }
    // Force switch to manual if selecting from picker
    inputMode.value = 'manual' 
    manualValue.value = media.url
    emit('update:value', media.url)
  }
}

const clearValue = () => {
  if (inputMode.value === 'manual') manualValue.value = ''
  else varValue.value = ''
  emit('update:value', '')
}

const toggleModeDropdown = () => showModeDropdown.value = !showModeDropdown.value

const setMode = (mode: string) => {
    if (inputMode.value === mode) {
        showModeDropdown.value = false
        return
    }
    inputMode.value = mode
    showModeDropdown.value = false
    
    // Clear value when switching to prevent incompatible values
    manualValue.value = ''
    varValue.value = ''
    emit('update:value', '')
}

const handleVarBlur = () => {
    setTimeout(() => {
        showVarSuggestions.value = false
    }, 200)
}

// Click Outside
const handleClickOutside = (event: MouseEvent) => {
    if (showModeDropdown.value && modeDropdownRef.value && !(modeDropdownRef.value as HTMLElement).contains(event.target as Node)) {
        showModeDropdown.value = false
    }
    if (showVarSuggestions.value && varInputRef.value && !(varInputRef.value as HTMLElement).contains(event.target as Node)) {
        showVarSuggestions.value = false
    }
}

onMounted(() => {
    window.addEventListener('mousedown', handleClickOutside)
})
onUnmounted(() => {
    window.removeEventListener('mousedown', handleClickOutside)
})

</script>

<style scoped>
.upload-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.preview-container {
  position: relative;
  width: 100%;
  height: 120px;
  background: var(--builder-bg-tertiary);
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.preview-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  background-size: contain;
  background-position: center;
  background-repeat: no-repeat;
}

div.preview-image {
  width: 100%;
  height: 100%;
}

.preview-container.is-inherited .preview-image {
  opacity: 0.4;
  filter: grayscale(0.5);
}

.inherited-badge {
  position: absolute;
  bottom: 8px;
  left: 8px;
  background: rgba(0, 0, 0, 0.6);
  color: white;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  pointer-events: none;
}

.remove-btn {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.5);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.remove-btn:hover {
  background: rgba(0, 0, 0, 0.8);
}

.preview-file-icon {
  color: var(--builder-text-muted);
}

.controls-row {
    display: flex;
    gap: 4px;
    align-items: center;
}

.mode-toggle {
    position: relative;
    width: 32px;
    height: 32px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
}

.mode-btn {
    width: 100%;
    height: 100%;
    background: transparent;
    border: none;
    color: var(--builder-text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.mode-btn:hover {
    color: var(--builder-text-primary);
}

.mode-dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    width: 120px;
    background: var(--builder-bg-background);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    margin-top: 4px;
    z-index: 100;
    font-size: 12px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

.mode-item {
    padding: 6px 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    color: var(--builder-text-secondary);
}

.mode-item:hover, .mode-item.active {
    background: var(--builder-bg-secondary);
    color: var(--builder-text-primary);
}

.var-input {
    width: 100%;
    height: 32px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    padding: 0 8px;
    font-size: 13px;
    color: var(--builder-text-primary);
    font-family: inherit;
}

.var-input:focus {
    outline: none;
    border-color: var(--builder-accent);
}

.var-suggestions-dropdown {
  position: absolute;
  top: 100%; 
  left: 0; 
  width: 100%;
  background-color: var(--builder-bg-background, #1f2937);
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 100;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  margin-top: 4px;
}

.var-suggestion-item {
  padding: 6px 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.var-suggestion-item:hover {
  background-color: var(--builder-bg-secondary);
}

.suggestion-preview {
    width: 24px;
    height: 24px;
    border-radius: 3px;
    overflow: hidden;
    background: var(--builder-bg-tertiary);
    border: 1px solid var(--builder-border);
    flex-shrink: 0;
}

.suggestion-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.suggestion-text {
    font-size: 12px;
    color: var(--builder-text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.var-suggestion-empty {
  padding: 8px;
  font-size: 11px;
  color: var(--builder-text-muted);
  text-align: center;
}
</style>

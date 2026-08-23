<template>
  <div class="attribute-field">
    <div v-if="localValue.length > 0" class="attributes-list mb-3">
      <div v-for="(attr, index) in localValue" :key="index" class="attribute-item-wrapper mb-2 border border-builder-border rounded-md overflow-hidden">
        <div class="attribute-item flex items-center justify-between p-3 bg-builder-bg-secondary">
          <div class="attribute-info">
            <div class="attribute-name font-bold text-sm">{{ attr.name }}</div>
            <div class="attribute-value text-xs text-muted">{{ attr.value || '(no value)' }}</div>
          </div>
          <div class="attribute-actions flex gap-1">
            <IconButton :icon="editingIndex === index ? ChevronUp : Settings2" size="sm" @click="toggleEdit(index)" />
            <IconButton :icon="Trash2" size="sm" variant="ghost" class="text-red-500 hover:bg-red-500/10 hover:text-red-600" @click="removeAttribute(index)" />
          </div>
        </div>

        <!-- Inline Editor -->
        <div v-if="editingIndex === index" class="attribute-editor p-3 bg-builder-bg-secondary/30 border-t border-builder-border">
            <div class="editor-row mb-3">
                <BaseLabel class="mb-1 text-[10px] uppercase opacity-60">Attribute Name</BaseLabel>
                <input 
                    type="text" 
                    class="builder-input w-full" 
                    v-model="attr.name" 
                    placeholder="e.g. data-id"
                    @input="updateValue"
                >
            </div>
            <div class="editor-row">
                <BaseLabel class="mb-1 text-[10px] uppercase opacity-60">Attribute Value</BaseLabel>
                <input 
                    type="text" 
                    class="builder-input w-full" 
                    v-model="attr.value" 
                    placeholder="Value"
                    @input="updateValue"
                >
            </div>
        </div>
      </div>
    </div>

    <div class="relative" ref="pickerTrigger">
      <BaseButton 
        variant="outline" 
        class="w-full justify-start gap-2 h-9"
        @click.stop="togglePicker"
      >
        <Plus :size="14" />
        {{ $t('builder.advanced.attributes.add', 'Add Attribute') }}
      </BaseButton>
    </div>

    <BasePopover
      v-if="isPickerOpen"
      :is-open="isPickerOpen"
      :trigger-rect="pickerRect"
      :width="280"
      :no-padding="true"
      :show-close="false"
      @close="isPickerOpen = false"
    >
      <div class="popover-content custom-scrollbar">
        <button v-for="opt in presetOptions" :key="opt" class="dropdown-item" @click="selectPreset(opt)">
          {{ opt }}
        </button>
        <div class="dropdown-divider"></div>
        <button class="dropdown-item custom-entry" @click="startCustomEntry">
          {{ $t('builder.advanced.attributes.custom', 'Enter Custom Attribute') }}
        </button>
      </div>
    </BasePopover>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import type { BlockInstance, SettingDefinition } from '@/types/builder'
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Settings2 from 'lucide-vue-next/dist/esm/icons/settings.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import ChevronUp from 'lucide-vue-next/dist/esm/icons/chevron-up.js';
import { BaseButton, IconButton, BaseLabel, BasePopover } from '@/components/builder/ui'

interface AttributeItem {
  name: string;
  value: string;
}

const props = defineProps<{
  field?: SettingDefinition;
  value: AttributeItem[];
  module?: BlockInstance;
}>()

const emit = defineEmits(['update:value'])

const localValue = ref<AttributeItem[]>([...(props.value || [])])
const isPickerOpen = ref(false)
const pickerRect = ref<DOMRect | undefined>(undefined)
const pickerTrigger = ref<HTMLElement | null>(null)
const editingIndex = ref(-1)

const presetOptions = [
  'class', 'id', 'title', 'alt', 'rel', 'target', 'role', 'aria-label', 'data-'
]

const togglePicker = () => {
  isPickerOpen.value = !isPickerOpen.value
  if (isPickerOpen.value && pickerTrigger.value) {
    pickerRect.value = (pickerTrigger.value as HTMLElement).getBoundingClientRect()
  }
}

const selectPreset = (name: string) => {
  addAttribute(name)
  isPickerOpen.value = false
}

const startCustomEntry = () => {
  addAttribute('custom-attr')
  isPickerOpen.value = false
}

const addAttribute = (name: string) => {
  localValue.value.push({ name, value: '' })
  updateValue()
  editingIndex.value = localValue.value.length - 1
}

const removeAttribute = (index: number) => {
  localValue.value.splice(index, 1)
  updateValue()
  if (editingIndex.value === index) editingIndex.value = -1
}

const toggleEdit = (index: number) => {
  editingIndex.value = editingIndex.value === index ? -1 : index
}

const updateValue = () => {
    emit('update:value', localValue.value)
}

watch(() => props.value, (newVal) => {
  localValue.value = [...(newVal || [])]
}, { deep: true })


</script>

<style scoped>
.attribute-field {
  width: 100%;
}

.attribute-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-sm);
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-xs);
}

.attribute-name {
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--builder-text-primary);
}

.attribute-actions {
  display: flex;
  gap: var(--spacing-xs);
}

.popover-content {
  max-height: 250px;
  overflow-y: auto;
  padding: 4px;
  background: var(--builder-bg-background);
}

.dropdown-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 8px 12px;
  background: transparent;
  border: none;
  color: var(--builder-text-secondary);
  font-size: var(--font-size-md);
  cursor: pointer;
  border-radius: var(--border-radius-sm);
  transition: var(--transition-fast);
  text-align: left;
}

.dropdown-item:hover {
  background: var(--builder-bg-tertiary);
  color: var(--builder-text-primary);
}

.dropdown-divider {
  height: 1px;
  background: var(--builder-border);
  margin: 4px 0;
}

.custom-entry {
  font-weight: 600;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #475569;
  border-radius: 10px;
}
</style>

<template>
  <div class="repeater-field">
    <!-- List Items -->
    <div class="repeater-list" v-if="Array.isArray(value) && value.length > 0">
      <div 
        v-for="(item, index) in value" 
        :key="index" 
        class="repeater-item"
        :class="{ 'is-open': openIndex === index }"
      >
        <!-- Item Header -->
        <div class="repeater-item-header" @click="toggleItem(index)">
          <div class="repeater-item-handle">
            <GripVertical :size="14" class="handle-icon" />
          </div>
          <div class="repeater-item-label">
            {{ getItemLabel(item, index) }}
          </div>
          <div class="repeater-item-actions">
            <button class="action-btn delete-btn" @click.stop="deleteItem(index)" title="Remove Item">
              <Trash2 :size="14" />
            </button>
            <ChevronDown :size="16" class="chevron-icon" />
          </div>
        </div>

        <!-- Item Body (Fields) -->
        <div v-show="openIndex === index" class="repeater-item-body">
            <div v-for="subField in mappedFields" :key="subField.name" class="sub-field-row">
                <FieldRenderer
                    :field="subField"
                    :value="item[subField.nativeKey]"
                    :module="module"
                    @update="(val) => updateItemField(index, subField.nativeKey, val)"
                />
            </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="repeater-empty">
        {{ t('builder.fields.repeater.empty', 'No items yet') }}
    </div>

    <!-- Add Button -->
    <button class="repeater-add-btn" @click="addItem">
      <Plus :size="14" />
      <span>{{ t('builder.fields.repeater.add', 'Add Item') }}</span>
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import type { BlockInstance, SettingDefinition, ModuleField } from '@/types/builder'
import { useI18n } from 'vue-i18n'
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import GripVertical from 'lucide-vue-next/dist/esm/icons/grip-vertical.js';
import FieldRenderer from '@/components/builder/fields/FieldRenderer.vue'

// Recursive component definition is handled automatically by Vue for local registration if loop is indirect.
// But since FieldRenderer imports RepeaterField (via dynamic import), we should be fine.
// Wait, FieldRenderer imports RepeaterField dynamically? I need to add that later.

const props = defineProps<{
  field: SettingDefinition;
  value: Record<string, unknown>[];
  module: BlockInstance;
}>()

const emit = defineEmits(['update:value'])
const { t } = useI18n()

const openIndex = ref<number | null>(null)

const toggleItem = (index: number) => {
    openIndex.value = openIndex.value === index ? null : index
}

// Map 'key' from definition to 'name' for FieldRenderer
const mappedFields = computed(() => {
    return (props.field.fields || []).map((f) => {
        const key = getFieldKey(f)
        return {
            ...f,
            name: key || '',
            nativeKey: key || '', 
            responsive: false 
        } as SettingDefinition & { nativeKey: string }
    })
})

const getItemLabel = (item: Record<string, unknown>, index: number) => {
    if (item.label) return String(item.label)
    if (item.title) return String(item.title)
    if (item.text) return String(item.text)
    if (item.question) return String(item.question) // For Accordion
    
    return `${props.field.itemLabel || 'Item'} ${index + 1}`
}

const addItem = () => {
    const newItem: Record<string, unknown> = {}
    if (props.field.fields) {
        props.field.fields.forEach((f) => {
            const key: string = getFieldKey(f)
            const defaultValue = (f as SettingDefinition).default
            newItem[key] = defaultValue !== undefined ? defaultValue : ''
        })
    }
    
    const newList = [...(props.value || []), newItem]
    emit('update:value', newList)
    openIndex.value = newList.length - 1
}

const getFieldKey = (f: ModuleField): string => {
    if ('key' in f && f.key) return String(f.key)
    if ('name' in f && f.name) return String(f.name)
    if ('id' in f && f.id) return String(f.id)
    return ''
}

const deleteItem = (index: number) => {
    const newList = [...props.value]
    newList.splice(index, 1)
    emit('update:value', newList)
    if (openIndex.value === index) openIndex.value = null
}

const updateItemField = (index: number, key: string, val: unknown) => {
    const newList = [...props.value]
    newList[index] = { ...newList[index], [key]: val }
    emit('update:value', newList)
}
</script>

<style scoped>
.repeater-field {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.repeater-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    border: 1px solid var(--builder-border);
    border-radius: 6px;
    overflow: hidden;
    background: var(--builder-bg-secondary);
}

.repeater-item {
    background: var(--builder-bg-primary);
    border-bottom: 1px solid var(--builder-border);
}

.repeater-item:last-child {
    border-bottom: none;
}

.repeater-item-header {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    cursor: pointer;
    gap: 8px;
    user-select: none;
    transition: background 0.2s;
}

.repeater-item-header:hover {
    background: var(--builder-bg-hover);
}

.repeater-item.is-open .repeater-item-header {
    background: var(--builder-bg-active);
}

.repeater-item-handle {
    color: var(--builder-text-muted);
    cursor: grab;
    display: flex; /* For alignment */
}

.repeater-item-label {
    flex: 1;
    font-size: 13px;
    font-weight: 500;
    color: var(--builder-text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.repeater-item-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--builder-text-muted);
}

.action-btn {
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    display: flex;
    color: inherit;
    transition: all 0.2s;
}

.action-btn:hover {
    background: var(--builder-bg-hover);
    color: var(--builder-text-primary);
}

.delete-btn:hover {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.1);
}

.chevron-icon {
    transition: transform 0.2s;
}

.repeater-item.is-open .chevron-icon {
    transform: rotate(180deg);
}

.repeater-item-body {
    padding: 16px;
    border-top: 1px solid var(--builder-border);
    background: var(--builder-bg-secondary); /* Slightly different bg for contrast */
}

.sub-field-row {
    margin-bottom: 12px;
}

.sub-field-row:last-child {
    margin-bottom: 0;
}

.repeater-add-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px;
    width: 100%;
    background: var(--builder-bg-primary);
    border: 1px dashed var(--builder-border);
    border-radius: 6px;
    color: var(--builder-text-secondary);
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}

.repeater-add-btn:hover {
    border-color: var(--builder-accent);
    color: var(--builder-accent);
    background: rgba(37, 99, 235, 0.05); /* Assuming blue accent */
}

.repeater-empty {
    padding: 12px;
    text-align: center;
    color: var(--builder-text-muted);
    font-size: 13px;
    font-style: italic;
    border: 1px dashed var(--builder-border);
    border-radius: 6px;
}
</style>

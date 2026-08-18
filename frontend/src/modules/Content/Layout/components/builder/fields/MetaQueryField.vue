<template>
  <div class="meta-query-field">
    <!-- List View -->
    <div v-if="!isEditing" class="meta-query-list">
      <div 
        v-for="(query, index) in localValue" 
        :key="index" 
        class="query-item"
      >
        <div class="query-info">
          <span class="query-index">{{ index + 1 }}</span>
          <span class="query-preview" v-if="query.key">{{ query.key }} {{ query.compare }} {{ query.value }}</span>
          <span class="query-preview placeholder" v-else>New Meta Query</span>
        </div>
        
        <div class="query-actions">
           <div class="action-icon" :title="$t('builder.fields.actions.edit')" @click="editQuery(index)">
               <Pencil :size="14" />
           </div>
           <div class="action-icon" :title="$t('builder.fields.actions.duplicate')" @click="duplicateQuery(index)">
               <Copy :size="14" />
           </div>
           <div class="action-icon delete-btn" :title="$t('builder.fields.actions.remove')" @click="removeQuery(index)">
               <Trash2 :size="14" />
           </div>
        </div>
      </div>

      <div class="add-action">
        <BaseButton variant="ghost" size="sm" class="add-btn" @click="addQuery">
            <Plus :size="16" />
            <span>{{ t('builder.settings.meta_query.add') || 'Add Meta Query' }}</span>
        </BaseButton>
      </div>
    </div>

    <!-- Edit Form -->
    <div v-else class="query-form">
      <div class="form-group">
        <label class="form-label">{{ t('builder.settings.meta_query.key') || 'Meta Key' }}</label>
        <BaseInput v-model="editingQuery.key" :placeholder="t('builder.settings.meta_query.key_placeholder') || 'Enter meta key'" />
      </div>

      <div class="form-group">
        <label class="form-label">{{ t('builder.settings.meta_query.value') || 'Meta Value' }}</label>
        <BaseInput v-model="editingQuery.value" :placeholder="t('builder.settings.meta_query.value_placeholder') || 'Enter meta value'" />
      </div>

      <div class="form-group">
        <label class="form-label">{{ t('builder.settings.meta_query.compare') || 'Compare' }}</label>
        <BaseDropdown ref="compareDropdown" width="100%">
            <template #trigger="{ open }">
                <div class="custom-select-trigger" :class="{ open }">
                    <span>{{ getSelectedCompareLabel() }}</span>
                    <ChevronDown :size="14" class="select-arrow" />
                </div>
            </template>
            <template #default="{ close }">
                <div class="dropdown-options">
                    <div 
                        v-for="opt in compareOptions" 
                        :key="opt.value" 
                        class="dropdown-item"
                        :class="{ active: editingQuery.compare === opt.value }"
                        @click="editingQuery.compare = opt.value; close()"
                    >
                        {{ opt.label }}
                    </div>
                </div>
            </template>
        </BaseDropdown>
      </div>

      <div class="form-group">
        <label class="form-label">{{ t('builder.settings.meta_query.type') || 'Type' }}</label>
        <BaseDropdown ref="typeDropdown" width="100%">
            <template #trigger="{ open }">
                <div class="custom-select-trigger" :class="{ open }">
                    <span>{{ getSelectedTypeLabel() }}</span>
                    <ChevronDown :size="14" class="select-arrow" />
                </div>
            </template>
            <template #default="{ close }">
                <div class="dropdown-options">
                    <div 
                        v-for="opt in typeOptions" 
                        :key="opt.value" 
                        class="dropdown-item"
                        :class="{ active: editingQuery.type === opt.value }"
                        @click="editingQuery.type = opt.value; close()"
                    >
                        {{ opt.label }}
                    </div>
                </div>
            </template>
        </BaseDropdown>
      </div>

      <div class="form-actions">
        <BaseButton variant="secondary" size="sm" @click="cancelEdit">{{ t('builder.fields.actions.cancel') }}</BaseButton>
        <BaseButton variant="primary" size="sm" @click="applyEdit">{{ t('builder.fields.actions.apply') }}</BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import { BaseButton, BaseInput, BaseDropdown } from '@/components/builder/ui'
import type { SettingDefinition } from '@/types/builder'

interface MetaQueryItem {
  key: string;
  value: string;
  compare: string;
  type: string;
}

const props = defineProps<{
  field?: SettingDefinition;
  value: MetaQueryItem[];
}>()

const emit = defineEmits(['update:value'])
const { t } = useI18n()

// Local state
const localValue = ref<MetaQueryItem[]>([...(props.value || [])])
const isEditing = ref(false)
const editingIndex = ref(-1)
const editingQuery = ref<MetaQueryItem>({
    key: '',
    value: '',
    compare: '=',
    type: 'CHAR'
})

// Watch for external updates
watch(() => props.value, (newVal) => {
    if (JSON.stringify(newVal || []) !== JSON.stringify(localValue.value)) {
        localValue.value = [...(newVal || [])]
    }
}, { deep: true })

// Options
const compareOptions = [
    { value: '=', label: 'Equals (=)' },
    { value: '!=', label: 'Not Equals (!=)' },
    { value: '>', label: 'Greater Than (>)' },
    { value: '>=', label: 'Greater Than or Equal (>=)' },
    { value: '<', label: 'Less Than (<)' },
    { value: '<=', label: 'Less Than or Equal (<=)' },
    { value: 'LIKE', label: 'LIKE' },
    { value: 'NOT LIKE', label: 'NOT LIKE' },
    { value: 'IN', label: 'IN' },
    { value: 'NOT IN', label: 'NOT IN' },
    { value: 'BETWEEN', label: 'BETWEEN' },
    { value: 'NOT BETWEEN', label: 'NOT BETWEEN' },
    { value: 'EXISTS', label: 'EXISTS' },
    { value: 'NOT EXISTS', label: 'NOT EXISTS' }
]

const typeOptions = [
    { value: 'CHAR', label: 'Character' },
    { value: 'NUMERIC', label: 'Numeric' },
    { value: 'BINARY', label: 'Binary' },
    { value: 'DATE', label: 'Date' },
    { value: 'DATETIME', label: 'Datetime' },
    { value: 'DECIMAL', label: 'Decimal' },
    { value: 'SIGNED', label: 'Signed' },
    { value: 'TIME', label: 'Time' },
    { value: 'UNSIGNED', label: 'Unsigned' }
]

const getSelectedCompareLabel = () => {
    return compareOptions.find(o => o.value === editingQuery.value.compare)?.label || ''
}

const getSelectedTypeLabel = () => {
    return typeOptions.find(o => o.value === editingQuery.value.type)?.label || ''
}

// Actions
const addQuery = () => {
    editingQuery.value = {
        key: '',
        value: '',
        compare: '=',
        type: 'CHAR'
    }
    editingIndex.value = -1
    isEditing.value = true
}

const editQuery = (index: number) => {
    editingQuery.value = { ...localValue.value[index] }
    editingIndex.value = index
    isEditing.value = true
}

const duplicateQuery = (index: number) => {
    const newVal = [...localValue.value]
    newVal.splice(index + 1, 0, { ...newVal[index] })
    localValue.value = newVal
    emitUpdates()
}

const removeQuery = (index: number) => {
    const newVal = [...localValue.value]
    newVal.splice(index, 1)
    localValue.value = newVal
    emitUpdates()
}

const cancelEdit = () => {
    isEditing.value = false
    editingQuery.value = {
        key: '',
        value: '',
        compare: '=',
        type: 'CHAR'
    }
    editingIndex.value = -1
}

const applyEdit = () => {
    const newVal = [...localValue.value]
    if (editingIndex.value === -1) {
        newVal.push(editingQuery.value as MetaQueryItem)
    } else {
        newVal[editingIndex.value] = editingQuery.value as MetaQueryItem
    }
    localValue.value = newVal
    emitUpdates()
    isEditing.value = false
}

const emitUpdates = () => {
    emit('update:value', localValue.value)
}
</script>

<style scoped>
.meta-query-field {
    width: 100%;
}

.meta-query-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.query-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    background: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    transition: border-color 0.2s;
}

.query-item:hover {
    border-color: var(--builder-border-hover);
}

.query-info {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: var(--builder-text-primary);
}

.query-index {
    font-weight: 600;
    color: var(--builder-text-muted);
    min-width: 16px;
}

.query-preview.placeholder {
    color: var(--builder-text-muted);
    font-style: italic;
}

.query-actions {
    display: flex;
    gap: 6px;
    align-items: center;
}

.action-icon {
    color: var(--builder-text-muted);
    cursor: pointer;
    padding: 2px;
    transition: color 0.15s;
}

.action-icon:hover {
    color: var(--builder-text-primary);
}

.action-icon.delete-btn:hover {
    color: var(--builder-error);
}

.add-btn {
    width: fit-content;
    color: var(--builder-text-secondary);
}

.add-btn:hover {
    color: var(--builder-accent);
}

/* Form Styles */
.query-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    padding: 12px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--builder-text-secondary);
}

.custom-select-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    padding: 8px 12px;
    font-size: 13px;
    color: var(--builder-text-primary);
    transition: all 0.2s;
}

.custom-select-trigger:hover, .custom-select-trigger.open {
    border-color: var(--builder-accent);
}

.select-arrow {
    color: var(--builder-text-muted);
}

.dropdown-options {
    max-height: 250px;
    overflow-y: auto;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 4px;
}

:deep(.base-dropdown-wrapper) {
    width: 100%;
}
</style>

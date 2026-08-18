<template>
  <div class="condition-field">
    <div v-if="localValue.length > 0" class="conditions-list mb-3">
      <div v-for="(condition, index) in localValue" :key="index" class="condition-item-wrapper mb-2 border border-builder-border rounded-md overflow-hidden">
        <div class="condition-item flex items-center justify-between p-3 bg-builder-bg-secondary">
          <div class="condition-info">
            <div class="condition-label font-bold text-sm">{{ getConditionLabel(condition) }}</div>
            <div class="condition-summary text-xs text-muted">{{ getConditionSummary(condition) }}</div>
          </div>
          <div class="condition-actions flex gap-1">
            <IconButton :icon="editingIndex === index ? ChevronUp : Settings2" size="sm" @click="toggleEdit(index)" />
            <IconButton 
              :icon="Trash2" 
              size="sm" 
              variant="ghost" 
              class="text-red-500 hover:bg-red-500/10"
              @click="removeCondition(index)" 
            />
</div>
        </div>

        <!-- Inline Editor for Condition Rules -->
        <div v-if="editingIndex === index" class="condition-editor p-3 bg-builder-bg-secondary/30 border-t border-builder-border">
            <div class="editor-row mb-3">
                <BaseLabel class="mb-1 text-[10px] uppercase opacity-60">Condition</BaseLabel>
                <select class="builder-select w-full" v-model="condition.condition" @change="updateValue">
                    <option value="is">Is</option>
                    <option value="is_not">Is Not</option>
                    <option value="contains">Contains</option>
                    <option value="empty" v-if="condition.type === 'form_field'">Is Empty</option>
                    <option value="not_empty" v-if="condition.type === 'form_field'">Is Not Empty</option>
                    <option value="gt" v-if="condition.type === 'form_field'">Greater Than</option>
                    <option value="lt" v-if="condition.type === 'form_field'">Less Than</option>
                    <option value="exists" v-if="['url_param', 'cookie'].includes(condition.type)">Exists</option>
                </select>
            </div>

            <div v-if="['post_meta', 'url_param', 'cookie', 'form_field'].includes(condition.type)" class="editor-row mb-3">
                <BaseLabel class="mb-1 text-[10px] uppercase opacity-60">{{ condition.type === 'form_field' ? 'Field ID' : 'Key (Meta/Param/Cookie Name)' }}</BaseLabel>
                <input 
                    type="text" 
                    class="builder-input w-full" 
                    v-model="condition.key" 
                    :placeholder="condition.type === 'form_field' ? 'e.g. first_name' : 'e.g. promo_code'"
                    @input="updateValue"
                >
            </div>

            <div v-if="!['exists', 'empty', 'not_empty', 'logged_in', 'guest'].includes(condition.condition)" class="editor-row">
                <BaseLabel class="mb-1 text-[10px] uppercase opacity-60">Value</BaseLabel>
                <input 
                    type="text" 
                    class="builder-input w-full" 
                    v-model="condition.value" 
                    placeholder="Value to match"
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
        {{ $t('builder.advanced.conditions.add', 'Add Condition') }}
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
      <div class="condition-picker">
        <div class="popover-search">
          <Search :size="14" />
          <input 
            v-model="searchQuery" 
            type="text" 
            :placeholder="$t('builder.advanced.conditions.search', 'Search conditions...')"
            autofocus
          >
        </div>
        <div class="popover-content custom-scrollbar">
          <div v-for="(group, groupName) in filteredGroups" :key="groupName" class="data-group">
            <h4 class="group-title">{{ group.label }}</h4>
            <div class="group-items">
              <button 
                v-for="item in group.items" 
                :key="item.id"
                class="data-item"
                @click="selectCondition(item)"
              >
                <span class="item-label">{{ item.label }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </BasePopover>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { BlockInstance, SettingDefinition } from '@/types/builder'
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Settings2 from 'lucide-vue-next/dist/esm/icons/settings.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import ChevronUp from 'lucide-vue-next/dist/esm/icons/chevron-up.js';
import { BaseButton, BasePopover, IconButton, BaseLabel } from '@/components/builder/ui'

interface ConditionItem {
  type: string;
  enabled: boolean;
  condition: string;
  value: string;
  key: string;
}

interface ConditionGroup {
  label: string;
  items: { id: string; label: string }[];
}

const props = defineProps<{
  field?: SettingDefinition;
  value: ConditionItem[];
  module: BlockInstance;
}>()

const emit = defineEmits(['update:value'])

const localValue = ref<ConditionItem[]>([...(props.value || [])])
const editingIndex = ref(-1)

const isPickerOpen = ref(false)
const pickerRect = ref<DOMRect | undefined>(undefined)
const pickerTrigger = ref<HTMLElement | null>(null)
const searchQuery = ref('')

const conditionGroups: Record<string, ConditionGroup> = {
  form: {
    label: 'Form Interaction',
    items: [
      { id: 'form_field', label: 'Form Field Value' }
    ]
  },
  post_info: {
    label: 'Post Info',
    items: [
      { id: 'post_type', label: 'Post Type' },
      { id: 'post_category', label: 'Post Category' },
      { id: 'post_tag', label: 'Post Tag' },
      { id: 'author', label: 'Author' },
      { id: 'post_meta', label: 'Custom Field (Post Meta)' }
    ]
  },
  location: {
    label: 'Location',
    items: [
      { id: 'tag_page', label: 'Tag Page' },
      { id: 'category_page', label: 'Category Page' },
      { id: 'date_archive', label: 'Date Archive' },
      { id: 'search_results', label: 'Search Results' }
    ]
  },
  user: {
    label: 'User',
    items: [
      { id: 'logged_in', label: 'Logged In Status' },
      { id: 'user_role', label: 'User Role' }
    ]
  },
  interaction: {
    label: 'Interaction',
    items: [
      { id: 'date_time', label: 'Date & Time' },
      { id: 'page_visit', label: 'Page Visit' },
      { id: 'post_visit', label: 'Post Visit' },
      { id: 'view_count', label: 'Number Of Views' },
      { id: 'url_param', label: 'URL Parameter' }
    ]
  },
  device: {
    label: 'Device',
    items: [
      { id: 'browser', label: 'Browser' },
      { id: 'os', label: 'Operating System' },
      { id: 'cookie', label: 'Cookie' }
    ]
  }
}

const filteredGroups = computed<Record<string, ConditionGroup>>(() => {
  if (!searchQuery.value) return conditionGroups
  const query = searchQuery.value.toLowerCase()
  const result: Record<string, ConditionGroup> = {}
  
  Object.entries(conditionGroups).forEach(([key, group]) => {
    const items = group.items.filter((item: { id: string; label: string }) => 
      item.label.toLowerCase().includes(query)
    )
    if (items.length > 0) {
      result[key] = { ...group, items }
    }
  })
  
  return result
})

const getConditionLabel = (condition: ConditionItem) => {
  for (const group of Object.values(conditionGroups)) {
    const item = group.items.find(i => i.id === condition.type)
    if (item) return item.label
  }
  return condition.type
}

const getConditionSummary = (condition: ConditionItem) => {
  if (!condition.condition) return 'Click settings to configure'
  let summary = `${condition.condition} ${condition.value || ''}`
  if (condition.key) summary = `${condition.key}: ${summary}`
  return summary
}

const togglePicker = () => {
  isPickerOpen.value = !isPickerOpen.value
  if (isPickerOpen.value && pickerTrigger.value) {
    pickerRect.value = (pickerTrigger.value as HTMLElement).getBoundingClientRect()
  }
}

const selectCondition = (item: { id: string; label: string }) => {
  localValue.value.push({
    type: item.id,
    enabled: true,
    condition: 'is',
    value: '',
    key: ''
  })
  isPickerOpen.value = false
  updateValue()
  editingIndex.value = localValue.value.length - 1
}

const removeCondition = (index: number) => {
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
.condition-field {
  width: 100%;
}

.condition-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-sm);
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-xs);
}

.condition-label {
  font-size: var(--font-size-sm);
  font-weight: 600;
}

.condition-actions {
  display: flex;
  gap: var(--spacing-xs);
}

/* Picker Styles (Duplicate of DynamicDataPopover for consistency) */
.condition-picker {
  width: 280px;
  max-height: 400px;
  display: flex;
  flex-direction: column;
}

.popover-search {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: 8px 12px;
  border-bottom: 1px solid var(--builder-border);
  color: var(--builder-text-muted);
}

.popover-search input {
  flex: 1;
  background: transparent;
  border: none;
  font-size: var(--font-size-md);
  color: var(--builder-text-primary);
  outline: none;
}

.popover-content {
  flex: 1;
  overflow-y: auto;
  padding: 4px;
  background: var(--builder-bg-background);
}

.data-group {
  padding-bottom: 8px;
}

.group-title {
  padding: 8px 12px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--builder-text-muted);
  letter-spacing: 0.5px;
}

.group-items {
  display: flex;
  flex-direction: column;
}

.data-item {
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

.data-item:hover {
  background: var(--builder-bg-tertiary);
  color: var(--builder-text-primary);
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: var(--builder-border);
  border-radius: 10px;
}
</style>

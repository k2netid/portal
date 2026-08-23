<template>
  <div class="interaction-field">
    <div v-if="localValue.length > 0" class="interactions-list mb-3">
      <div v-for="(interaction, index) in localValue" :key="index" class="interaction-item-wrapper">
        <div class="interaction-item">
          <div class="interaction-info">
            <div class="interaction-label">{{ getInteractionLabel(interaction.trigger) }}</div>
            <div class="interaction-summary text-xs text-muted">
                {{ getActionLabel(interaction.action) }} 
                <span v-if="interaction.targetId" class="text-primary-400">→ {{ getTargetLabel(interaction.targetId) }}</span>
            </div>
          </div>
          <div class="interaction-actions">
            <IconButton :icon="editingIndex === index ? ChevronUp : Settings2" size="sm" @click="toggleEdit(index)" />
            <IconButton 
              :icon="Trash2" 
              size="sm" 
              variant="ghost" 
              class="text-red-500 hover:bg-red-500/10"
              @click="removeInteraction(index)" 
            />
</div>
        </div>
        
        <!-- Inline Editor -->
        <div v-if="editingIndex === index" class="interaction-editor p-3 border-t border-builder-border bg-builder-bg-secondary/50">
            <div class="editor-row mb-3">
                <BaseLabel class="mb-1 text-[10px] uppercase opacity-60">Action</BaseLabel>
                <select class="builder-select w-full" v-model="interaction.action" @change="updateValue">
                    <option value="">Select Action</option>
                    <option v-for="act in availableActions" :key="act.id" :value="act.id">{{ act.label }}</option>
                </select>
            </div>

            <div class="editor-row mb-3">
                <BaseLabel class="mb-1 text-[10px] uppercase opacity-60">Target Block</BaseLabel>
                <select class="builder-select w-full" v-model="interaction.targetId" @change="updateValue">
                    <option :value="module.id">This Block (Self)</option>
                    <optgroup label="Other Blocks">
                        <option v-for="m in allModules" :key="m.id" :value="m.id">
                            {{ getBlockTitle(m) }} ({{ m.id.substring(0,5) }})
                        </option>
                    </optgroup>
                </select>
            </div>

            <div v-if="interaction.action?.includes('class')" class="editor-row">
                <BaseLabel class="mb-1 text-[10px] uppercase opacity-60">CSS Class Name</BaseLabel>
                <input 
                    type="text" 
                    class="builder-input w-full" 
                    v-model="interaction.className" 
                    placeholder="e.g. is-active"
                    @input="updateValue"
                >
            </div>

            <div v-if="interaction.action === 'play-animation'" class="editor-row">
                <BaseLabel class="mb-1 text-[10px] uppercase opacity-60">Animation Name</BaseLabel>
                 <select class="builder-select w-full" v-model="interaction.animationName" @change="updateValue">
                    <option value="animate-fade">Fade In</option>
                    <option value="animate-fade-up">Fade In Up</option>
                    <option value="animate-zoom">Zoom In</option>
                    <option value="animate-bounce-in">Bounce In</option>
                </select>
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
        {{ $t('builder.advanced.interactions.add', 'Add Interaction') }}
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
        <button v-for="opt in triggers" :key="opt.id" class="dropdown-item" @click="selectTrigger(opt.id)">
          {{ opt.label }}
        </button>
      </div>
    </BasePopover>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, inject, computed } from 'vue'
import type { BlockInstance, BuilderInstance, SettingDefinition } from '@/types/builder'
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Settings2 from 'lucide-vue-next/dist/esm/icons/settings.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import ChevronUp from 'lucide-vue-next/dist/esm/icons/chevron-up.js';
import { BaseButton, IconButton, BaseLabel, BasePopover } from '@/components/builder/ui'

interface InteractionItem {
  trigger: string;
  action: string;
  targetId: string;
  className?: string;
  animationName?: string;
}

const props = defineProps<{
  field?: SettingDefinition;
  value: InteractionItem[];
  module: BlockInstance;
}>()

const emit = defineEmits(['update:value'])

const builder = inject<BuilderInstance>('builder')
const localValue = ref<InteractionItem[]>([...(props.value || [])])
const isPickerOpen = ref(false)
const pickerRect = ref<DOMRect | undefined>(undefined)
const pickerTrigger = ref<HTMLElement | null>(null)
const editingIndex = ref(-1)

const triggers = [
  { id: 'click', label: 'Click' },
  { id: 'mouseenter', label: 'Mouse Enter' },
  { id: 'mouseleave', label: 'Mouse Exit' },
  { id: 'viewportenter', label: 'Viewport Enter' },
  { id: 'viewportexit', label: 'Viewport Exit' }
]

const availableActions = [
    { id: 'toggle-class', label: 'Toggle CSS Class' },
    { id: 'add-class', label: 'Add CSS Class' },
    { id: 'remove-class', label: 'Remove CSS Class' },
    { id: 'play-animation', label: 'Play Animation' }
]

const allModules = computed(() => {
    // Collect all modules from builder for target selection
    if (!builder) return []
    const modules = (builder as BuilderInstance & { allModules?: { value: BlockInstance[] } }).allModules?.value || builder.blocks.value || []
    return modules.filter((m: BlockInstance) => m.id !== props.module.id)
})

const getInteractionLabel = (id: string) => {
  return triggers.find(t => t.id === id)?.label || id
}

const getActionLabel = (id: string) => {
    return availableActions.find(a => a.id === id)?.label || id || '(no action)'
}

const getTargetLabel = (id: string) => {
    if (id === props.module.id) return 'Self'
    const modules = (builder as BuilderInstance & { allModules?: { value: BlockInstance[] } })?.allModules?.value || builder?.blocks.value || []
    const m = modules.find((mod: BlockInstance) => mod.id === id)
    return m ? getBlockTitle(m) : id.substring(0, 8)
}

const getBlockTitle = (m: BlockInstance) => {
    if (m.settings?._label) return String(m.settings._label)
    const def = builder?.getModuleDefinition(m.type)
    return def?.title || def?.label || m.type
}

const togglePicker = () => {
  isPickerOpen.value = !isPickerOpen.value
  if (isPickerOpen.value && pickerTrigger.value) {
    pickerRect.value = (pickerTrigger.value as HTMLElement).getBoundingClientRect()
  }
}

const selectTrigger = (trigger: string) => {
  localValue.value.push({ 
    trigger, 
    action: 'toggle-class', 
    targetId: props.module.id,
    className: 'is-active'
  })
  updateValue()
  isPickerOpen.value = false
  editingIndex.value = localValue.value.length - 1
}

const removeInteraction = (index: number) => {
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
.interaction-field {
  width: 100%;
}

.interaction-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-sm);
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-xs);
}

.interaction-label {
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--builder-text-primary);
}

.interaction-actions {
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

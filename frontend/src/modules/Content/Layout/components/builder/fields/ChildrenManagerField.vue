<template>
  <div class="children-manager">
    <!-- Children List -->
    <div class="children-list" v-if="children.length > 0">
      <div 
        v-for="child in children" 
        :key="child.id" 
        class="child-item"
        :class="{ 'child-item--selected': isSelected(child.id) }"
      >
        <div class="child-info" @click="openSettings(child)">
            <!-- Input-like box appearance -->
            <div class="child-input-box">
                {{ getChildLabel(child) }}
            </div>
        </div>
        
        <div class="child-actions-wrapper">
          <ModuleActions 
              :label="getChildLabel(child)"
              :show-info="true"
              :info-active="showInfoId === child.id"
              @edit="openSettings(child)"
              @duplicate="duplicateChild(child)"
              @delete="deleteChild(child)"
              @toggle-info="toggleInfo(child.id)"
          />
        </div>

        <!-- Inline Info Panel -->
        <transition name="fade-slide">
          <div v-if="showInfoId === child.id" class="child-info-panel">
              <p>{{ getChildInfo(child) }}</p>
          </div>
        </transition>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="empty-state">
        <p>{{ t('builder.fields.children.empty', { type: getChildTypeLabel() }) }}</p>
    </div>

    <!-- Actions -->
    <div class="manager-actions">
        <!-- Add Button -->
        <BaseButton variant="ghost" size="sm" class="add-btn" @click="addChild">
            <Plus :size="16" />
            <span>{{ t('builder.fields.children.add', { type: getChildTypeLabel() }) }}</span>
        </BaseButton>
        
        <!-- Special button for Section -> Row template structure -->
        <BaseButton 
            variant="secondary" 
            size="sm"
            class="template-btn" 
            @click="openStructureTemplate"
        >
            <Layout :size="14" />
            <span>{{ t('builder.fields.children.applyTemplate') }}</span>
        </BaseButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import type { BlockInstance, BuilderInstance, SettingDefinition } from '@/types/builder'
import { useI18n } from 'vue-i18n'
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import { BaseButton } from '@/components/builder/ui'
import ModuleActions from './ModuleActions.vue'

const props = defineProps<{
  field: SettingDefinition;
  module: BlockInstance;
}>()

const { t, te } = useI18n()
const builder = inject<BuilderInstance>('builder')

const showInfoId = ref<string | null>(null)

const toggleInfo = (id: string) => {
    if (showInfoId.value === id) {
        showInfoId.value = null
    } else {
        showInfoId.value = id
    }
}

const getChildInfo = (child: BlockInstance) => {
    // Return some basic info about the child
    const typeLabel = getChildTypeLabel()
    const name = getChildLabel(child)
    return `${typeLabel}: ${name}. ID: ${child.id}. Type: ${child.type}.`
}

const getChildTypeLabel = () => {
    if (props.module.type === 'section') return t('builder.fields.types.row')
    if (props.module.type === 'column' || props.module.type === 'row') return t('builder.fields.types.element')
    return t('builder.fields.types.item')
}

// Reactive children logic
const children = computed(() => {
    const currentModule = builder?.findModule(props.module.id)
    if (!currentModule || !currentModule.children || !currentModule.children.length) return []
    return currentModule.children as BlockInstance[]
})

const getChildLabel = (child: BlockInstance) => {
    if (child.type === 'row') return t('builder.fields.types.row')
    if (child.settings?.admin_label) return child.settings.admin_label as string
    
    // Try to translate module name
    if (te(`builder.modules.${child.type}`)) {
        return t(`builder.modules.${child.type}`)
    }
    
    return child.type.charAt(0).toUpperCase() + child.type.slice(1)
}

const isSelected = (id: string) => {
    return builder?.selectedModule.value?.id === id
}

// Actions
const openSettings = (child: BlockInstance) => {
    builder?.selectModule(child.id)
}

const duplicateChild = (child: BlockInstance) => {
    builder?.duplicateModule(child.id)
}

const deleteChild = (child: BlockInstance) => {
    builder?.removeModule(child.id)
}

const addChild = () => {
    if (props.module.type === 'section') {
        builder?.openInsertRowModal?.(props.module.id)
    } else if (props.module.type === 'row') {
        builder?.insertModule('column', props.module.id)
    } else if (props.module.type === 'column' || isGenericContainer.value) {
        // For columns and generic containers (like Group), open the module selector
        builder?.openInsertModal?.(props.module.id)
    } else {
        // For specialized modules (Accordion, Tabs, Map, etc.), 
        // find the allowed child type from definition and insert it
        const definition = builder?.getModuleDefinition(props.module.type)
        if (definition?.children && definition.children.length > 0) {
            const firstChildType = definition.children[0]
            if (firstChildType !== '*') {
                builder?.insertModule(firstChildType, props.module.id)
            }
        }
    }
}

const isGenericContainer = computed(() => {
    const definition = builder?.getModuleDefinition(props.module.type)
    return definition?.children?.includes('*')
})

const openStructureTemplate = () => {
    // Open the specialized structure template modal instead of the generic row modal
    builder?.openStructureTemplateModal?.(props.module.id, props.module.type)
}
</script>

<style scoped>
.children-manager {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.children-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.child-item {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 2px 0;
}

.child-info {
    flex: 1;
    cursor: pointer;
}

.child-input-box {
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    padding: 8px 12px;
    background: var(--builder-bg-primary);
    font-size: 13px;
    color: var(--builder-text-primary);
    transition: all 0.2s;
}

.child-input-box:hover {
    border-color: var(--builder-accent);
}

.child-item--selected .child-input-box {
    border-color: var(--builder-accent);
    box-shadow: 0 0 0 1px var(--builder-accent);
}

.child-actions-wrapper {
    display: flex;
    gap: 4px;
    align-items: center;
    opacity: 0;
    transition: opacity 0.2s;
    flex-shrink: 0;
}

.child-item:hover .child-actions-wrapper {
    opacity: 1;
}

.child-info-panel {
    width: 100%;
    order: 10;
    margin-top: 4px;
    padding: 8px 12px;
    background: var(--builder-bg-secondary);
    border-left: 2px solid var(--builder-accent);
    border-radius: 0 4px 4px 0;
    font-size: 11px;
    color: var(--builder-text-secondary);
    line-height: 1.5;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.2s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

/* Dropdown Menu */
.manager-actions {
    margin-top: 4px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.add-btn {
    width: fit-content;
    color: var(--builder-text-secondary);
    font-weight: 500;
}

.add-btn:hover {
    color: var(--builder-accent);
}

.template-btn {
    width: 100%;
}

.empty-state {
    padding: 12px 0;
    color: var(--builder-text-muted);
    font-size: 13px;
    font-style: italic;
}
</style>

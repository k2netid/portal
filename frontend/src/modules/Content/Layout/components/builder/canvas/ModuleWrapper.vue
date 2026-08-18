<template>
  <div 
    class="module-wrapper"
    ref="wrapperRef"
    :class="wrapperClasses"
    :style="[wrapperStyles, animationStyles]"
    @click.stop="selectModule"
    @mouseenter="hoverModule"
    @mouseleave="unhoverModule"
    @contextmenu.stop.prevent="handleContextMenu"
  >
    <!-- Inline Toolbar -->
    <div v-if="shouldShowToolbar" class="module-toolbar" :class="`module-toolbar--${moduleType}`" :style="{ zIndex: isSelected ? 150 : 100 }">
      <ModuleActions 
        :label="moduleTitle"
        :show-edit="false"
        :show-layout="isRow"
        :show-duplicate="true"
        :show-delete="true"
        :show-drag="true"
        :show-more="false"
        @layout="openRowLayoutModal"
        @duplicate="duplicateModule"
        @delete="deleteModule"
      />
    </div>

    <!-- Loop Indicator Badge -->
    <div v-if="isLoopEnabled" class="loop-indicator" title="Loop Enabled">
        <component :is="icons.Repeat" :size="10" />
        <span>LOOP</span>
    </div>

    <!-- Module Content with Children -->
    <div class="module-content" :class="[{ 'is-looping': isLoopEnabled }, ...animationClasses]" :style="animationStyles">
      <template v-if="!isVirtualized">
        <!-- Wireframe Mode: Hide actual renderer content for specific modules -->
        <template v-if="wireframeMode && !hasChildren">
          <div class="wireframe-placeholder">
            {{ moduleTitle }}
          </div>
        </template>

        <template v-else v-for="instance in loopInstances" :key="instance.id">
          <ModuleRenderer 
            :module="module"
            :class="{ 'loop-ghost': instance.isGhost }"
            :style="instance.isGhost ? { opacity: 0.6, pointerEvents: 'none' } : {}"
          >
            <!-- Pass children to the block component via slot -->
            <template v-if="hasChildren && !instance.isGhost">
              <draggable
                v-model="childrenModel"
                item-key="id"
                :group="childType"
                class="children-container"
                ghost-class="ja-builder-ghost"
                drag-class="drag-module"
              >
                <template #item="{ element: child, index: idx }">
                  <ModuleWrapper
                    :module="child"
                    :index="idx"
                    :is-ghost="isGhost"
                  />
                </template>
              </draggable>
            </template>
            
            <!-- Static rendering for ghosts to avoid double-binding draggables -->
            <template v-else-if="hasChildren && instance.isGhost">
              <div class="children-container loop-ghost-children">
                <ModuleWrapper
                  v-for="(child, idx) in (module.children || [])"
                  :key="child.id + '-' + instance.id"
                  :module="child"
                  :index="idx"
                  :is-ghost="true"
                />
              </div>
            </template>
          </ModuleRenderer>
        </template>
      </template>
      
      <!-- Virtualized Placeholder -->
      <div v-else class="virtual-placeholder" :style="{ height: lastHeight + 'px' }">
        <div class="virtual-placeholder-inner">
           <span>{{ moduleTitle }}</span>
        </div>
      </div>
    </div>
    
    <!-- Module Add Button UX -->
    <!-- 1. Sibling Section Button -->
    <AddModuleButton 
       v-if="isSection && (isSelected || isHovered) && !isGhost"
       type="section"
       :floating="true"
       @click="addSiblingSection"
    />

    <!-- 2. Sibling Row Button -->
    <AddModuleButton 
       v-if="isRow && (isSelected || isHovered) && !isGhost"
       type="row"
       :floating="true"
       @click="addSiblingRow"
    />

    <!-- 3. Module Add Button (Inside/Below) -->
    <template v-if="(isColumn || isContent) && (isSelected || isHovered) && !isGhost">
        <!-- Center Button for Empty Column (Matching Module Style) -->
        <div v-if="isColumn && !hasChildrenContent" class="module-center-add">
            <AddModuleButton 
                type="module"
                :circular="true"
                @click="handlePlusClick"
            />
        </div>
        
        <!-- Floating Bottom Button for Populated Column or Module -->
        <AddModuleButton 
            v-else
            :type="isColumn ? 'column' : 'module'"
            :floating="true"
            :circular="true"
            @click="handlePlusClick"
        />
    </template>
    
    <!-- Module Label (Grid/Wireframe) -->
    <div v-if="gridViewMode || wireframeMode" class="module-label">
      {{ moduleTitle }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, ref, onMounted, onUnmounted } from 'vue'
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Repeat from 'lucide-vue-next/dist/esm/icons/repeat.js';
import draggable from 'vuedraggable'
import ModuleRegistry from '@/components/builder/core/ModuleRegistry'
import ModuleRenderer from './ModuleRenderer.vue'
import AddModuleButton from './AddModuleButton.vue'
import ModuleActions from '../fields/ModuleActions.vue'
import { useI18n } from 'vue-i18n'
import { getAnimationStyles, getResponsiveValue } from '@/shared/utils/styleUtils'
import type { BlockInstance, BuilderInstance } from '@/types/builder'

const icons = { Plus, Repeat }

// Props
interface Props {
  module: BlockInstance;
  index?: number;
  isGhost?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  index: 0,
  isGhost: false
})

// Inject
const builder = inject<BuilderInstance>('builder')
const openContextMenu = inject<(id: string, e: MouseEvent, title?: string, type?: string) => void>('openContextMenu')
const { t } = useI18n()

// Virtualization State
const isVirtualized = ref(false)
const lastHeight = ref(100)
const wrapperRef = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

onMounted(() => {
    if (props.isGhost) return 

    observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                isVirtualized.value = false
            } else {
                if (wrapperRef.value) {
                    lastHeight.value = wrapperRef.value.offsetHeight
                }
                isVirtualized.value = true
            }
        })
    }, {
        rootMargin: '400px',
        threshold: 0
    })

    if (wrapperRef.value) {
        observer.observe(wrapperRef.value)
    }
})

onUnmounted(() => {
    if (observer) {
        observer.disconnect()
    }
})

const currentDevice = computed(() => builder?.device.value || 'desktop')

const animationClasses = computed(() => {
    const anim = getResponsiveValue(props.module.settings, 'animation', currentDevice.value) as { effect?: string } | undefined
    const effect = (anim && anim.effect) ? anim.effect : (getResponsiveValue(props.module.settings, 'animation_effect', currentDevice.value) as string | undefined)
    return effect ? [effect] : []
})

const animationStyles = computed(() => {
    return getAnimationStyles(props.module.settings, currentDevice.value)
})

// Computed
const childrenModel = computed({
  get: () => props.module.children || [],
  set: (val) => {
    if (builder) {
      builder.updateModule(props.module.id, { children: val })
    }
  }
})

const isSelected = computed(() => 
  !props.isGhost && builder?.selectedModuleId.value === props.module.id
)

const isHovered = computed(() => 
  !props.isGhost && builder?.hoveredModuleId.value === props.module.id
)

const shouldShowToolbar = computed(() => {
  if (props.isGhost) return false
  if (isSelected.value) return true
  if (builder?.selectedModuleId.value && builder.selectedModuleId.value !== props.module.id) return false
  return isHovered.value
})

const wireframeMode = computed(() => 
  builder?.wireframeMode.value || false
)

const gridViewMode = computed(() => 
  builder?.gridViewMode.value || false
)

const moduleDefinition = computed(() => 
  ModuleRegistry.get(props.module.type)
)

const moduleTitle = computed(() => 
  moduleDefinition.value?.title || props.module.type
)

const moduleType = computed(() => props.module.type)

const isSection = computed(() => moduleType.value === 'section')
const isRow = computed(() => moduleType.value === 'row')
const isColumn = computed(() => moduleType.value === 'column')
const isContent = computed(() => !isSection.value && !isRow.value && !isColumn.value)

const hasChildren = computed(() => 
  Array.isArray(props.module.children)
)

const hasChildrenContent = computed(() => 
  props.module.children && props.module.children.length > 0
)

const canAddChildren = computed(() => {
  const def = moduleDefinition.value
  return !!def?.children
})

const childType = computed(() => {
  const def = moduleDefinition.value
  if (def?.children) {
      const type = Array.isArray(def.children) ? def.children[0] : def.children
      if (type === '*') return 'module'
      return type
  }
  if (isSection.value) return 'row'
  if (isRow.value) return 'column'
  if (isColumn.value) return 'module'
  return null
})

const wrapperClasses = computed(() => ({
  'module-wrapper--selected': isSelected.value,
  'module-wrapper--hovered': isHovered.value && !isSelected.value,
  [`module-wrapper--${moduleType.value}`]: true,
  'module-wrapper--grid': gridViewMode.value,
  'module-wrapper--wireframe': wireframeMode.value,
  'module-wrapper--content': !isSection.value && !isRow.value && !isColumn.value,
  'module-wrapper--loop': isLoopEnabled.value
}))

const wrapperStyles = computed(() => {
    const styles: Record<string, string | number> = {}
    
    if (isColumn.value) {
        const width = getResponsiveValue(props.module.settings, 'width', currentDevice.value) as string | undefined
        const flexGrow = getResponsiveValue(props.module.settings, 'flexGrow', currentDevice.value) as number | undefined
        
        styles.height = '100%' 
        
        if (width) {
            styles.flex = `0 0 ${width}`
            styles.maxWidth = width
        } else if (flexGrow) {
           styles.flex = `${flexGrow} 1 0%`
        } else {
           styles.flex = '1 1 0%' 
           styles.minWidth = '50px'
        }
    }

    return styles
})

const isLoopEnabled = computed(() => props.module.settings?.loop_enable === true)

const loopInstances = computed(() => {
  if (!isLoopEnabled.value) return [{ id: 'single', isGhost: false }]
  const count = parseInt(props.module.settings.posts_per_page as string) || 3
  const displayCount = Math.min(Math.max(count, 1), 6)
  return Array.from({ length: displayCount }, (_, i) => ({
    id: `loop-${i}`,
    isGhost: i > 0
  }))
})

// Methods
const selectModule = () => {
  builder?.selectModule(props.module.id)
}

const hoverModule = () => {
  builder?.hoverModule(props.module.id)
}

const unhoverModule = () => {
  builder?.hoverModule(null)
}

const handleContextMenu = (e: MouseEvent) => {
    if (openContextMenu) {
        openContextMenu(props.module.id, e, moduleTitle.value, props.module.type)
    } else if (builder?.openContextMenu) {
        builder.openContextMenu(props.module.id, e, moduleTitle.value, props.module.type)
    }
}

const handlePlusClick = () => {
    if (canAddChildren.value) {
        addChild()
    } else {
        addSibling()
    }
}

const addChild = () => {
  const type = childType.value
  if (!type) return

  if (type === 'row') {
    builder?.openInsertRowModal?.(props.module.id)
  } else if (type === 'module') {
    builder?.openInsertModal?.(props.module.id)
  } else {
    builder?.insertModule?.(type, props.module.id)
  }
}

const addSibling = () => {
    const parent = builder?.findParentById?.(builder.blocks.value, props.module.id)
    const parentId = parent ? parent.id : null
    builder?.openInsertModal?.(parentId, props.index + 1)
}

const addSiblingSection = () => {
    builder?.openInsertSectionModal?.(props.index + 1)
}

const addSiblingRow = () => {
    const parentSectionId = findParentId(props.module.id)
    if (parentSectionId) {
        builder?.openInsertRowModal?.(parentSectionId)
    }
}

const openRowLayoutModal = () => {
    builder?.openUpdateRowModal?.(props.module.id)
}

const findParentId = (moduleId: string): string | null => {
    const blocks = builder?.blocks.value || []
    for (const section of blocks) {
        if (section.children) {
            for (const row of section.children) {
                if (row.id === moduleId) return section.id
            }
        }
    }
    return null
}

const duplicateModule = () => {
  builder?.duplicateModule(props.module.id)
}

const deleteModule = async () => {
  const confirmed = await builder?.confirm({
    title: t('builder.modals.confirm.deleteModule'),
    message: t('builder.modals.confirm.deleteModuleDesc'),
    confirmText: t('builder.modals.confirm.delete'),
    cancelText: t('builder.modals.confirm.cancel'),
    type: 'delete'
  })
  if (confirmed) {
    builder?.removeModule(props.module.id)
  }
}
</script>

<style scoped>
.module-wrapper {
  position: relative;
  overflow: visible !important;
}

.module-wrapper--content {
    margin-bottom: var(--spacing-md);
    width: 100% !important;
}

.module-add-container {
    position: absolute;
    bottom: -16px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
    z-index: 100;
    pointer-events: none;
}

.module-add-container > * {
    pointer-events: auto;
}

.module-wrapper--column > .module-add-container {
    bottom: -15px; /* Match section/row floating position */
}

.module-center-add {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    z-index: 50;
}

.module-wrapper--grid,
.module-wrapper--wireframe {
  border: 1px solid transparent;
  border-radius: var(--border-radius-sm);
  padding: 0;
}

.module-wrapper--grid.module-wrapper--section,
.module-wrapper--wireframe.module-wrapper--section {
  border: 2px solid transparent;
  margin-bottom: var(--spacing-xl);
  padding: 0;
}

.module-wrapper--grid.module-wrapper--section:hover,
.module-wrapper--grid.module-wrapper--section.is-selected {
  border-color: var(--builder-section);
}

.module-wrapper--grid.module-wrapper--row,
.module-wrapper--wireframe.module-wrapper--row {
  border: 2px solid transparent;
  margin: 0 auto var(--spacing-lg) auto;
  width: 100%;
  padding: 0;
}

.module-wrapper--grid.module-wrapper--row:hover,
.module-wrapper--grid.module-wrapper--row.is-selected {
  border-color: var(--builder-row);
}

.module-wrapper--grid.module-wrapper--column,
.module-wrapper--wireframe.module-wrapper--column {
  border: 2px solid transparent;
  min-height: 50px;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.module-wrapper--grid.module-wrapper--column:hover,
.module-wrapper--grid.module-wrapper--column.is-selected {
  border-color: var(--builder-column);
}

.module-wrapper--grid.module-wrapper--content,
.module-wrapper--wireframe.module-wrapper--content {
  border: 1px dashed transparent;
  width: 100%; 
  display: flex;
  flex-direction: column;
}

.module-wrapper--grid.module-wrapper--content:hover,
.module-wrapper--grid.module-wrapper--content.is-selected {
  border-color: var(--builder-module);
  border-style: solid;
}

.module-wrapper--wireframe.module-wrapper--text,
.module-wrapper--wireframe.module-wrapper--image,
.module-wrapper--wireframe.module-wrapper--button {
    background: #f1f5f9;
    border: 2px dashed var(--builder-module);
}

.module-wrapper--selected.module-wrapper--section {
  outline: 2px solid var(--builder-section);
  outline-offset: -2px;
  z-index: 5;
}
.module-wrapper--selected.module-wrapper--row {
  outline: 2px solid var(--builder-row);
  outline-offset: -2px;
  z-index: 5;
}
.module-wrapper--selected.module-wrapper--column {
  outline: 2px solid var(--builder-column);
  outline-offset: -2px;
  z-index: 5;
}

.module-wrapper--hovered.module-wrapper--section {
  outline: 1px solid var(--builder-section);
  outline-offset: -1px;
  z-index: 4;
}
.module-wrapper--hovered.module-wrapper--row {
  outline: 1px solid var(--builder-row);
  outline-offset: -1px;
  z-index: 4;
}
.module-wrapper--hovered.module-wrapper--column {
  outline: 1px solid var(--builder-column);
  outline-offset: -1px;
  z-index: 4;
}
.module-wrapper--hovered.module-wrapper--content {
  outline: 1px solid var(--builder-module);
  outline-offset: -1px;
  z-index: 6;
}

.module-wrapper--selected.module-wrapper--content {
  outline: 2px solid var(--builder-module);
  outline-offset: -2px;
  z-index: 6;
}

.module-toolbar {
  position: absolute;
  display: flex;
  gap: 2px;
  padding: 4px;
  background: var(--builder-toolbar-bg-module);
  z-index: 110;
}

.module-wrapper--section > .module-toolbar {
    top: 0;
    left: 0;
    right: auto;
    background: var(--builder-toolbar-bg-section);
    border-bottom-right-radius: var(--border-radius-sm);
}

.module-wrapper--row > .module-toolbar {
    top: 0;
    left: 0;
    right: auto;
    background: var(--builder-toolbar-bg-row);
    border-bottom-right-radius: var(--border-radius-sm);
}

.module-toolbar--column {
    top: 0;
    left: 0;
    right: auto;
    background: var(--builder-toolbar-bg-column);
    border-bottom-right-radius: var(--border-radius-sm);
    border-bottom-left-radius: 0;
}

.module-wrapper--content > .module-toolbar {
    top: 0;
    left: auto;
    right: 0;
    background: var(--builder-toolbar-bg-module);
    border-bottom-left-radius: var(--border-radius-sm);
}

:deep(.module-actions .action-icon) {
    color: white !important;
    opacity: 0.9;
}

:deep(.module-actions .action-icon:hover) {
    background: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
    opacity: 1;
}

:deep(.module-actions .delete-btn:hover) {
    background: var(--builder-error) !important;
    color: white !important;
}

.module-content {
  min-height: 40px;
}

.module-wrapper--section > .module-content,
.module-wrapper--row > .module-content,
.module-wrapper--column > .module-content,
.module-wrapper--content > .module-content {
    height: 100%;
    width: 100%;
}

.module-label {
  position: absolute;
  top: 0;
  left: 0;
  padding: 2px 6px;
  background: var(--builder-module);
  border-bottom-right-radius: 4px;
  color: white;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  z-index: 105;
  pointer-events: none;
  transition: all 0.2s ease;
}

.module-wrapper--section.module-wrapper--selected > .module-label,
.module-wrapper--section.module-wrapper--hovered > .module-label {
    left: auto;
    right: 0;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 4px;
}

.module-wrapper--row.module-wrapper--selected > .module-label,
.module-wrapper--row.module-wrapper--hovered > .module-label,
.module-wrapper--column.module-wrapper--selected > .module-label,
.module-wrapper--column.module-wrapper--hovered > .module-label,
.module-wrapper--content.module-wrapper--selected > .module-label,
.module-wrapper--content.module-wrapper--hovered > .module-label {
    left: 0;
    right: auto;
}

.module-wrapper--grid.module-wrapper--section > .module-label,
.module-wrapper--wireframe.module-wrapper--section > .module-label {
  background: var(--builder-section);
}

.module-wrapper--grid.module-wrapper--row > .module-label,
.module-wrapper--wireframe.module-wrapper--row > .module-label {
  background: var(--builder-row);
}

.module-wrapper--grid.module-wrapper--column > .module-label,
.module-wrapper--wireframe.module-wrapper--column > .module-label {
  background: var(--builder-column);
  color: white;
  z-index: 11;
}

.loop-indicator {
    position: absolute;
    top: -18px;
    right: 0;
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 2px 6px;
    background: var(--builder-accent);
    color: white;
    font-size: 9px;
    font-weight: 700;
    border-radius: 4px 4px 0 0;
    z-index: 10;
    pointer-events: none;
    letter-spacing: 0.5px;
}

.is-looping {
    display: flex;
    flex-direction: column;
}

.wireframe-placeholder {
    color: var(--builder-text-secondary);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    text-align: center;
    width: 100%;
}
.virtual-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--builder-bg-tertiary);
    border: 1px dashed var(--builder-border);
    border-radius: var(--border-radius-sm);
}

.virtual-placeholder-inner {
    color: var(--builder-text-muted);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}
</style>

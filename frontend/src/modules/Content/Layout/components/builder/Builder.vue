<template>
  <Teleport to="body" :disabled="!builder.isFullscreen.value">
    <div 
      class="ja-builder ja-builder-main" 
      :class="[
        isDarkMode ? 'ja-builder--dark' : 'ja-builder--light',
        { 
          'ja-builder--fullscreen': builder.isFullscreen.value,
          'ja-builder--wireframe': builder.wireframeMode.value
        }
      ]"
    >
      <!-- Top Toolbar -->
      <TopToolbar 
        :active-panel="activePanel"
        @toggle-sidebar="toggleSidebar"
        @change-device="changeDevice"
        @open-pages="showInsertSectionModal = true"
        @close-builder="handleClose"
        @save="handleSave"
      />
      
      <!-- Main Content Area -->
      <div class="ja-builder__main">
        <!-- Left Sidebar (Always Visible) -->
        <LeftSidebar 
          :active-panel="activePanel || undefined"
          @change-panel="togglePanel"
        />
        
        <!-- Left Panel Drawer -->
        <LeftPanel
          v-if="sidebarVisible"
          :active-panel="activePanel!"
          :visible="!!activePanel"
          @close="activePanel = null"
        />
        
        <!-- Canvas Area -->
        <div 
          class="ja-builder__canvas-area"
          ref="canvasAreaRef"
        >
          <CanvasControls 
            @save="handleSave"
          />
          <CanvasFrame 
            :device="builder.device.value" 
            :zoom="builder.zoom.value" 
            :width="builder.customViewportWidth.value"
          >
            <Canvas />
          </CanvasFrame>
        </div>
        
        <!-- Right Panel (Settings) -->
        <RightPanel 
          v-if="selectedModule || activePanel === 'theme'"
          :module="selectedModule"
          @close="closeSettings"
        />
      </div>

      <!-- Modals -->
      <InsertModuleModal 
        v-if="showInsertModal"
        @close="showInsertModal = false"
        @insert="handleModuleInsert"
      />
      <InsertRowModal
        v-if="showInsertRowModal"
        :mode="insertRowMode"
        @close="showInsertRowModal = false"
        @insert="(_type, payload) => insertRow(payload as Record<string, unknown>)"
        @update="(payload) => updateRow(payload as any)"
      />
      <InsertSectionModal
        v-if="showInsertSectionModal"
        :target-index="insertSectionIndex"
        @close="showInsertSectionModal = false"
        @inserted="handleSectionInserted"
      />
      <StructureTemplateModal
        v-if="showStructureTemplateModal"
        :target-type="(structureTemplateTargetType as string)"
        @close="showStructureTemplateModal = false"
        @insert="(payload) => handleStructureTemplateInsert(payload as Record<string, unknown>)"
      />
      <ResponsiveFieldModal
        v-if="builder.responsiveModal.value && builder.responsiveModal.value.baseKey"
        v-bind="builder.responsiveModal.value"
        @close="builder.closeResponsiveModal"
        @update="handleResponsiveUpdate"
      />
      <IconPickerModal
        v-if="showIconPickerModal"
        :value="iconPickerValue"
        :on-select="handleIconSelect"
        @close="showIconPickerModal = false"
      />

      <!-- Canvas Modals -->
      <AddCanvasModal
        v-if="showAddCanvasModal"
        @close="showAddCanvasModal = false"
        @add="handleAddCanvas"
      />
      <ImportExportModal
        v-if="showImportExportModal"
        @close="showImportExportModal = false"
        @export="handleExportCanvas"
        @import="handleImportCanvas"
      />
      <CanvasSettingsModal
        v-if="showCanvasSettingsModal && activeCanvasData"
        :canvas="activeCanvasData"
        @close="showCanvasSettingsModal = false"
        @save="handleSaveCanvasSettings"
      />

      <SavePresetModal
        v-if="builder.savePresetModal.value.visible"
        :loading="builder.savePresetModal.value.loading"
        @close="builder.closeSavePresetModal"
        @save="builder.handleSavePreset"
      />

      <ConfirmModal
        v-if="builder.confirmModal.value.visible"
        :is-open="builder.confirmModal.value.visible"
        :title="builder.confirmModal.value.title"
        :message="builder.confirmModal.value.message"
        :confirm-text="builder.confirmModal.value.confirmText"
        :cancel-text="builder.confirmModal.value.cancelText"
        :type="builder.confirmModal.value.type"
        @confirm="builder.closeConfirmModal(true)"
        @cancel="builder.closeConfirmModal(false)"
      />

      <InputModal
        v-if="builder.inputModal.value.visible"
        :is-open="builder.inputModal.value.visible"
        :title="builder.inputModal.value.title"
        :message="builder.inputModal.value.message"
        :placeholder="builder.inputModal.value.placeholder"
        :initial-value="builder.inputModal.value.initialValue"
        :confirm-text="builder.inputModal.value.confirmText"
        :cancel-text="builder.inputModal.value.cancelText"
        @confirm="builder.closeInputModal"
        @cancel="builder.closeInputModal(null)"
      />
      
      <ContextMenu 
          :visible="contextMenu.visible"
          :x="contextMenu.x"
          :y="contextMenu.y"
          :module-id="contextMenu.moduleId || undefined"
          :title="contextMenu.title"
          :type="contextMenu.type"
          :mode="contextMenu.mode"
          @close="closeContextMenu"
          @action="handleContextMenuAction"
      />
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, reactive, computed, provide, watch, onMounted, onUnmounted } from 'vue'


// Layout Components
import TopToolbar from './layout/TopToolbar.vue'
import LeftSidebar from './layout/LeftSidebar.vue'
import LeftPanel from './layout/LeftPanel.vue'
import RightPanel from './layout/RightPanel.vue'
import CanvasFrame from './layout/CanvasFrame.vue'

// Canvas Components
import Canvas from './canvas/Canvas.vue'
import CanvasControls from './canvas/CanvasControls.vue'

// Modal Components
import InsertModuleModal from './modals/InsertModuleModal.vue'
import InsertRowModal from './modals/InsertRowModal.vue'
import InsertSectionModal from './modals/InsertSectionModal.vue'
import StructureTemplateModal from './modals/StructureTemplateModal.vue'
import ResponsiveFieldModal from './modals/ResponsiveFieldModal.vue'
import AddCanvasModal from './modals/AddCanvasModal.vue'
import ImportExportModal from './modals/ImportExportModal.vue'
import CanvasSettingsModal from './modals/CanvasSettingsModal.vue'
import SavePresetModal from './modals/SavePresetModal.vue'
import IconPickerModal from './modals/IconPickerModal.vue'
import ConfirmModal from './modals/ConfirmModal.vue'
import InputModal from './modals/InputModal.vue'
import ContextMenu from './ui/ContextMenu.vue'

// Core
import { useBuilder } from './core'
import ModuleRegistry from './core/ModuleRegistry'
import { useDarkMode } from '@/shared/composables/useDarkMode'
import { useCmsStore } from '@/stores/cms'
const { isDark: isDarkMode } = useDarkMode()
import { throttle, debounce } from '@/shared/utils/performance'
import type { BlockInstance, BuilderInstance, Canvas as ICanvas, BuilderPreset } from '@/shared/types/builder'

// Register Module Definitions (side-effect import)
import './modules'

// Global Builder Styles
import './styles/builder.css'

// Register all Block Components
import { registerBlockComponents } from './core/registerBlocks'
registerBlockComponents()

// Props
interface Props {
  initialData?: { blocks: BlockInstance[] };
  modelValue?: BlockInstance[];
  contentId?: string | number | null;
  mode?: 'site' | 'page';
}

const props = withDefaults(defineProps<Props>(), {
  initialData: () => ({ blocks: [] }),
  modelValue: () => [],
  contentId: null,
  mode: 'site'
})

// Emits
const emit = defineEmits<{
  (e: 'update', payload: { blocks: BlockInstance[] }): void;
  (e: 'save', status: string | null): void;
  (e: 'update:modelValue', blocks: BlockInstance[]): void;
  (e: 'close'): void;
  (e: 'update:fullscreen', val: boolean): void;
  (e: 'update:autoSave', val: boolean): void;
}>()

// Initialize builder state
const builderInitialData = computed(() => {
  if (props.modelValue) {
    return { blocks: props.modelValue }
  }
  return props.initialData
})

const builderBase = useBuilder(builderInitialData.value, {
  mode: props.mode
})

// Expose builder instance
defineExpose({ builder: builderBase })

const globalAction = ref<string | null>(null)
const cmsStore = useCmsStore()

// Local theme handling is now done via classes on the root .ja-builder div in the template
// and scoped variables in builder.css. No more global body pollution.

// Correctly provide dark mode for children
const darkMode = computed(() => cmsStore.isDarkMode)
provide('darkMode', darkMode)

const sidebarVisible = ref(true)
const activePanel = ref<string | null>(props.mode === 'site' ? 'pages' : 'layers')

const showInsertModal = ref(false)
const insertTargetId = ref<string | null>(null)
const insertTargetIndex = ref(-1)

const showInsertRowModal = ref(false)
const insertRowTargetId = ref<string | null>(null)

const showInsertSectionModal = ref(false)
const insertSectionIndex = ref(-1)

const showStructureTemplateModal = ref(false)
const structureTemplateTargetId = ref<string | null>(null)
const structureTemplateTargetType = ref<string | null>(null)

const builder = {
  ...(builderBase as unknown as BuilderInstance),
  darkMode,
  sidebarVisible,
  activePanel,
  globalAction,
  insertTargetId,
  insertTargetIndex
} as BuilderInstance

// Provide builder for child components
provide('builder', builder)

watch(() => builder.isFullscreen.value, (val) => {
  emit('update:fullscreen', val)
})

watch(builder.blocks, debounce((newBlocks: unknown) => {
  const blocks = newBlocks as BlockInstance[]
  emit('update', { blocks })
  emit('update:modelValue', blocks)
}, 500), { deep: true })

watch(() => builder.autoSave.value, (val) => {
  emit('update:autoSave', val)
}, { immediate: true })

watch(() => props.modelValue, (newBlocks) => {
  // Only sync if the reference actually changed and it's not the same as what we have
  // (Using a simple check instead of expensive JSON.stringify on every prop update)
  if (newBlocks && newBlocks !== builder.blocks.value) {
    builder.blocks.value = newBlocks
  }
}, { deep: false }) // deep: false since we only care about top-level array reference changes from parent

const selectedModule = computed(() => builder.selectedModule.value)

watch(selectedModule, (newVal) => {
  if (newVal && window.innerWidth <= 768) {
    activePanel.value = null
  }
})

// Methods
const toggleSidebar = () => {
  sidebarVisible.value = !sidebarVisible.value
}

const changeDevice = (newDevice: string) => {
  builder.setDeviceMode(newDevice as 'desktop' | 'tablet' | 'mobile')
}

const togglePanel = (panel: string) => {
  if (activePanel.value === panel) {
    activePanel.value = null
  } else {
    activePanel.value = panel
  }
}

const closeSettings = () => {
  builder.clearSelection()
}

const openInsertModal = (targetId: string | null, index = -1) => {
  insertTargetId.value = targetId
  insertTargetIndex.value = index
  showInsertModal.value = true
}

const insertRowMode = ref<'insert' | 'edit'>('insert')

const openInsertRowModal = (targetId: string | null) => {
  insertRowTargetId.value = targetId
  insertRowMode.value = 'insert'
  showInsertRowModal.value = true
}

const openUpdateRowModal = (rowId: string | null) => {
  insertRowTargetId.value = rowId
  insertRowMode.value = 'edit'
  showInsertRowModal.value = true
}

const openInsertSectionModal = (index = -1) => {
  if (window.innerWidth <= 768) {
      sidebarVisible.value = false
  }
  insertSectionIndex.value = index
  showInsertSectionModal.value = true
}

const openStructureTemplateModal = (targetId: string | null, targetType: string | null) => {
  structureTemplateTargetId.value = targetId
  structureTemplateTargetType.value = targetType
  showStructureTemplateModal.value = true
}

const insertModule = (type: string) => {
  builder.insertModule(type, insertTargetId.value, insertTargetIndex.value)
  showInsertModal.value = false
}

const handleModuleInsert = (type: string, payload: unknown) => {
  if (type === 'row') {
    insertRowTargetId.value = insertTargetId.value
    insertRow(payload as Record<string, unknown>)
    return
  }
  if (type === 'preset') {
    builder.insertFromPreset(payload as BuilderPreset, insertTargetId.value, insertTargetIndex.value)
    showInsertModal.value = false
    return
  }
  insertModule(type)
}

const handleStructureTemplateInsert = (payload: Record<string, unknown>) => {
    insertRow(payload)
    showStructureTemplateModal.value = false
}

const insertRow = (layout: Record<string, unknown>) => {
  
  const createSingleRow = (config: Record<string, unknown>, parentId = insertRowTargetId.value) => {
    // Insert the row using builder (handles selection and basic insertion into tree)
    const row = builder.insertModule('row', parentId);
    
    if (row) {
      // Ensure children is initialized
      if (!row.children) row.children = [];

      if (config.cols) {
        // Complex nested layout
        (config.cols as { width: number, rows?: Record<string, unknown>[] }[]).forEach((colConfig) => {
          const col = ModuleRegistry.createInstance('column');
          if (col) {
             col.settings = { ...col.settings, flexGrow: colConfig.width };
             row.children!.push(col);

            if (colConfig.rows) {
              // Now that col is in the tree, we can insert rows into it
              colConfig.rows.forEach((nestedRowConfig: Record<string, unknown>) => {
                createSingleRow(nestedRowConfig, col.id)
              })
            }
          }
        })
      } else {
        // Standard/Flat layout
        const structure = (config.structure || config) as string | Record<string, unknown>
        const widths = (config.widths || (typeof structure === 'string' ? structure.split('-').map(() => 1) : [1])) as number[]
        
        // Calculate and set explicit column structure for RowBlock
        const totalWidth = widths.reduce((a: number, b: number) => a + b, 0)
        const columnsStr = widths.map((w: number) => totalWidth > 0 ? `${w}/${totalWidth}` : '1/1').join('-')
        
        // Update row settings directly
        row.settings = { ...row.settings, columns: columnsStr };

        // Create and append columns explicitly (skipping insertModule for children to avoid overhead/race conditions)
        widths.forEach((width: number) => {
          const col = ModuleRegistry.createInstance('column');
          if (col) {
            col.settings = { ...col.settings, flexGrow: width };
            row.children!.push(col);
          }
        })
      }
      
      // Take a final snapshot to save the fully constructed row
      builder.takeSnapshot({ immediate: true });
    }
  }

  if (layout.rows) {
      (layout.rows as Record<string, unknown>[]).forEach((rowConfig: Record<string, unknown>) => createSingleRow(rowConfig))
  } else {
      createSingleRow(layout)
  }
  showInsertRowModal.value = false
}

const updateRow = (layout: string | Record<string, unknown>) => {
    if (!insertRowTargetId.value) return
    const success = builder.updateRowLayout(insertRowTargetId.value, layout)
    if (!success) {
        toast.error.action('Could not update row layout')
    }
    showInsertRowModal.value = false
}

const handleResponsiveUpdate = (updates: Record<string, unknown>) => {
  if (builder.responsiveModal.value) {
    const moduleId = builder.responsiveModal.value.module.id
    builder.updateModuleSettings(moduleId, updates)
  }
}

const handleSectionInserted = () => {
  showInsertSectionModal.value = false
}

const handleClose = () => {
    emit('close')
}

const handleKeydown = (e: KeyboardEvent) => {
  if (['INPUT', 'TEXTAREA', 'SELECT'].includes((e.target as HTMLElement).tagName) || (e.target as HTMLElement).isContentEditable) {
    return
  }

  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'z') {
    e.preventDefault()
    if (e.shiftKey) {
      builder.redo()
    } else {
      builder.undo()
    }
  }
  
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's') {
    e.preventDefault()
    emit('save', null)
  }
}

import { useToast } from '@/composables/useToast'
import { useI18n } from 'vue-i18n'
import i18n from '@/engine/i18n'
import { ensureDeferredLocales } from '@/engine/i18n/deferredLocales'
import builderEn from '@/modules/Content/Layout/locales/builder/en.json'
import builderId from '@/modules/Content/Layout/locales/builder/id.json'
import builderSu from '@/modules/Content/Layout/locales/builder/su.json'

// Synchronous eager registration to guarantee zero leak
try {
  i18n.global.mergeLocaleMessage('en', { builder: builderEn })
  i18n.global.mergeLocaleMessage('id', { builder: builderId })
  i18n.global.mergeLocaleMessage('su', { builder: builderSu })
} catch (e) {
  // safe fallback
}

const toast = useToast()
const { t } = useI18n()

const handleDeleteModule = async (id: string) => {
    const confirmed = await builder.confirm({
        title: t('builder.modals.confirm.deleteModule'),
        message: t('builder.modals.confirm.deleteModuleDesc'),
        confirmText: t('builder.modals.confirm.delete'),
        cancelText: t('builder.modals.confirm.cancel'),
        type: 'delete'
    })
    if (confirmed) {
        builder.removeModule(id)
    }
}

const handleSave = async (status: string | null = null) => {
  emit('save', status)
  builder.markAsSaved()
}

const canvasAreaRef = ref<HTMLElement | null>(null)
let resizeObserver: ResizeObserver | null = null

onMounted(async () => {
    void ensureDeferredLocales(['content'])
    window.addEventListener('keydown', handleKeydown)
    builder.loadTheme()
    builder.fetchMetadata()
    
    if (canvasAreaRef.value) {
        const handleResize = throttle((...args: unknown[]) => {
            const entries = args[0] as ResizeObserverEntry[]
            if (builder.deviceModeType.value !== 'auto') return

            for (let entry of entries) {
                const width = entry.contentRect.width
                let newDevice: 'desktop' | 'tablet' | 'mobile' = 'desktop'
                if (width < 768) {
                    newDevice = 'mobile'
                } else if (width < 1024) {
                    newDevice = 'tablet'
                }
                if (builder.device.value !== newDevice) {
                    builder.device.value = newDevice
                }
            }
        }, 150)

        resizeObserver = new ResizeObserver(handleResize)
        resizeObserver.observe(canvasAreaRef.value)
    }

    if (props.contentId) {
      try {
        await builder.loadContent(props.contentId)
      } catch (err) {
        console.error(err)
        toast.error.load('Failed to load content')
      }
    }
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown)
    if (resizeObserver) {
        resizeObserver.disconnect()
    }
})

// Context Menu Logic
const contextMenu = reactive({
    visible: false,
    x: 0,
    y: 0,
    moduleId: null as string | null,
    title: '',
    type: '',
    mode: 'module'
})

const openContextMenu = (moduleId: string, event: MouseEvent, title = '', type = '', mode = 'module') => {
    event.preventDefault()
    contextMenu.visible = true
    contextMenu.x = event.clientX
    contextMenu.y = event.clientY
    contextMenu.moduleId = moduleId
    contextMenu.title = title
    contextMenu.type = type
    contextMenu.mode = mode
}

provide('openContextMenu', openContextMenu)

const closeContextMenu = () => {
    contextMenu.visible = false
}

// Canvas Modal State
const showAddCanvasModal = ref(false)
const showImportExportModal = ref(false)
const showCanvasSettingsModal = ref(false)
const showIconPickerModal = ref(false)
const iconPickerValue = ref('')
const onIconSelectCallback = ref<((icon: string) => void) | null>(null)
const activeCanvasForModal = ref<string | null>(null)

const getCanvasById = (id: string | null): ICanvas | null => {
    if (!id || !builder || !builder.canvases.value) return null
    return builder.canvases.value.find((c: ICanvas) => c.id === id) || null
}

const activeCanvasData = computed(() => {
    return getCanvasById(activeCanvasForModal.value)
})


const handleContextMenuAction = async (action: string, id?: string, mode = 'module') => {
    if (mode === 'canvas' && id) {
        handleCanvasAction(action, id)
        return
    }
    
    // If id is missing but required for action, return
    if (!id && action !== 'paste' && action !== 'paste-style') { // paste might happen without id? No, usually paste to something.
         // Actually context menu usually has an ID.
         // But type signature mismatch in ContextMenu.vue prop vs emit handling causes error.
         // We allow undefined and check.
         return
    }
    if (!id) return;

    switch (action) {
        case 'undo': builder.undo(); break
        case 'redo': builder.redo(); break
        case 'duplicate': builder.duplicateModule(id); break
        case 'delete': handleDeleteModule(id); break
        case 'add-element':
            insertTargetId.value = id
            insertTargetIndex.value = -1
            showInsertModal.value = true
            break
        case 'copy': builder.copyModule(id); break
        case 'paste': builder.pasteModule(id); break
        case 'copy-style': builder.copyStyles(id); break
        case 'paste-style': builder.pasteStyles(id); break
        case 'reset-styles': builder.resetModuleStyles?.(id); break
        case 'parent': {
            const parent = builder.findParentById(builder.blocks.value, id)
            if (parent) {
                builder.selectModule(parent.id)
            }
            break
        }
        case 'go-to-layer':
            activePanel.value = 'layers'
            setTimeout(() => {
                const layerEl = document.querySelector(`[data-layer-id="${id}"]`)
                if (layerEl) {
                    layerEl.scrollIntoView({ behavior: 'smooth', block: 'center' })
                    layerEl.classList.add('layer-highlight')
                    setTimeout(() => layerEl.classList.remove('layer-highlight'), 1500)
                }
            }, 100)
            break
        case 'rename': {
            const currentModule = builder.findModule(id)
            const newLabel = await builder.prompt({
                title: t('builder.contextMenu.renameLabel'),
                initialValue: currentModule?.settings?._label || '',
                confirmText: 'OK',
                cancelText: 'Cancel'
            })
            if (newLabel !== null && newLabel !== '') {
                builder.updateModuleSettings(id, { _label: newLabel })
            }
            break
        }
        case 'toggle-visibility': {
            const module = builder.findModule(id)
            if (module) {
                const isDisabled = module.settings?.disabled === true
                builder.updateModuleSettings(id, { disabled: !isDisabled })
            }
            break
        }
        case 'save-to-library':
            builder.openSavePresetModal?.(id)
            break
    }
}

const handleCanvasAction = (action: string, id: string) => {
    if (action === 'edit-canvas') {
        builder.switchCanvas(id)
    } else if (action === 'duplicate-canvas') {
        builder.duplicateCanvas(id)
    } else if (action === 'delete-canvas') {
        builder.removeCanvas(id)
    } else if (action === 'canvas-settings') {
        activeCanvasForModal.value = id
        showCanvasSettingsModal.value = true
    } else if (action === 'export-canvas') {
        activeCanvasForModal.value = id
        showImportExportModal.value = true
    } else if (action === 'make-main-canvas') {
        builder.setMainCanvas(id)
    }
}

const handleAddCanvas = (data: { title: string }) => {
    builder.addCanvas(data.title)
    showAddCanvasModal.value = false
}

const handleExportCanvas = (_data: unknown) => {
    if (activeCanvasForModal.value) {
        builder.exportCanvas(activeCanvasForModal.value)
    }
    showImportExportModal.value = false
}

const handleImportCanvas = (_data: unknown) => {
    showImportExportModal.value = false
}

const handleSaveCanvasSettings = (data: { title: string, isGlobal: boolean, append: string }) => {
    if (activeCanvasForModal.value) {
        builder.renameCanvas(activeCanvasForModal.value, data.title)
        const canvas = getCanvasById(activeCanvasForModal.value)
        if (canvas) {
            canvas.isGlobal = data.isGlobal;
            canvas.append = data.append === 'true' || data.append === '1';
        }
    }
    showCanvasSettingsModal.value = false
}

const handleIconSelect = (iconName: string) => {
    if (onIconSelectCallback.value) {
        onIconSelectCallback.value(iconName)
    }
}

builder.openIconPickerModal = (value: string, onSelect: (icon: string) => void) => {
    iconPickerValue.value = value
    onIconSelectCallback.value = onSelect
    showIconPickerModal.value = true
}

builder.openAddCanvasModal = () => { showAddCanvasModal.value = true }
builder.openImportExportModal = (id: string) => { 
    activeCanvasForModal.value = id
    showImportExportModal.value = true 
}

builder.openInsertModal = openInsertModal
builder.openInsertRowModal = openInsertRowModal
builder.openUpdateRowModal = openUpdateRowModal
builder.openInsertSectionModal = openInsertSectionModal
builder.openStructureTemplateModal = openStructureTemplateModal
builder.openContextMenu = openContextMenu
</script>

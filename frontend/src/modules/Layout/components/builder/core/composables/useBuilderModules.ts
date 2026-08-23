import { computed, triggerRef } from 'vue'
import ModuleRegistry from '../ModuleRegistry'
import ValidationService from '../ValidationService'
import type { BlockInstance, BuilderState, ModuleSettings, BuilderPreset, BlockDefinition } from '@/types/builder'
import { logger } from '@/utils/logger'

export interface HistoryManager {
    takeSnapshot: (options?: { immediate?: boolean; delay?: number }) => void;
    flushSnapshot: () => void;
    undo: () => boolean;
    redo: () => boolean;
    canUndo: import('vue').ComputedRef<boolean>;
    canRedo: import('vue').ComputedRef<boolean>;
}

export function useBuilderModules(state: BuilderState, historyManager: HistoryManager) {
    const {
        blocks,
        selectedModuleId,
        hoveredModuleId,
        device,
        clipboard,
        markAsDirty
    } = state
    const { takeSnapshot } = historyManager

    // Registry proxies
    const definitions = computed(() => {
        const defs: Record<string, BlockDefinition> = {}
        ModuleRegistry.getAll().forEach(m => {
            if (m.name) defs[m.name] = m
        })
        return defs
    })

    const moduleCategories = computed(() => {
        const cats = new Set<string>()
        ModuleRegistry.getAll().forEach(m => {
            if (m.category) cats.add(m.category)
        })
        return Array.from(cats)
    })

    // We don't have async loading state for internal registry yet
    const loadingModules = computed(() => false)

    function registerModule(definition: BlockDefinition) {
        ModuleRegistry.register(definition)
    }

    function getModuleDefinition(type: string) {
        return ModuleRegistry.get(type)
    }

    async function fetchTemplates() {
        return []
    }

    async function fetchWidgets() {
        return []
    }

    // ============================================
    // SELECTION
    // ============================================

    const selectedModule = computed(() => {
        if (!selectedModuleId.value) return null
        return findModuleById(blocks.value, selectedModuleId.value)
    })

    const modulePath = computed(() => {
        if (!selectedModuleId.value) return []
        return getModulePath(blocks.value, selectedModuleId.value)
    })

    function selectModule(id: string | null) {
        selectedModuleId.value = id
    }

    function hoverModule(id: string | null) {
        hoveredModuleId.value = id
    }

    function clearSelection() {
        selectedModuleId.value = null
    }

    // ============================================
    // MODULE CRUD
    // ============================================

    function insertModule(type: string, parentId: string | null = null, index: number = -1): BlockInstance | null {
        const instance = ModuleRegistry.createInstance(type)
        if (!instance) return null

        if (parentId) {
            const parent = findModuleById(blocks.value, parentId)
            if (parent && parent.children) {
                if (index >= 0) {
                    parent.children.splice(index, 0, instance)
                } else {
                    parent.children.push(instance)
                }
            }
        } else {
            if (index >= 0) {
                blocks.value.splice(index, 0, instance)
            } else {
                blocks.value.push(instance)
            }
        }

        takeSnapshot({ immediate: true })
        selectModule(instance.id)
        return instance
    }

    function insertFromPreset(preset: BuilderPreset, parentId: string | null = null, index: number = -1): BlockInstance | null {
        const instance = ModuleRegistry.createInstance(preset.type)
        if (!instance) return null

        instance.settings = JSON.parse(JSON.stringify({
            ...instance.settings,
            ...preset.settings
        }))

        if (parentId) {
            const parent = findModuleById(blocks.value, parentId)
            if (parent && parent.children) {
                if (index >= 0) {
                    parent.children.splice(index, 0, instance)
                } else {
                    parent.children.push(instance)
                }
            }
        } else {
            if (index >= 0) {
                blocks.value.splice(index, 0, instance)
            } else {
                blocks.value.push(instance)
            }
        }

        takeSnapshot({ immediate: true })
        selectModule(instance.id)
        return instance
    }

    function removeModule(id: string): boolean {
        const removed = removeModuleById(blocks.value, id)
        if (removed) {
            markAsDirty()
            takeSnapshot({ immediate: true })
            if (selectedModuleId.value === id) {
                clearSelection()
            }
        }
        return removed
    }

    function duplicateModule(id: string): BlockInstance | null {
        const module = findModuleById(blocks.value, id)
        if (!module) return null

        const duplicate = JSON.parse(JSON.stringify(module)) as BlockInstance
        duplicate.id = ModuleRegistry.generateId()
        regenerateIds(duplicate)

        const parent = findParentById(blocks.value, id)
        const siblings = parent ? parent.children! : blocks.value
        const index = siblings.findIndex(m => m.id === id)

        siblings.splice(index + 1, 0, duplicate)
        markAsDirty()
        takeSnapshot({ immediate: true })
        selectModule(duplicate.id)

        return duplicate
    }

    function moveModule(id: string, direction: 'up' | 'down'): boolean {
        const parent = findParentById(blocks.value, id)
        const siblings = parent ? parent.children! : blocks.value
        const index = siblings.findIndex(m => m.id === id)

        if (index < 0) return false

        const newIndex = direction === 'up' ? index - 1 : index + 1
        if (newIndex < 0 || newIndex >= siblings.length) return false

        const [module] = siblings.splice(index, 1)
        if (module) {
            siblings.splice(newIndex, 0, module)
        }
        markAsDirty()
        takeSnapshot({ immediate: true })

        return true
    }

    function updateModuleSettings(id: string, settings: ModuleSettings): boolean {
        const module = findModuleById(blocks.value, id)
        if (!module) return false

        const tentativeSettings = { ...module.settings, ...settings }
        const validation = ValidationService.validate(module.type, tentativeSettings)

        if (!validation.success) {
            logger.warning(`[Validation] Failed for ${module.type}:`, validation.errors)
            return false
        }

        module.settings = validation.data
        blocks.value = [...blocks.value]
        markAsDirty()
        takeSnapshot() // Debounced
        return true
    }

    function updateModule(id: string, data: Partial<BlockInstance>): boolean {
        const module = findModuleById(blocks.value, id)
        if (!module) return false

        let tentativeSettings = module.settings || {}
        if (data.settings) {
            tentativeSettings = { ...tentativeSettings, ...data.settings }
        }

        const validation = ValidationService.validate(module.type, tentativeSettings)
        if (!validation.success) {
            logger.warning(`[Validation] Failed for ${module.type}:`, validation.errors)
            return false
        }

        if (data.settings) {
            module.settings = validation.data
            delete data.settings
        }

        Object.assign(module, data)
        blocks.value = [...blocks.value]
        markAsDirty()
        takeSnapshot() // Debounced
        return true
    }

    function updateModuleSetting(id: string, key: string, value: unknown): boolean {
        const module = findModuleById(blocks.value, id)
        if (!module) return false

        if (!module.settings) module.settings = {}

        const current = module.settings[key]
        let newValue = value

        if (typeof current === 'object' && current !== null && !Array.isArray(current) &&
            (Object.prototype.hasOwnProperty.call(current, 'desktop') ||
                Object.prototype.hasOwnProperty.call(current, 'tablet') ||
                Object.prototype.hasOwnProperty.call(current, 'mobile'))) {
            newValue = { ...current, [device.value]: value }
        }

        const tentativeSettings = { ...module.settings, [key]: newValue }
        const validation = ValidationService.validate(module.type, tentativeSettings)

        if (!validation.success) {
            logger.warning(`[Validation] Failed for ${module.type} (field ${key}):`, validation.errors)
            return false
        }

        module.settings = validation.data
        blocks.value = [...blocks.value]
        markAsDirty()
        takeSnapshot() // Debounced
        return true
    }

    function applyPreset(id: string, preset: BuilderPreset): boolean {
        const module = findModuleById(blocks.value, id)
        if (!module || !preset.settings) return false

        module.settings = JSON.parse(JSON.stringify({
            ...module.settings,
            ...preset.settings
        }))

        triggerRef(blocks)
        markAsDirty()
        takeSnapshot({ immediate: true })
        return true
    }

    function resetModuleStyles(id: string): boolean {
        const module = findModuleById(blocks.value, id)
        if (!module) return false

        module.settings = {
            ...module.settings,
            padding: undefined,
            margin: undefined,
            backgroundColor: undefined,
            backgroundImage: undefined,
            border: undefined,
            borderRadius: undefined,
            boxShadow: undefined,
            textColor: undefined,
            typography: undefined,
            width: undefined,
            maxWidth: undefined,
            minHeight: undefined
        }

        markAsDirty()
        takeSnapshot({ immediate: true })
        return true
    }

    function updateRowLayout(rowId: string, layout: string | { widths?: number[]; structure?: string; cols?: unknown } | Record<string, unknown>): boolean {
        const row = findModuleById(blocks.value, rowId)
        if (!row || row.type !== 'row') return false

        let newWidths: number[] = []

        // Normalize layout
        const layoutObj = (typeof layout === 'string' ? { structure: layout } : layout) as { structure?: string; widths?: number[]; cols?: unknown };

        if (layoutObj.cols) {
            if (confirm('Switching to a complex nested layout will reset columns. Continue?')) {
                row.children = []
            } else {
                return false
            }
        } else {
            newWidths = layoutObj.widths || (typeof layoutObj.structure === 'string' ? layoutObj.structure.split('-').map(() => 1) : [1])
        }

        if (newWidths.length > 0) {
            const currentChildren = row.children || []
            const currentCount = currentChildren.length
            const newCount = newWidths.length

            if (newCount === currentCount) {
                currentChildren.forEach((col, index) => {
                    col.settings.flexGrow = newWidths[index]
                })
            } else if (newCount > currentCount) {
                currentChildren.forEach((col, index) => {
                    col.settings.flexGrow = newWidths[index]
                })
                for (let i = currentCount; i < newCount; i++) {
                    const col = ModuleRegistry.createInstance('column')
                    if (col) {
                        col.settings.flexGrow = newWidths[i]
                        row.children!.push(col)
                    }
                }
            } else {
                const survivingCols = currentChildren.slice(0, newCount)
                const removedCols = currentChildren.slice(newCount)

                removedCols.forEach(oldCol => {
                    if (oldCol.children && oldCol.children.length > 0) {
                        const targetCol = survivingCols[newCount - 1]
                        if (targetCol) {
                            if (!targetCol.children) targetCol.children = []
                            targetCol.children.push(...oldCol.children)
                        }
                    }
                })

                survivingCols.forEach((col, index) => {
                    col.settings.flexGrow = newWidths[index]
                })
                row.children = survivingCols
            }
        }

        markAsDirty()
        takeSnapshot({ immediate: true })
        triggerRef(blocks)
        return true
    }

    // ============================================
    // CLIPBOARD
    // ============================================

    function copyModule(id: string): boolean {
        const module = findModuleById(blocks.value, id)
        if (!module) return false
        clipboard.value = {
            type: 'module',
            data: JSON.parse(JSON.stringify(module))
        }
        return true
    }

    function pasteModule(parentId: string | null, index: number = -1): BlockInstance | null {
        if (!clipboard.value || clipboard.value.type !== 'module') return null
        const duplicate = JSON.parse(JSON.stringify(clipboard.value.data)) as BlockInstance
        duplicate.id = ModuleRegistry.generateId()
        regenerateIds(duplicate)

        if (parentId) {
            const parent = findModuleById(blocks.value, parentId)
            if (parent && parent.children) {
                if (index >= 0) parent.children.splice(index, 0, duplicate)
                else parent.children.push(duplicate)
            }
        } else {
            if (index >= 0) blocks.value.splice(index, 0, duplicate)
            else blocks.value.push(duplicate)
        }

        markAsDirty()
        takeSnapshot({ immediate: true })
        return duplicate
    }

    function copyStyles(id: string): boolean {
        const module = findModuleById(blocks.value, id)
        if (!module || !module.settings) return false
        clipboard.value = {
            type: 'styles',
            sourceType: module.type,
            data: JSON.parse(JSON.stringify(module.settings.design || {}))
        }
        return true
    }

    function pasteStyles(id: string): boolean {
        if (!clipboard.value || clipboard.value.type !== 'styles') return false
        const module = findModuleById(blocks.value, id)
        if (!module) return false
        if (!module.settings.design) module.settings.design = {}
        module.settings.design = { ...(module.settings.design as Record<string, unknown>), ...(clipboard.value.data as Record<string, unknown>) }
        triggerRef(blocks)
        markAsDirty()
        takeSnapshot({ immediate: true })
        return true
    }

    function resetLayout(): void {
        blocks.value = []
        clearSelection()
        markAsDirty()
        takeSnapshot({ immediate: true })
    }

    // ============================================
    // HELPERS
    // ============================================

    function findModuleById(items: BlockInstance[], id: string): BlockInstance | null {
        for (const item of items) {
            if (item.id === id) return item
            if (item.children) {
                const found = findModuleById(item.children, id)
                if (found) return found
            }
        }
        return null
    }

    function findParentById(items: BlockInstance[], id: string, parent: BlockInstance | null = null): BlockInstance | null {
        for (const item of items) {
            if (item.id === id) return parent
            if (item.children) {
                const found = findParentById(item.children, id, item)
                if (found !== null) return found
            }
        }
        return null
    }

    function removeModuleById(items: BlockInstance[], id: string): boolean {
        const index = items.findIndex(item => item.id === id)
        if (index >= 0) {
            items.splice(index, 1)
            return true
        }
        for (const item of items) {
            if (item.children && removeModuleById(item.children, id)) return true
        }
        return false
    }

    function getModulePath(items: BlockInstance[], id: string, path: BlockInstance[] = []): BlockInstance[] {
        for (const item of items) {
            if (item.id === id) return [...path, item]
            if (item.children) {
                const found = getModulePath(item.children, id, [...path, item])
                if (found.length > 0) return found
            }
        }
        return []
    }

    function regenerateIds(module: BlockInstance): void {
        module.id = ModuleRegistry.generateId()
        if (module.children) module.children.forEach(child => regenerateIds(child))
    }

    return {
        selectedModule,
        modulePath,
        selectModule,
        hoverModule,
        clearSelection,
        insertModule,
        insertFromPreset,
        removeModule,
        duplicateModule,
        moveModule,
        updateModuleSettings,
        updateModule,
        updateModuleSetting,
        applyPreset,
        resetModuleStyles,
        updateRowLayout,
        copyModule,
        pasteModule,
        copyStyles,
        pasteStyles,
        resetLayout,
        findModule: (id: string) => findModuleById(blocks.value, id),
        findParentById: (items: BlockInstance[], id: string) => findParentById(items, id),
        getModulePath: (items: BlockInstance[], id: string) => getModulePath(items, id),

        // Registry
        definitions,
        moduleCategories,
        loadingModules,
        registerModule,
        getModuleDefinition,
        fetchTemplates,
        fetchWidgets
    }
}


import type { BuilderState, Canvas, ResponsiveModalState, ConfirmModalState, InputModalState, ModuleManager } from '@/modules/Layout/types/builder'
import type { HistoryManager } from './useBuilderModules'

export function useBuilderUI(state: BuilderState, _historyManager: HistoryManager, moduleManager: ModuleManager) {
    const {
        canvases,
        activeCanvasId,
        device,
        deviceModeType,
        customViewportWidth,
        gridViewMode,
        responsiveModal,
        confirmModal,
        inputModal,
        savePresetModal
    } = state


    const { clearSelection } = moduleManager

    // ============================================
    // CANVAS MANAGEMENT
    // ============================================

    function addCanvas(title = 'New Canvas'): string {
        const id = `canvas-${Date.now()}`
        canvases.value.push({
            id,
            title,
            blocks: [],
            isMain: false
        })
        activeCanvasId.value = id
        clearSelection()
        return id
    }

    function removeCanvas(id: string): boolean {
        const index = canvases.value.findIndex((c: Canvas) => c.id === id)
        const target = canvases.value[index]
        if (index === -1 || !target || target.isMain) return false

        const isDeletingActive = activeCanvasId.value === id
        canvases.value.splice(index, 1)

        if (isDeletingActive) {
            const first = canvases.value[0]
            activeCanvasId.value = first ? first.id : ''
            clearSelection()
        }
        return true
    }

    function switchCanvas(id: string): void {
        const canvas = canvases.value.find((c: Canvas) => c.id === id)
        if (!canvas) return

        activeCanvasId.value = id
        gridViewMode.value = false
        clearSelection()
    }

    function renameCanvas(id: string, title: string): void {
        const canvas = canvases.value.find((c: Canvas) => c.id === id)
        if (canvas) {
            canvas.title = title
        }
    }

    function duplicateCanvas(id: string): string | null {
        const canvas = canvases.value.find((c: Canvas) => c.id === id)
        if (!canvas) return null

        const newId = `canvas-${Date.now()}`
        const newCanvas: Canvas = {
            ...JSON.parse(JSON.stringify(canvas)),
            id: newId,
            title: `${canvas.title} (Copy)`,
            isMain: false
        }
        canvases.value.push(newCanvas)
        return newId
    }

    function setMainCanvas(id: string): void {
        canvases.value.forEach((c: Canvas) => {
            c.isMain = c.id === id
        })
    }

    function exportCanvas(id: string): void {
        const canvas = canvases.value.find((c: Canvas) => c.id === id)
        if (!canvas) return

        const data = JSON.stringify(canvas, null, 2)
        const blob = new Blob([data], { type: 'application/json' })
        const url = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `${canvas.title.toLowerCase().replace(/\s+/g, '-')}.json`
        a.click()
        URL.revokeObjectURL(url)
    }

    // ============================================
    // VIEWPORT / DEVICE
    // ============================================

    function setDeviceMode(mode: 'desktop' | 'tablet' | 'mobile'): void {
        device.value = mode
        deviceModeType.value = 'manual'
        customViewportWidth.value = null
    }

    function setDeviceModeAuto(): void {
        deviceModeType.value = 'auto'
    }

    // ============================================
    // MODALS
    // ============================================

    function openResponsiveModal(config: ResponsiveModalState): void {
        responsiveModal.value = config
    }

    function closeResponsiveModal(): void {
        responsiveModal.value = null
    }

    function openSavePresetModal(moduleId: string): void {
        savePresetModal.value = {
            visible: true,
            moduleId,
            loading: false
        }
    }

    function closeSavePresetModal(): void {
        savePresetModal.value.visible = false
        savePresetModal.value.moduleId = null
    }

    function confirm(options: Partial<ConfirmModalState> = {}): Promise<boolean> {
        return new Promise((resolve) => {
            confirmModal.value = {
                visible: true,
                title: options.title || 'Confirm',
                message: options.message || 'Are you sure?',
                confirmText: options.confirmText || 'Confirm',
                cancelText: options.cancelText || 'Cancel',
                type: options.type || 'warning',
                resolve
            }
        })
    }

    function closeConfirmModal(confirmed = false): void {
        if (confirmModal.value.resolve) {
            confirmModal.value.resolve(confirmed)
        }
        confirmModal.value = {
            ...confirmModal.value,
            visible: false,
            resolve: null
        }
    }

    function prompt(options: Partial<InputModalState> = {}): Promise<string | null> {
        return new Promise((resolve) => {
            inputModal.value = {
                visible: true,
                title: options.title || 'Input',
                message: options.message || '',
                placeholder: options.placeholder || '',
                initialValue: options.initialValue || '',
                confirmText: options.confirmText || 'OK',
                cancelText: options.cancelText || 'Cancel',
                resolve
            }
        })
    }

    function closeInputModal(value: string | null = null): void {
        if (inputModal.value.resolve) {
            inputModal.value.resolve(value)
        }
        inputModal.value = {
            ...inputModal.value,
            visible: false,
            resolve: null
        }
    }

    return {
        addCanvas,
        removeCanvas,
        switchCanvas,
        renameCanvas,
        duplicateCanvas,
        setMainCanvas,
        exportCanvas,
        setDeviceMode,
        setDeviceModeAuto,
        openResponsiveModal,
        closeResponsiveModal,
        openSavePresetModal,
        closeSavePresetModal,
        confirm,
        closeConfirmModal,
        prompt,
        closeInputModal
    }
}

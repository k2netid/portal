import { computed, ref, onBeforeUnmount } from 'vue'
import type { BuilderState } from '@/modules/Layout/types/builder'

export function useBuilderHistory(state: BuilderState) {
    const { blocks, history, historyIndex, maxHistory, markAsDirty } = state
    const snapshotTimeout = ref<ReturnType<typeof setTimeout> | null>(null)

    const canUndo = computed(() => historyIndex.value > 0)
    const canRedo = computed(() => historyIndex.value < history.value.length - 1)

    /**
     * Internal function to save state to history
     */
    function saveSnapshot(force = false) {
        const snapshot = JSON.stringify(blocks.value)

        // Basic redundancy check: don't save if it's the same as the current history point
        if (!force && history.value[historyIndex.value] === snapshot) {
            return
        }

        // Remove future history if we're not at the end
        if (historyIndex.value < history.value.length - 1) {
            history.value = history.value.slice(0, historyIndex.value + 1)
        }

        // Add new snapshot
        history.value.push(snapshot)
        historyIndex.value++

        // Limit history size
        if (history.value.length > maxHistory) {
            history.value.shift()
            historyIndex.value--
        }
    }

    /**
     * Public method to take a snapshot, debounced by default
     * @param options - { immediate: boolean, delay: number }
     */
    function takeSnapshot(options: { immediate?: boolean; delay?: number } = {}) {
        const { immediate = false, delay = 500 } = options

        if (snapshotTimeout.value) {
            clearTimeout(snapshotTimeout.value)
            snapshotTimeout.value = null
        }

        if (immediate) {
            saveSnapshot()
        } else {
            snapshotTimeout.value = setTimeout(() => {
                saveSnapshot()
                snapshotTimeout.value = null
            }, delay)
        }
    }

    /**
     * Flush any pending snapshot immediately
     */
    function flushSnapshot() {
        if (snapshotTimeout.value) {
            clearTimeout(snapshotTimeout.value)
            snapshotTimeout.value = null
            saveSnapshot()
        }
    }

    function undo(): boolean {
        flushSnapshot()

        if (!canUndo.value) return false
        historyIndex.value--
        const snapshot = history.value[historyIndex.value]
        if (snapshot) {
            blocks.value = JSON.parse(snapshot)
        }
        markAsDirty()
        return true
    }

    function redo(): boolean {
        flushSnapshot()

        if (!canRedo.value) return false
        historyIndex.value++
        const snapshot = history.value[historyIndex.value]
        if (snapshot) {
            blocks.value = JSON.parse(snapshot)
        }
        markAsDirty()
        return true
    }

    onBeforeUnmount(() => {
        if (snapshotTimeout.value) clearTimeout(snapshotTimeout.value)
    })

    return {
        canUndo,
        canRedo,
        takeSnapshot,
        flushSnapshot,
        undo,
        redo
    }
}

import { logger } from '@/shared/utils/logger';
import { computed, ref } from 'vue'
import ModuleRegistry from './ModuleRegistry'
import api from '@/engine/api/client'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { usePresets } from './usePresets'
import { useGlobalVariables } from './useGlobalVariables'
import type {
    BlockInstance,
    BuilderOptions,
    BuilderInstance,
    BlockDefinition
} from '@/modules/Layout/types/builder'

// Sub-composables
import { useBuilderState } from './composables/useBuilderState'
import { useBuilderHistory } from './composables/useBuilderHistory'
import { useBuilderModules } from './composables/useBuilderModules'
import { useBuilderSync } from './composables/useBuilderSync'
import { useBuilderUI } from './composables/useBuilderUI'

export default function useBuilder(initialData = { blocks: [] as BlockInstance[] }, options: BuilderOptions = {}): BuilderInstance {
    // Initialize primary state
    const state = useBuilderState(initialData, options)

    // Initialize external composables
    const {
        presets,
        loading: loadingPresets,
        fetchPresets,
        savePreset,
        deletePreset
    } = usePresets()

    const {
        activeTheme: globalActiveTheme,
        themeSettings: globalThemeSettings,
        themeAssets: globalThemeAssets,
        applyThemeStyles
    } = useTheme()

    const globalVariables = useGlobalVariables()

    // Initialize sub-composables with dependencies
    const historyManager = useBuilderHistory(state)
    const moduleManager = useBuilderModules(state, historyManager)
    const syncManager = useBuilderSync(state, historyManager, globalVariables)
    const uiManager = useBuilderUI(state, historyManager, moduleManager)

    // ============================================
    // THEME LOADING (Specific to Builder Facade)
    // ============================================

    async function loadTheme(slug: string | null = null): Promise<void> {
        try {
            if (slug && slug !== state.activeTheme.value) {
                const targetTheme = (state.availableThemes.value || []).find((t: any) => t.slug === slug)
                const themeId = targetTheme?.id || slug
                try {
                    await api.post(`/manage/layout/themes/${themeId}/activate`)
                } catch (e) {
                    logger.error('Theme activation endpoint returned:', e instanceof Error ? e.message : String(e))
                }
            }

            const response = await api.get(`/public/layout/themes/active`)
            const data = response.data?.data || response.data

            if (data) {
                state.themeData.value = data
                state.themeSettings.value = data.settings || {}
                state.activeTheme.value = data.slug
                state.selectedThemeSlug.value = data.slug

                globalActiveTheme.value = data
                globalThemeSettings.value = data.settings || {}
                if (data.assets) {
                    globalThemeAssets.value = data.assets
                }

                if (data.settings?.global_variables) {
                    globalVariables.loadVariables(data.settings.global_variables)
                }

                applyThemeStyles()
            }
        } catch (error: unknown) {
            logger.error('Failed to load theme for builder:', error instanceof Error ? error.message : String(error));
        }
    }

    // ============================================
    // PRESET HANDLING (Integration between UI and Service)
    // ============================================

    async function handleSavePreset(name: string): Promise<void> {
        if (!state.savePresetModal.value.moduleId) return

        const module = moduleManager.findModule(state.savePresetModal.value.moduleId)
        if (!module) return

        state.savePresetModal.value.loading = true
        try {
            await savePreset(module, name)
            uiManager.closeSavePresetModal()
        } catch (error: unknown) {
            logger.error('Failed to save preset:', error instanceof Error ? error.message : String(error));
        } finally {
            state.savePresetModal.value.loading = false
        }
    }

    // ============================================
    // INITIALIZATION
    // ============================================

    historyManager.takeSnapshot()
    fetchPresets()

    if (state.mode.value === 'site') {
        syncManager.fetchPages().then(() => {
            const firstPage = state.pages.value?.[0]
            if (state.pages.value.length > 0 && !state.currentPageId.value && firstPage?.id) {
                syncManager.setCurrentPage(firstPage.id)
            }
        })
    }

    // UI state
    const activePanel = ref<string | null>(null)
    const sidebarVisible = ref(true)
    const darkMode = ref(false)


    // ============================================
    // RETURN (EXACT SAME INTERFACE)
    // ============================================

    return {
        // State from useBuilderState
        ...state,

        // History Methods
        canUndo: historyManager.canUndo,
        canRedo: historyManager.canRedo,
        takeSnapshot: historyManager.takeSnapshot,
        undo: historyManager.undo,
        redo: historyManager.redo,

        // Module Methods
        ...moduleManager,

        // Sync/API Methods
        ...syncManager,

        // UI/Canvas Methods
        ...uiManager,

        activePanel,
        sidebarVisible,
        darkMode,

        // Registry/Helper integration
        getModuleDefinition: (type: string) => ModuleRegistry.get(type),
        globalVariables,
        saveGlobalVariables: syncManager.saveGlobalVariables,
        loadTheme,
        handleSavePreset,
        updateThemeSettings: syncManager.updateThemeSettings,
        fetchTemplates: syncManager.fetchTemplates,
        createTemplate: syncManager.createTemplate,
        deleteTemplate: syncManager.deleteTemplate,
        updateContentMeta: syncManager.updateContentMeta,
        fetchThemes: syncManager.fetchThemes,

        // Modal Prompt Aliases
        confirm: uiManager.confirm,
        prompt: uiManager.prompt,
        applyThemeStyles,

        // External Composables re-exposing
        presets,
        loadingPresets,
        fetchPresets,
        savePreset,
        // Missing Props for Interface Compliance
        definitions: computed(() => {
            const defs: Record<string, BlockDefinition> = {};
            ModuleRegistry.getAll().forEach(m => { if (m.name) defs[m.name] = m });
            return defs;
        }),
        moduleCategories: computed(() => {
            const categories = new Set<string>();
            ModuleRegistry.getAll().forEach(m => {
                if (m.category) categories.add(m.category);
            });
            return Array.from(categories);
        }),
        loadingModules: ref(false),
        registerModule: (def: BlockDefinition) => ModuleRegistry.register(def),
        fetchWidgets: async () => [],
        getComponent: (type: string) => ModuleRegistry.getComponent(type),

        deletePreset
    }
}

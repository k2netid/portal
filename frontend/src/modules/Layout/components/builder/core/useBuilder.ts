import { logger } from '@/shared/utils/logger';
import { computed, ref } from 'vue'
import ModuleRegistry from './ModuleRegistry'
import api from '@/engine/api/client'
import { applyMergedSettingsSchema } from '@/modules/Layout/customizer/loaders/mergeThemeSettingsSchema'
import { usePresets } from './usePresets'
import { useGlobalVariables } from './useGlobalVariables'
import type {
    BlockInstance,
    BuilderOptions,
    BuilderInstance,
    BlockDefinition
} from '@/modules/Layout/types/builder'
import type { ThemeData } from '@/modules/Layout/types/theme'

// Sub-composables
import { useBuilderState } from './composables/useBuilderState'
import { useBuilderHistory } from './composables/useBuilderHistory'
import { useBuilderModules } from './composables/useBuilderModules'
import { useBuilderSync } from './composables/useBuilderSync'
import { useBuilderUI } from './composables/useBuilderUI'

function unwrapThemePayload(raw: unknown): ThemeData | null {
    if (!raw || typeof raw !== 'object') return null
    const root = raw as Record<string, unknown>
    const nested = root.data
    if (nested && typeof nested === 'object' && !Array.isArray(nested) && 'slug' in (nested as object)) {
        return nested as ThemeData
    }
    if ('slug' in root) {
        return root as ThemeData
    }
    return null
}

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

    const globalVariables = useGlobalVariables()

    // Initialize sub-composables with dependencies
    const historyManager = useBuilderHistory(state)
    const moduleManager = useBuilderModules(state, historyManager)
    const syncManager = useBuilderSync(state, historyManager, globalVariables)
    const uiManager = useBuilderUI(state, historyManager, moduleManager)

    // ============================================
    // THEME LOADING (builder-scoped — no public activate / global CSS)
    // ============================================

    function applyThemeToBuilderState(data: ThemeData): void {
        applyMergedSettingsSchema(data, data.slug || 'janari')
        state.themeData.value = data
        state.themeSettings.value = data.settings || {}
        state.activeTheme.value = data.slug
        state.selectedThemeSlug.value = data.slug

        if (data.settings && typeof data.settings === 'object' && data.settings.global_variables) {
            globalVariables.loadVariables(data.settings.global_variables as Parameters<typeof globalVariables.loadVariables>[0])
        }
    }

    /**
     * Load a theme into builder canvas state only.
     * Does NOT POST /activate and does NOT write document :root CSS (Canvas owns scoped styles).
     */
    async function loadTheme(slug: string | null = null): Promise<void> {
        try {
            let data: ThemeData | null = null
            if (slug) {
                const response = await api.get(`/manage/layout/themes/${slug}`)
                data = unwrapThemePayload(response.data)
            } else {
                const response = await api.get(`/public/layout/themes/active`)
                data = unwrapThemePayload(response.data)
            }

            if (data) {
                applyThemeToBuilderState(data)
            }
        } catch (error: unknown) {
            logger.error('Failed to load theme for builder:', error instanceof Error ? error.message : String(error));
        }
    }

    /**
     * Explicitly activate theme for the public site, then load it into the builder.
     */
    async function activateSiteTheme(slug: string): Promise<void> {
        const targetTheme = (state.availableThemes.value || []).find((t) => t.slug === slug)
        const themeId = targetTheme?.id || slug
        try {
            await api.post(`/manage/layout/themes/${themeId}/activate`)
        } catch (e) {
            logger.error('Theme activation endpoint returned:', e instanceof Error ? e.message : String(e))
            throw e
        }
        await loadTheme(slug)
    }

    /** No-op: canvas injectThemeStyles applies scoped CSS; never touch #theme-variables. */
    function applyThemeStyles(): void {}

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
        void syncManager.fetchPages()
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
        activateSiteTheme,
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

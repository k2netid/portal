import { ref, computed, watch } from 'vue'
import ModuleRegistry from '../ModuleRegistry'
import type {
    BlockInstance,
    Canvas,
    PageMetadata,
    BuilderOptions,
    ConfirmModalState,
    InputModalState,
    SavePresetModalState,
    ResponsiveModalState
} from '@/modules/Layout/types/builder'
import type { ThemeData, ThemeSettings } from '@/modules/Layout/types/theme'
import type { Category, Tag } from '@/modules/Publishing/types/taxonomy'
import type { Menu } from '@/modules/Layout/types/menu'

function transformHtmlToBlocks(html: string): BlockInstance[] {
    if (!html || !html.trim()) return []
    const textBlock = ModuleRegistry.createInstance('text', {
        content: html.trim()
    }) || {
        id: `module-${Date.now()}-text`,
        type: 'text',
        settings: { content: html.trim() }
    }

    const column = ModuleRegistry.createInstance('column') || {
        id: `module-${Date.now()}-col`,
        type: 'column',
        children: [],
        settings: {}
    }
    column.children = [textBlock]

    const row = ModuleRegistry.createInstance('row') || {
        id: `module-${Date.now()}-row`,
        type: 'row',
        children: [],
        settings: { columns: '1' }
    }
    row.children = [column]

    const section = ModuleRegistry.createInstance('section') || {
        id: `module-${Date.now()}-sec`,
        type: 'section',
        children: [],
        settings: {}
    }
    section.children = [row]

    return [section]
}

export function useBuilderState(initialData: { blocks?: BlockInstance[], body?: string, title?: string, slug?: string } = { blocks: [] }, options: BuilderOptions = {}) {
    const mode = ref(options.mode || 'site')

    // Initial blocks resolution: use structured blocks, or convert classic HTML body if blocks empty
    const resolvedBlocks = (initialData.blocks && initialData.blocks.length > 0)
        ? initialData.blocks
        : transformHtmlToBlocks(initialData.body || '')

    // Content State
    const canvases = ref<Canvas[]>([
        {
            id: 'canvas-1',
            title: 'Main Canvas',
            blocks: resolvedBlocks,
            isMain: true
        }
    ])
    const activeCanvasId = ref('canvas-1')

    // Current blocks proxy
    const blocks = computed({
        get: () => {
            const canvas = canvases.value.find((c: Canvas) => c.id === activeCanvasId.value)
            return canvas ? canvas.blocks : []
        },
        set: (val: BlockInstance[]) => {
            const canvas = canvases.value.find((c: Canvas) => c.id === activeCanvasId.value)
            if (canvas) {
                canvas.blocks = val
            }
        }
    })

    // Selection State
    const selectedModuleId = ref<string | null>(null)
    const hoveredModuleId = ref<string | null>(null)
    const insertTargetId = ref<string | null>(null)

    // UI State
    const activeTab = ref('content') // content | design | advanced
    const device = ref<'desktop' | 'tablet' | 'mobile'>('desktop')
    const deviceModeType = ref<'auto' | 'manual'>('manual')
    const customViewportWidth = ref<number | null>(null)
    const zoom = ref(100)
    const wireframeMode = ref(false)
    const previewMode = ref(false)
    const gridViewMode = ref(false)
    const isFullscreen = ref(false)
    const activeTheme = ref('janari')
    const activeThemePage = ref<string | null>(null)
    const selectedThemeSlug = ref<string | null>(null)
    const themeData = ref<ThemeData | null>(null)
    const themeSettings = ref<ThemeSettings>({})

    const responsiveModal = ref<ResponsiveModalState | null>(null)
    const savePresetModal = ref<SavePresetModalState>({
        visible: false,
        moduleId: null,
        loading: false
    })

    const confirmModal = ref<ConfirmModalState>({
        visible: false,
        title: 'Confirm',
        message: 'Are you sure?',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        type: 'warning',
        resolve: null
    })

    const inputModal = ref<InputModalState>({
        visible: false,
        title: 'Input',
        message: '',
        placeholder: '',
        initialValue: '',
        confirmText: 'OK',
        cancelText: 'Cancel',
        resolve: null
    })

    // Content Metadata
    const content = ref<PageMetadata>({
        id: null,
        title: initialData.title || '',
        slug: initialData.slug || '',
        excerpt: '',
        body: initialData.body || '',
        status: 'draft',
        type: options.mode === 'page' ? 'post' : 'page',
        editor_type: 'builder',
        category_id: null,
        featured_image: null,
        published_at: null,
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        og_image: null,
        comment_status: true,
        is_featured: false,
        tags: [],
        menu_item: {
            add_to_menu: false,
            menu_id: '',
            parent_id: null,
            title: ''
        }
    })

    // Preferences (scoped per builder mode so site/page prefs do not bleed)
    const PREFS_STORAGE_KEY = `ja-builder-preferences:${mode.value || 'site'}`
    const loadPreferences = () => {
        try {
            const stored = localStorage.getItem(PREFS_STORAGE_KEY)
            if (stored) return JSON.parse(stored)
            // Migrate legacy unscoped key once
            const legacy = localStorage.getItem('ja-builder-preferences')
            return legacy ? JSON.parse(legacy) : {}
        } catch { return {} }
    }
    const storedPrefs = loadPreferences()

    const showGrid = ref(storedPrefs.showGrid ?? false)
    const snapToObjects = ref(storedPrefs.snapToObjects ?? true)
    const autoSave = ref(storedPrefs.autoSave ?? true)

    watch([showGrid, snapToObjects, autoSave], () => {
        localStorage.setItem(PREFS_STORAGE_KEY, JSON.stringify({
            showGrid: showGrid.value,
            snapToObjects: snapToObjects.value,
            autoSave: autoSave.value
        }))
    })

    // History
    const history = ref<string[]>([])
    const historyIndex = ref(-1)
    const maxHistory = 50

    // Pages
    const pages = ref<PageMetadata[]>([])
    const currentPageId = ref<number | string | null>(null)
    const pagesLoading = ref(false)

    // Metadata
    const categories = ref<Category[]>([])
    const availableTags = ref<Tag[]>([])
    const menus = ref<Menu[]>([])
    const availableThemes = ref<ThemeData[]>([])
    const loadingThemes = ref(false)

    // Clipboard State
    const clipboard = ref<{ type: 'module' | 'styles', data: Record<string, unknown>, sourceType?: string } | null>(null)

    // Global Action State
    const globalAction = ref<{ type: string; payload: unknown } | null>(null)

    // Versioning & Dirty State (Optimized: No JSON.stringify on interaction)
    const dataVersion = ref(0)
    const lastSavedVersion = ref(0)

    const markAsDirty = () => {
        dataVersion.value++
    }

    const isDirty = computed(() => dataVersion.value !== lastSavedVersion.value)

    return {
        mode,
        canvases,
        activeCanvasId,
        blocks,
        selectedModuleId,
        hoveredModuleId,
        activeTab,
        device,
        deviceModeType,
        customViewportWidth,
        zoom,
        wireframeMode,
        previewMode,
        gridViewMode,
        isFullscreen,
        activeTheme,
        activeThemePage,
        selectedThemeSlug,
        themeData,
        themeSettings,
        responsiveModal,
        savePresetModal,
        confirmModal,
        inputModal,
        content,
        showGrid,
        snapToObjects,
        autoSave,
        history,
        historyIndex,
        maxHistory,
        pages,
        currentPageId,
        pagesLoading,
        categories,
        availableTags,
        menus,
        availableThemes,
        loadingThemes,
        clipboard,
        globalAction,
        dataVersion,
        lastSavedVersion,
        markAsDirty,
        isDirty,
        insertTargetId
    }
}

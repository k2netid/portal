import { logger } from '@/shared/utils/logger';
import api from '@/engine/api/client'
import { layoutPaths } from '@/engine/api/paths'
import { triggerRef } from 'vue'
import ModuleRegistry from '../ModuleRegistry'
import { BUILDER_SCHEMA_VERSION } from '../constants'
import type { BuilderState } from '@/modules/Layout/types/builder'
import type { Category, Tag } from '@/modules/Publishing/types/taxonomy'
import type { Menu } from '@/modules/Layout/types/menu'
import type { HistoryManager } from './useBuilderModules'
import type { GlobalVariablesManager } from '../useGlobalVariables'

export function useBuilderSync(state: BuilderState, historyManager: HistoryManager, globalVariables: GlobalVariablesManager) {
    const {
        blocks,
        content,
        pages,
        currentPageId,
        pagesLoading,
        categories,
        availableTags,
        menus,
        availableThemes,
        loadingThemes,
        autoSave,
        activeTheme,
        themeSettings,
        themeData,
        dataVersion,
        lastSavedVersion
    } = state

    const { takeSnapshot } = historyManager

    function markAsSaved(): void {
        lastSavedVersion.value = dataVersion.value
    }

    /** Resolve active theme UUID/slug for manage theme APIs. */
    function resolveThemeRouteKey(preferredSlug?: string): string {
        const slug = (preferredSlug || (typeof activeTheme.value === 'string' ? activeTheme.value : '') || '').trim()
        const fromThemeData = themeData.value && typeof themeData.value === 'object'
            ? (themeData.value as { id?: string; slug?: string })
            : null
        if (fromThemeData?.id && (!slug || fromThemeData.slug === slug)) {
            return String(fromThemeData.id)
        }
        const fromList = (availableThemes.value || []).find((t) => t.slug === slug || t.id === slug)
        if (fromList?.id) {
            return String(fromList.id)
        }
        if (slug) {
            return slug
        }
        throw new Error('No active theme to update')
    }

    async function fetchPages(): Promise<void> {
        pagesLoading.value = true
        try {
            const response = await api.get('/manage/publishing/contents', {
                params: { per_page: 100 }
            })
            const data = response.data?.data || response.data
            pages.value = (data.data || data || []).map((p: { id: number | string | null, title: string, slug: string, status: string, type?: string }) => ({
                id: p.id,
                title: p.title,
                slug: p.slug,
                status: p.status,
                type: p.type || 'page'
            }))
        } catch (error: unknown) {
            logger.error('Failed to fetch pages:', error instanceof Error ? error.message : String(error));
        } finally {
            pagesLoading.value = false
        }
    }

    async function loadContent(id: number | string): Promise<void> {
        try {
            const response = await api.get(`/manage/publishing/contents/${id}`)
            const data = response.data?.data || response.data

            if (data) {
                content.value = {
                    id: data.id,
                    title: data.title || '',
                    slug: data.slug || '',
                    excerpt: data.excerpt || '',
                    body: data.body || '',
                    status: data.status || 'draft',
                    type: data.type || 'page',
                    editor_type: data.editor_type || 'builder',
                    category_id: data.category_id || null,
                    featured_image: data.featured_image || null,
                    published_at: data.published_at || null,
                    meta_title: data.meta_title || '',
                    meta_description: data.meta_description || '',
                    meta_keywords: data.meta_keywords || '',
                    og_image: data.og_image || null,
                    comment_status: data.comment_status !== undefined ? data.comment_status : true,
                    is_featured: !!data.is_featured,
                    tags: data.tags || [],
                    meta: data.meta || {},
                    menu_item: {
                        add_to_menu: false,
                        menu_id: '',
                        parent_id: null,
                        title: ''
                    }
                }

                if (data.menu_items && data.menu_items.length > 0) {
                    const menuItem = data.menu_items[0]
                    content.value.menu_item = {
                        add_to_menu: true,
                        menu_id: menuItem.menu_id,
                        parent_id: menuItem.parent_id,
                        title: menuItem.title
                    }
                }

                // Check builder_blocks in meta first (CMS standard storage), then root blocks, then fallback to body
                const metaBlocks = data.meta?.builder_blocks
                if (Array.isArray(metaBlocks) && metaBlocks.length > 0) {
                    blocks.value = metaBlocks
                } else if (data.blocks && Array.isArray(data.blocks) && data.blocks.length > 0) {
                    blocks.value = data.blocks
                } else if (data.body && data.body.trim().length > 0) {
                    const textBlock = ModuleRegistry.createInstance('text', {
                        content: data.body.trim()
                    }) || {
                        id: `module-${Date.now()}-text`,
                        type: 'text',
                        settings: { content: data.body.trim() }
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

                    blocks.value = [section]
                } else {
                    // Empty stays empty. Templates are an explicit insert from the canvas library.
                    blocks.value = []
                }

                triggerRef(blocks)
                takeSnapshot({ immediate: true })
                markAsSaved()

                if (data.global_variables) {
                    globalVariables.loadVariables(data.global_variables)
                }
            }
        } catch (error: unknown) {
            logger.error('Failed to load content for builder:', error instanceof Error ? error.message : String(error));
            throw error
        }
    }

    async function setCurrentPage(id: number | string): Promise<void> {
        try {
            await loadContent(id)
            currentPageId.value = id
        } catch (error: unknown) {
            logger.error('Failed to switch page:', error instanceof Error ? error.message : String(error));
        }
    }

    async function addPage(title: string): Promise<void> {
        try {
            const payload = {
                title,
                type: 'page',
                status: 'draft',
                editor_type: 'builder',
                body: '',
                excerpt: '',
                category_id: null,
                blocks: []
            }
            const response = await api.post('/manage/publishing/contents', payload)
            const newPage = response.data?.data || response.data

            if (newPage) {
                await fetchPages()
                await setCurrentPage(newPage.id)
            }
        } catch (error: unknown) {
            let message = 'Failed to create page'
            if (error && typeof error === 'object' && 'response' in error) {
                const err = error as { response?: { data?: { message?: string } }; message?: string };
                message = err.response?.data?.message || err.message || message
            } else if (error instanceof Error) {
                message = error.message
            }
            logger.error('Failed to create page:', error instanceof Error ? error.message : String(error));
            throw new Error(message, { cause: error })
        }
    }

    async function deletePage(id: number | string): Promise<void> {
        try {
            await api.delete(`/manage/publishing/contents/${id}`)
            await fetchPages()

            if (currentPageId.value === id) {
                if (pages.value.length > 0) {
                    const firstPage = pages.value[0]
                    if (firstPage && firstPage.id !== null && firstPage.id !== undefined) {
                        await setCurrentPage(firstPage.id)
                    }
                } else {
                    blocks.value = []
                    currentPageId.value = null
                    content.value = {
                        id: null, title: '', slug: '', excerpt: '', body: '', status: 'draft',
                        type: 'page', editor_type: 'builder', category_id: null, featured_image: null,
                        published_at: null, tags: [], menu_item: { add_to_menu: false, menu_id: '', parent_id: null, title: '' }
                    }
                }
            }
        } catch (error: unknown) {
            logger.error('Failed to delete page:', error instanceof Error ? error.message : String(error));
            throw error
        }
    }

    function extractHtmlFromBlocks(blocksList: any[]): string {
        let html = ''
        function traverse(b: any) {
            if (!b) return
            if (b.type === 'richtext' || b.type === 'text') {
                if (b.settings?.content) {
                    html += String(b.settings.content) + '\n'
                }
            } else if (b.type === 'heading') {
                const level = b.settings?.level || 2
                const text = b.settings?.text || ''
                if (text) {
                    html += `<h${level}>${text}</h${level}>\n`
                }
            }
            const nested = Array.isArray(b.children) ? b.children : b.children
            if (Array.isArray(nested)) {
                nested.forEach(traverse)
            }
        }
        (blocksList || []).forEach(traverse)
        return html.trim()
    }

    async function saveContent(): Promise<Record<string, unknown> | false> {
        if (!content.value.id) return false
        try {
            const currentMeta = (content.value.meta as Record<string, any>) || {}
            const extractedHtml = extractHtmlFromBlocks(blocks.value)
            const payload: Record<string, unknown> = {
                ...content.value,
                body: extractedHtml || content.value.body || '',
                blocks: blocks.value,
                create_revision: true,
                revision_note: 'Builder save',
                meta: {
                    ...currentMeta,
                    builder_blocks: blocks.value,
                    builder_schema_version: BUILDER_SCHEMA_VERSION,
                },
                global_variables: globalVariables.getVariables()
            }
            if (content.value.tags) {
                payload.tags = (content.value.tags as { id?: string | number }[])
                    .filter((t) => t && t.id != null)
                    .map((t) => t.id)
                payload.new_tags = (content.value.tags as { id?: string | number; name?: string }[])
                    .filter((t) => t && t.id == null && t.name)
                    .map((t) => t.name)
            }
            const response = await api.put(`/manage/publishing/contents/${content.value.id}`, payload)
            markAsSaved()
            return response.data
        } catch (error: unknown) {
            logger.error('Failed to save content from builder:', error instanceof Error ? error.message : String(error));
            throw error
        }
    }

    async function saveGlobalVariables(): Promise<void> {
        const vars = globalVariables.getVariables()
        if (content.value.id) {
            try {
                const response = await api.put(`/manage/publishing/contents/${content.value.id}`, { global_variables: vars })
                return response.data
            } catch (error: unknown) {
                logger.error('Failed to save global variables to content:', error instanceof Error ? error.message : String(error));
                throw error
            }
        }
        if (activeTheme.value) {
            try {
                const currentSettings = themeSettings.value || {}
                const newSettings = { ...currentSettings, global_variables: vars }
                const themeKey = resolveThemeRouteKey()
                const response = await api.put(layoutPaths.themeSettings(themeKey), { settings: newSettings })
                themeSettings.value = newSettings
                return response.data
            } catch (error: unknown) {
                logger.error('Failed to save global variables to theme:', error instanceof Error ? error.message : String(error));
                throw error
            }
        }
        throw new Error('No content or theme available to save global variables')
    }

    async function fetchMetadata(): Promise<void> {
        try {
            const [catsRes, tagsRes, menusRes, formsRes, modelsRes] = await Promise.allSettled([
                api.get('/manage/library/categories'),
                api.get('/manage/library/tags'),
                api.get('/manage/layout/menus'),
                api.get('/manage/forms'),
                api.get('/manage/infra/models/types')
            ]);

            const parseList = (settled: PromiseSettledResult<any>) => {
                if (settled.status !== 'fulfilled') return [];
                const res = settled.value;
                const data = res?.data?.data || res?.data || [];
                return Array.isArray(data) ? data : [];
            };

            const catList = parseList(catsRes);
            const tagList = parseList(tagsRes);
            const menuList = parseList(menusRes);
            const formList = parseList(formsRes);
            const modelList = parseList(modelsRes);

            categories.value = catList as Category[];
            availableTags.value = tagList as Tag[];
            menus.value = menuList as Menu[];

            if (typeof window !== 'undefined') {
                (window as any).jaCmsData = {
                    ...((window as any).jaCmsData || {}),
                    categories: catList.map((c: any) => ({ value: c.slug || c.id, label: c.name || c.title || c.slug })),
                    terms: catList.map((c: any) => ({ value: c.slug || c.id, label: c.name || c.title || c.slug })),
                    tags: tagList.map((t: any) => ({ value: t.slug || t.id, label: t.name || t.slug })),
                    menus: menuList.map((m: any) => ({ value: m.slug || m.id, label: m.name || m.slug })),
                    forms: formList.map((f: any) => ({ value: f.slug || f.id, label: `${f.name || f.title || f.slug} (${f.slug})` })),
                    models: modelList.map((m: any) => ({ value: m.slug, label: `${m.name || m.title || m.slug} (${m.slug})` }))
                };
            }
        } catch (error: unknown) {
            logger.error('Failed to fetch builder metadata:', error instanceof Error ? error.message : String(error));
        }
    }

    async function fetchThemes(): Promise<void> {
        loadingThemes.value = true
        try {
            const response = await api.get('/manage/layout/themes')
            const data = response.data?.data || response.data
            availableThemes.value = Array.isArray(data) ? data : (data.data || [])
        } catch (error: unknown) {
            logger.error('Failed to fetch themes:', error instanceof Error ? error.message : String(error));
        } finally {
            loadingThemes.value = false
        }
    }

    let autoSaveInterval: ReturnType<typeof setInterval> | null = null
    function startAutoSave(): void {
        if (autoSaveInterval) clearInterval(autoSaveInterval)
        autoSaveInterval = setInterval(async () => {
            if (autoSave.value && content.value.id) {
                try {
                    await saveContent()
                    logger.info('[Auto-save] Content saved')
                } catch (e: unknown) {
                    logger.error('[Auto-save] Failed:', e instanceof Error ? e.message : String(e));
                }
            }
        }, 60000)
    }

    function stopAutoSave(): void {
        if (autoSaveInterval) {
            clearInterval(autoSaveInterval)
            autoSaveInterval = null
        }
    }

    async function updateThemeSettings(themeSlug: string, settings: Record<string, unknown>): Promise<void> {
        try {
            const themeKey = resolveThemeRouteKey(themeSlug)
            await api.put(layoutPaths.themeSettings(themeKey), { settings })
            themeSettings.value = { ...themeSettings.value, ...settings }
            if (themeData.value && typeof themeData.value === 'object') {
                themeData.value = {
                    ...themeData.value,
                    settings: { ...(themeData.value.settings || {}), ...settings },
                }
            }
        } catch (error: unknown) {
            logger.error('Failed to update theme settings:', error instanceof Error ? error.message : String(error));
            throw error
        }
    }

    async function fetchTemplates(): Promise<unknown[]> {
        try {
            const response = await api.get('/manage/publishing/contents', {
                params: { type: 'layout', per_page: 100 }
            })
            const data = response.data?.data || response.data
            return data.data || data || []
        } catch (error: unknown) {
            logger.error('Failed to fetch templates:', error instanceof Error ? error.message : String(error));
            return []
        }
    }

    async function createTemplate(data: { name: string, type: string }): Promise<Record<string, unknown>> {
        try {
            const payload = {
                title: data.name,
                type: 'layout',
                status: 'published',
                editor_type: 'builder',
                blocks: [],
                meta: { template_type: data.type }
            }
            const response = await api.post('/manage/publishing/contents', payload)
            return response.data?.data || response.data
        } catch (error: unknown) {
            logger.error('Failed to create template:', error instanceof Error ? error.message : String(error));
            throw error
        }
    }

    async function deleteTemplate(id: number | string): Promise<boolean> {
        try {
            await api.delete(`/manage/publishing/contents/${id}`)
            return true
        } catch (error: unknown) {
            logger.error('Failed to delete template:', error instanceof Error ? error.message : String(error));
            throw error
        }
    }

    async function fetchRevisions(): Promise<Array<Record<string, unknown>>> {
        const id = content.value.id
        if (!id) {
            return []
        }
        const response = await api.get(`/manage/publishing/contents/${id}/revisions`)
        const payload = response.data?.data
        if (Array.isArray(payload)) {
            return payload
        }
        if (payload && Array.isArray(payload.data)) {
            return payload.data
        }
        return []
    }

    async function restoreRevision(revisionId: string): Promise<void> {
        const id = content.value.id
        if (!id) {
            return
        }
        const response = await api.post(`/manage/publishing/contents/${id}/revisions/${revisionId}/restore`)
        const restored = response.data?.data?.content || response.data?.content
        const metaBlocks = restored?.meta?.builder_blocks
        if (Array.isArray(metaBlocks)) {
            blocks.value = metaBlocks
            triggerRef(blocks)
            takeSnapshot({ immediate: true })
            markAsSaved()
        }
    }

    async function acquireLock(): Promise<{ ok: boolean; message?: string }> {
        const id = content.value.id
        if (!id) {
            return { ok: true }
        }
        try {
            await api.post(`/manage/publishing/contents/${id}/lock`)
            return { ok: true }
        } catch (error: unknown) {
            const err = error as { response?: { data?: { message?: string }; status?: number } }
            return { ok: false, message: err.response?.data?.message || 'Content is locked' }
        }
    }

    async function releaseLock(): Promise<void> {
        const id = content.value.id
        if (!id) {
            return
        }
        try {
            await api.post(`/manage/publishing/contents/${id}/unlock`)
        } catch {
            /* unlock is best-effort on close */
        }
    }

    async function updateContentMeta(id: number | string, meta: Record<string, unknown>): Promise<Record<string, unknown>> {
        try {
            const response = await api.put(`/manage/publishing/contents/${id}`, { meta })
            return response.data?.data || response.data
        } catch (error: unknown) {
            logger.error('Failed to update content meta:', error instanceof Error ? error.message : String(error));
            throw error
        }
    }

    return {
        fetchPages,
        setCurrentPage,
        addPage,
        deletePage,
        loadContent,
        saveContent,
        saveGlobalVariables,
        fetchMetadata,
        fetchThemes,
        markAsSaved,
        startAutoSave,
        stopAutoSave,
        updateThemeSettings,
        fetchTemplates,
        createTemplate,
        deleteTemplate,
        updateContentMeta,
        fetchRevisions,
        restoreRevision,
        acquireLock,
        releaseLock,
    }
}

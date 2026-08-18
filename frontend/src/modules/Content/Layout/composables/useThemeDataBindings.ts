import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useTheme } from '@/modules/Content/Layout/composables/useTheme'
import api from '@/engine/api/client'
import { libraryPaths, publishingPaths } from '@/engine/api/paths'
import { logger } from '@/shared/utils/logger'
import { THEME_DATA_BINDINGS_KEY, isPlainSettingsObject } from '@/modules/Content/Layout/constants/themeBindings'

export interface SlotBinding {
    sourceType: 'static' | 'api_posts' | 'api_pages' | 'api_categories'
    categoryFilter?: string
    pageSlug?: string
    tagFilter?: string
    limit?: number
    orderBy?: string
    orderDir?: 'asc' | 'desc'
    propMapping: Record<string, string>
    staticContent?: Record<string, unknown>
}

export interface ComponentBindings {
    slots: Record<string, SlotBinding>
    settings?: Record<string, unknown>
}

/**
 * Theme data bindings from active theme `settings.theme_data_bindings`.
 */
export function getThemeDataBindings(): Record<string, ComponentBindings> {
    const { getSetting } = useTheme()
    const bindings = getSetting(THEME_DATA_BINDINGS_KEY, {})

    if (isPlainSettingsObject(bindings)) {
        return bindings as Record<string, ComponentBindings>
    }

    return {}
}

/**
 * Bindings for a single theme component id.
 */
export function getThemeComponentBindings(componentId: string): ComponentBindings | null {
    const all = getThemeDataBindings()

    return all[componentId] || null
}

/**
 * Resolve data from API based on a slot binding configuration
 */
async function resolveSlotData(binding: SlotBinding): Promise<unknown[]> {
    if (!binding || binding.sourceType === 'static') {
        return []
    }

    try {
        if (binding.sourceType === 'api_posts') {
            const params: Record<string, unknown> = {
                type: 'post',
                status: 'published',
                per_page: binding.limit || 5,
                sort_by: binding.orderBy || 'published_at',
                sort_dir: binding.orderDir || 'desc',
            }
            if (binding.categoryFilter && binding.categoryFilter !== 'all') {
                params.category = binding.categoryFilter
            }
            if (binding.tagFilter && binding.tagFilter !== 'all') {
                params.tag = binding.tagFilter
            }
            const response = await api.get(publishingPaths.publicContents, { params })
            const data = response.data

            return Array.isArray(data) ? data : data?.data || []
        }

        if (binding.sourceType === 'api_pages') {
            if (binding.pageSlug) {
                const response = await api.get(publishingPaths.publicContent(binding.pageSlug))
                const data = response.data
 
                return data ? [data] : []
            }
            const params: Record<string, unknown> = {
                type: 'page',
                status: 'published',
                per_page: binding.limit || 10,
            }
            const response = await api.get(publishingPaths.publicContents, { params })
            const data = response.data
 
            return Array.isArray(data) ? data : data?.data || []
        }

        if (binding.sourceType === 'api_categories') {
            const response = await api.get(libraryPaths.publicCategories)
            const data = response.data
 
            return Array.isArray(data) ? data : []
        }
    } catch (e) {
        logger.error(`[useThemeDataBindings] Failed to resolve slot data:`, e)
    }

    return []
}

/**
 * Map raw API data to component props using propMapping
 */
export function mapDataToProps(rawData: Record<string, unknown>[], propMapping: Record<string, string>): Record<string, unknown>[] {
    if (!propMapping || Object.keys(propMapping).length === 0) {
        return rawData
    }

    return rawData.map(item => {
        const mapped: Record<string, unknown> = { _raw: item }
        for (const [propKey, dataField] of Object.entries(propMapping)) {
            // Support nested fields like "category.name"
            const parts = dataField.split('.')
            let value: unknown = item
            for (const part of parts) {
                if (value && typeof value === 'object') {
                    value = (value as Record<string, unknown>)[part]
                } else {
                    value = undefined
                    break
                }
            }
            mapped[propKey] = value
        }

        return mapped
    })
}

/**
 * Resolve reactive data for one component slot from theme bindings.
 */
export function useThemeDataBindings(componentId: string, slotId: string = 'default') {
    const data = ref<unknown[]>([])
    const loading = ref(false)
    const error = ref<string | null>(null)
    const hasBinding = ref(false)
    const isUnmounted = ref(false)

    const resolve = async () => {
        if (isUnmounted.value) return

        const compBindings = getThemeComponentBindings(componentId)
        if (!compBindings?.slots?.[slotId]) {
            if (!isUnmounted.value) hasBinding.value = false

            return
        }
        const slotBinding = compBindings.slots[slotId]
        if (slotBinding.sourceType === 'static') {
            if (!isUnmounted.value) hasBinding.value = false

            return
        }

        if (isUnmounted.value) return
        hasBinding.value = true
        loading.value = true
        error.value = null

        try {
            const rawData = await resolveSlotData(slotBinding)
            if (isUnmounted.value) return

            if (slotBinding.propMapping && Object.keys(slotBinding.propMapping).length > 0) {
                data.value = mapDataToProps(rawData as Record<string, unknown>[], slotBinding.propMapping)
            } else {
                data.value = rawData
            }
        } catch (e) {
            if (isUnmounted.value) return
            error.value = e instanceof Error ? e.message : 'Failed to resolve data'
            logger.error(`[useThemeDataBindings] Error for ${componentId}/${slotId}:`, e)
        } finally {
            if (!isUnmounted.value) loading.value = false
        }
    }

    onMounted(() => {
        requestAnimationFrame(() => {
            void resolve()
        })
    })

    onBeforeUnmount(() => {
        isUnmounted.value = true
    })

    return {
        data,
        loading,
        error,
        hasBinding,
        refresh: resolve,
    }
}

/**
 * Resolve all non-static slots for a component at once.
 */
export function useThemeComponentBindings(componentId: string) {
    const slots = ref<Record<string, unknown[]>>({})
    const loading = ref(false)
    const hasAnyBinding = ref(false)
    const isUnmounted = ref(false)

    const resolve = async () => {
        if (isUnmounted.value) return

        const compBindings = getThemeComponentBindings(componentId)
        if (!compBindings || !compBindings.slots) {
            if (!isUnmounted.value) hasAnyBinding.value = false

            return
        }

        const slotEntries = Object.entries(compBindings.slots)
        const hasNonStatic = slotEntries.some(([_, b]) => b && b.sourceType !== 'static')
        if (!hasNonStatic) {
            if (!isUnmounted.value) hasAnyBinding.value = false

            return
        }

        if (isUnmounted.value) return
        hasAnyBinding.value = true
        loading.value = true

        try {
            const results: Record<string, unknown[]> = {}
            for (const [slotId, slotBinding] of slotEntries) {
                if (isUnmounted.value || !slotBinding) continue

                if (slotBinding.sourceType === 'static') {
                    results[slotId] = []
                    continue
                }
                const rawData = await resolveSlotData(slotBinding)
                if (isUnmounted.value) return

                if (slotBinding.propMapping && Object.keys(slotBinding.propMapping).length > 0) {
                    results[slotId] = mapDataToProps(rawData as Record<string, unknown>[], slotBinding.propMapping)
                } else {
                    results[slotId] = rawData
                }
            }
            if (!isUnmounted.value) slots.value = results
        } catch (e) {
            if (isUnmounted.value) return
            logger.error(`[useThemeComponentBindings] Error for ${componentId}:`, e)
        } finally {
            if (!isUnmounted.value) loading.value = false
        }
    }

    onMounted(() => {
        requestAnimationFrame(() => {
            void resolve()
        })
    })

    onBeforeUnmount(() => {
        isUnmounted.value = true
    })

    return {
        slots,
        loading,
        hasAnyBinding,
        refresh: resolve,
    }
}

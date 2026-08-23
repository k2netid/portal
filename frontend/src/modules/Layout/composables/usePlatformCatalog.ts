import { ref, onMounted } from 'vue'
import api from '@/engine/api/client'
import { platformPaths } from '@/engine/api/paths'
import { logger } from '@/shared/utils/logger'

export interface PlatformCatalogPackage {
    id: string
    name: string
    price_monthly: number
    price_yearly: number
    user_limit: number
    storage_limit_mb: number
    ai_monthly_token_limit: number
    feature_highlights: string[]
}

export interface PlatformCatalogProduct {
    id: string
    name: string
    description: string | null
    packages: PlatformCatalogPackage[]
}

export function usePlatformCatalog() {
    const live = ref(false)
    const products = ref<PlatformCatalogProduct[]>([])
    const loading = ref(true)
    const error = ref<string | null>(null)

    const load = async () => {
        loading.value = true
        error.value = null
        try {
            const response = await api.get(platformPaths.publicCatalog)
            const payload = response.data as { live?: boolean; products?: PlatformCatalogProduct[] }
            live.value = Boolean(payload?.live)
            products.value = Array.isArray(payload?.products) ? payload.products : []
        } catch (e) {
            logger.error('[usePlatformCatalog] Failed to load catalog', e)
            live.value = false
            products.value = []
            error.value = 'catalog_unavailable'
        } finally {
            loading.value = false
        }
    }

    onMounted(() => {
        void load()
    })

    return { live, products, loading, error, reload: load }
}

import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePublicPageContent } from '@/modules/Layout/composables/usePublicPageContent'
import { resolvePublicPageCmsBody } from '@/modules/Layout/utils/resolveLocalizedContent'
import type { BlockInstance } from '@/modules/Layout/types/builder'
import type { MaybeRefOrGetter } from 'vue'

function readMeta(record: Record<string, unknown> | null | undefined): Record<string, unknown> {
  const meta = record?.meta
  if (meta && typeof meta === 'object' && !Array.isArray(meta)) {
    return meta as Record<string, unknown>
  }
  return {}
}

/**
 * Theme pages keep Vue layout unless the editor explicitly opts into a full builder override.
 * Plain CMS pages / posts (no theme_page) still render builder_blocks when present.
 */
export function pageUsesBuilderOverride(record: Record<string, unknown> | null | undefined): boolean {
  if (!record) return false
  const meta = readMeta(record)
  const blocks = meta.builder_blocks || record.blocks
  if (!Array.isArray(blocks) || blocks.length === 0) return false

  const themePage = typeof meta.theme_page === 'string' ? meta.theme_page.trim() : ''
  if (!themePage) return true

  return meta.builder_override === true
}

/**
 * Shared CMS override for theme pages:
 * explicit builder_override → classic HTML body → theme Vue.
 */
export function useThemePageOverride(slug: MaybeRefOrGetter<string>) {
  const { locale } = useI18n({ useScope: 'global' })
  const { pageData, loading, error, reload } = usePublicPageContent(slug)

  const cmsBody = computed(() => resolvePublicPageCmsBody(pageData.value, locale.value))

  const builderBlocks = computed<BlockInstance[]>(() => {
    const meta = readMeta(pageData.value)
    const blocks = meta.builder_blocks || pageData.value?.blocks
    return Array.isArray(blocks) ? (blocks as BlockInstance[]) : []
  })

  /** @deprecated Prefer usesBuilderOverride — kept as alias for existing templates. */
  const hasBuilderBlocks = computed(() => pageUsesBuilderOverride(pageData.value))

  const usesBuilderOverride = hasBuilderBlocks

  return {
    pageData,
    loading,
    error,
    reload,
    cmsBody,
    builderBlocks,
    hasBuilderBlocks,
    usesBuilderOverride,
  }
}

import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePublicPageContent } from '@/modules/Layout/composables/usePublicPageContent'
import { resolveLocalizedPageHtml } from '@/modules/Layout/utils/resolveLocalizedContent'
import type { BlockInstance } from '@/modules/Layout/types/builder'
import type { MaybeRefOrGetter } from 'vue'

/**
 * Shared CMS override for theme pages: builder_blocks → classic HTML body → theme Vue.
 */
export function useThemePageOverride(slug: MaybeRefOrGetter<string>) {
  const { locale } = useI18n({ useScope: 'global' })
  const { pageData, loading, error, reload } = usePublicPageContent(slug)

  const cmsBody = computed(() => resolveLocalizedPageHtml(pageData.value, locale.value))

  const builderBlocks = computed<BlockInstance[]>(() => {
    const meta = pageData.value?.meta as Record<string, unknown> | undefined
    const blocks = meta?.builder_blocks || pageData.value?.blocks
    return Array.isArray(blocks) ? (blocks as BlockInstance[]) : []
  })

  const hasBuilderBlocks = computed(() => builderBlocks.value.length > 0)

  return {
    pageData,
    loading,
    error,
    reload,
    cmsBody,
    builderBlocks,
    hasBuilderBlocks,
  }
}

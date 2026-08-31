<template>
  <div class="min-h-screen bg-background" data-ja-customizer-target="pricing">
    <!-- Visual Builder Content if page was customized in Builder -->
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: 'Jejakawan' } }"
    />

    <!-- Dynamic Classic Content if exists -->
    <SafeHtml
      v-else-if="cmsBody"
      class="container mx-auto px-4 py-16 Jejakawan-content"
      :html="cmsBody"
      mode="publishing"
    />

    <!-- Default Theme Template -->
    <template v-else>
      <header class="py-10 md:py-12 bg-gradient-to-b from-primary/10 to-background border-b border-border/50">
        <div class="container mx-auto px-4 text-center">
          <span class="text-primary font-bold tracking-wider uppercase text-sm mb-4 block">
            {{ sectionLabel }}
          </span>
          <h1 class="text-4xl md:text-6xl font-extrabold text-foreground mb-6">
            {{ pageTitle }}
          </h1>
          <p class="text-xl text-muted-foreground max-w-2xl mx-auto leading-relaxed">
            {{ pageSubtitle }}
          </p>
        </div>
      </header>

      <section class="py-16">
        <div class="container mx-auto px-4">
          <div v-if="loading" class="flex justify-center py-20">
            <div class="h-10 w-10 animate-spin rounded-full border-b-2 border-primary" />
          </div>

          <template v-else-if="live && products.length > 0">
            <div v-for="product in products" :key="product.id" class="mb-16 last:mb-0">
              <div class="text-center mb-10">
                <span class="text-primary font-bold tracking-wider uppercase text-xs mb-2 block">
                  {{ productSectionLabel(product.id) }}
                </span>
                <h2 class="text-2xl font-bold text-foreground">{{ product.name }}</h2>
                <p v-if="product.description" class="text-muted-foreground mt-2 max-w-2xl mx-auto">
                  {{ product.description }}
                </p>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <article
                  v-for="pkg in product.packages"
                  :key="pkg.id"
                  class="flex flex-col p-8 rounded-2xl border border-border bg-card hover:border-primary/40 transition-colors"
                  :class="isFeaturedPackage(pkg) ? 'border-primary/50 ring-1 ring-primary/20' : ''"
                >
                  <span
                    v-if="isFeaturedPackage(pkg)"
                    class="text-[10px] font-black uppercase tracking-widest text-primary mb-3"
                  >
                    {{ recommendedText }}
                  </span>
                  <h3 class="text-lg font-bold text-foreground mb-4">{{ pkg.name }}</h3>
                  <div class="mb-6">
                    <template v-if="pkg.price_monthly > 0">
                      <div class="text-3xl font-black text-primary">{{ formatIdr(pkg.price_monthly) }}</div>
                      <span class="text-xs text-muted-foreground">{{ perMonthLabel }}</span>
                    </template>
                    <span v-else class="text-2xl font-black text-foreground">{{ customEnterpriseLabel }}</span>
                  </div>
                  <ul class="space-y-3 mb-8 text-sm flex-1">
                    <li class="flex items-center gap-2 text-muted-foreground">
                      <span class="text-primary font-bold">✓</span>
                      {{ formatLimit(pkg.user_limit) }} {{ membersLabel }}
                    </li>
                    <li class="flex items-center gap-2 text-muted-foreground">
                      <span class="text-primary font-bold">✓</span>
                      {{ formatStorage(pkg.storage_limit_mb) }} {{ storageLabel }}
                    </li>
                  </ul>
                  <a
                    href="/auth/console-sign-up"
                    class="block text-center py-3 text-xs font-bold uppercase tracking-widest rounded-lg transition-colors"
                    :class="isFeaturedPackage(pkg) ? 'bg-primary text-primary-foreground hover:opacity-90' : 'border border-border hover:bg-muted/50'"
                  >
                    {{ choosePlanText }}
                  </a>
                </article>
              </div>
            </div>
          </template>

          <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <article
              v-for="tier in staticTiers"
              :key="tier.key"
              class="flex flex-col p-8 rounded-2xl border border-border bg-card"
              :class="tier.featured ? 'border-primary shadow-lg ring-1 ring-primary/30' : ''"
            >
              <h3 class="text-lg font-bold text-foreground mb-4">
                {{ t(`pages.pricing.tiers.${tier.key}.name`) }}
              </h3>
              <p class="text-sm text-muted-foreground mb-6 flex-1">
                {{ t(`pages.pricing.tiers.${tier.key}.description`) }}
              </p>
              <router-link
                :to="contactUrl"
                class="block text-center py-3 text-xs font-bold uppercase tracking-widest rounded-lg"
                :class="tier.featured ? 'bg-primary text-primary-foreground' : 'border border-border hover:bg-muted/50'"
              >
                {{ contactSalesText }}
              </router-link>
            </article>
          </div>

          <p class="text-center text-xs text-muted-foreground mt-12">
            {{ footnoteText }}
          </p>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue'
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue'
import type { BlockInstance } from '@/modules/Layout/types/builder'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'
import { usePlatformCatalog } from '@/modules/Layout/composables/usePlatformCatalog'
import { usePublicPageContent } from '@/modules/Layout/composables/usePublicPageContent'
import { pageUsesBuilderOverride } from '@/modules/Layout/composables/useThemePageOverride'
import { resolvePublicPageCmsBody } from '@/modules/Layout/utils/resolveLocalizedContent'

const { t } = useThemeI18n('janari')
const { locale } = useI18n({ useScope: 'global' })
const { getSetting } = useTheme()
const { localizedString } = useLocalizedThemeSetting()
const { live, products, loading } = usePlatformCatalog()

const { pageData } = usePublicPageContent('pricing')
const cmsBody = computed(() => resolvePublicPageCmsBody(pageData.value, locale.value))

const sectionLabel = computed(() => localizedString('page_pricing_section_label') || t('pages.pricing.sectionLabel'))
const pageTitle = computed(() => localizedString('page_pricing_title') || t('pages.pricing.title'))
const pageSubtitle = computed(() => localizedString('page_pricing_subtitle') || t('pages.pricing.subtitle'))
const recommendedText = computed(() => localizedString('page_pricing_recommended') || t('pages.pricing.recommended'))
const choosePlanText = computed(() => localizedString('page_pricing_choose_plan') || t('pages.pricing.cta'))
const contactSalesText = computed(() => localizedString('page_pricing_contact_sales') || t('pages.pricing.contactCta'))
const footnoteText = computed(() => localizedString('page_pricing_footnote') || t('pages.pricing.footnote'))
const perMonthLabel = computed(() => localizedString('page_pricing_per_month') || t('pages.pricing.perMonth'))
const customEnterpriseLabel = computed(() => localizedString('page_pricing_custom') || t('pages.pricing.contactForPrice'))
const membersLabel = computed(() => localizedString('page_pricing_members') || t('pages.pricing.members'))
const storageLabel = computed(() => localizedString('page_pricing_storage') || t('pages.pricing.storage'))
const unlimitedLabel = computed(() => localizedString('page_pricing_unlimited') || t('pages.pricing.unlimited'))
const contactUrl = computed(() => {
  const raw = getSetting('page_pricing_contact_url', '/contact')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/contact'
})

const builderBlocks = computed<BlockInstance[]>(() => {
  const meta = pageData.value?.meta as Record<string, unknown> | undefined
  const blocks = meta?.builder_blocks || pageData.value?.blocks
  if (Array.isArray(blocks)) {
    return blocks as BlockInstance[]
  }
  return []
})
const hasBuilderBlocks = computed(() => pageUsesBuilderOverride(pageData.value))

const FEATURED_PACKAGE_ID = 'hub-growth'

const isFeaturedPackage = (pkg: { id: string }): boolean => pkg.id === FEATURED_PACKAGE_ID

const productSectionLabel = (productId: string): string => {
  if (productId === 'platform') return localizedString('page_pricing_platform_label') || t('pages.pricing.platformLabel')
  if (productId === 'hub') return localizedString('page_pricing_hub_label') || t('pages.pricing.hubLabel')
  return sectionLabel.value
}

const staticTiers = [
  { key: 'starter' as const, featured: false },
  { key: 'growth' as const, featured: true },
  { key: 'enterprise' as const, featured: false },
]

const formatIdr = (amount: number): string => {
  if (!Number.isFinite(amount) || amount <= 0) return '—'
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount)
}
const formatLimit = (n: number): string => (n >= 999999 ? unlimitedLabel.value : String(n))
const formatStorage = (mb: number): string => (mb >= 1024 ? `${Math.round(mb / 1024)} GB` : `${mb} MB`)
</script>

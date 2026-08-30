<template>
  <div class="min-h-screen bg-background">
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
      <header class="py-20 bg-gradient-to-b from-primary/10 to-background border-b border-border/50">
        <div class="container mx-auto px-4 text-center">
          <span class="text-primary font-bold tracking-wider uppercase text-sm mb-4 block">{{ sectionLabel }}</span>
          <h1 class="text-4xl md:text-6xl font-extrabold text-foreground mb-6">
            {{ pageTitle }}
          </h1>
          <p class="text-xl text-muted-foreground max-w-2xl mx-auto leading-relaxed">
            {{ pageSubtitle }}
          </p>
        </div>
      </header>

      <section class="py-20">
        <div class="container mx-auto px-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div
              v-for="pillar in pillars"
              :key="pillar.key"
              class="p-8 rounded-2xl border border-border bg-card text-center"
            >
              <component :is="pillar.icon" class="w-12 h-12 text-primary mx-auto mb-6" stroke-width="1.5" />
              <h3 class="text-lg font-bold text-foreground mb-3">
                {{ t(`pages.team.pillars.${pillar.key}.title`) }}
              </h3>
              <p class="text-sm text-muted-foreground leading-relaxed">
                {{ t(`pages.team.pillars.${pillar.key}.description`) }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <section class="py-16 border-t border-border">
        <div class="container mx-auto px-4">
          <h2 class="text-2xl font-bold text-center text-foreground mb-12">
            {{ t('pages.team.areasTitle') }}
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
              v-for="area in areas"
              :key="area.key"
              class="p-6 rounded-xl border border-border"
            >
              <h3 class="font-bold text-foreground mb-2">
                {{ t(`pages.team.areas.${area.key}.title`) }}
              </h3>
              <p class="text-sm text-muted-foreground">
                {{ t(`pages.team.areas.${area.key}.description`) }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <section class="py-16 border-t border-border bg-muted/20">
        <div class="container mx-auto px-4 max-w-3xl text-center space-y-6">
          <p class="text-muted-foreground leading-relaxed">
            {{ closingText }}
          </p>
          <div class="flex flex-wrap justify-center gap-4">
            <router-link to="/about" class="text-xs font-bold uppercase tracking-widest text-primary hover:underline">
              {{ t('pages.team.aboutLink') }}
            </router-link>
            <router-link to="/careers" class="text-xs font-bold uppercase tracking-widest text-primary hover:underline">
              {{ t('pages.team.careersLink') }}
            </router-link>
            <router-link to="/contact" class="text-xs font-bold uppercase tracking-widest text-primary hover:underline">
              {{ t('pages.team.contactLink') }}
            </router-link>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Heart, Rocket, Shield, Code, PenLine, Settings, Headphones } from 'lucide-vue-next'
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue'
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue'
import type { BlockInstance } from '@/modules/Layout/types/builder'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'
import { usePublicPageContent } from '@/modules/Layout/composables/usePublicPageContent'
import { resolveLocalizedPageHtml } from '@/modules/Layout/utils/resolveLocalizedContent'

const { t } = useThemeI18n('janari')
const { locale } = useI18n({ useScope: 'global' })
const { localizedString } = useLocalizedThemeSetting()

const { pageData } = usePublicPageContent('tim')
const cmsBody = computed(() => resolveLocalizedPageHtml(pageData.value, locale.value))

const builderBlocks = computed<BlockInstance[]>(() => {
  const meta = pageData.value?.meta as Record<string, unknown> | undefined
  const blocks = meta?.builder_blocks || pageData.value?.blocks
  if (Array.isArray(blocks)) {
    return blocks as BlockInstance[]
  }
  return []
})
const hasBuilderBlocks = computed(() => builderBlocks.value.length > 0)

const pageTitle = computed(() => localizedString('page_tim_title') || t('pages.team.title'))
const pageSubtitle = computed(() => localizedString('page_tim_subtitle') || t('pages.team.subtitle'))
const sectionLabel = computed(() => localizedString('page_tim_section_label') || t('pages.team.sectionLabel'))
const closingText = computed(() => localizedString('page_tim_closing') || t('pages.team.closing'))

const pillars = [
  { key: 'craft' as const, icon: Rocket },
  { key: 'trust' as const, icon: Shield },
  { key: 'scale' as const, icon: Heart },
]

const areas = [
  { key: 'engineering', icon: Code },
  { key: 'content', icon: PenLine },
  { key: 'platform', icon: Settings },
  { key: 'success', icon: Headphones },
]
</script>

<template>
  <div class="min-h-screen flex flex-col" data-ja-customizer-target="about">
    <div
      v-if="!isEnabled"
      class="flex-1"
    >
      <PageDisabled 
        :title="(pageTitle as string) || t('theme.janari.pages.about.title')" 
        :message="(getSetting('disabled_page_message') as string)" 
      />
    </div>

    <div
      v-else-if="loading"
      class="flex-1 flex items-center justify-center min-h-[60vh]"
    >
      <div class="flex flex-col items-center gap-4">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary" />
        <span class="text-muted-foreground text-sm">{{ t('theme.janari.common.loading') }}</span>
      </div>
    </div>
    
    <template v-else>
      <div class="flex-1">
        <!-- Visual Builder Content if page was customized in Builder -->
        <BlockRenderer
          v-if="hasBuilderBlocks"
          :blocks="builderBlocks"
          :context="{ post: pageData, site: { name: 'Jejakawan' } }"
        />

        <!-- Classic CMS body only (exclusive — matches Home; empty bind keeps theme) -->
        <SafeHtml
          v-else-if="cmsBody"
          class="container mx-auto px-4 py-16 Jejakawan-content"
          :html="cmsBody"
          mode="publishing"
        />

        <!-- Default Theme Template if no Visual Builder blocks / body -->
        <template v-else>
          <!-- Header -->
          <section
            ref="headerSection"
            class="py-24 bg-gradient-to-b from-primary/10 to-background border-b border-border/50"
          >
            <div class="container mx-auto px-4 text-center">
              <span class="motion-fade text-primary font-bold tracking-wider uppercase text-sm mb-4 block">{{ pageTitle || t('theme.janari.pages.about.sectionLabel') }}</span>
              <h1
                ref="aboutTitle"
                class="text-4xl md:text-6xl font-extrabold mb-6 text-foreground"
              >
                <JanariSplitText :text="pageTitle || 'jejakawan.com'" />
              </h1>
              <p class="motion-fade text-xl text-muted-foreground max-w-2xl mx-auto leading-relaxed font-medium">
                {{ pageSubtitle || t('theme.janari.pages.about.subtitle') }}
              </p>
            </div>
          </section>

        <!-- Mission/Content -->
        <section class="py-20 bg-background">
          <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
              <div
                ref="contentLeft"
                class="space-y-8"
              >
                <div class="motion-fade space-y-4">
                  <h2 class="text-3xl font-bold text-foreground">
                    {{ missionTitle }}
                  </h2>
                  <p class="text-muted-foreground text-lg leading-relaxed">
                    {{ missionP1 }}
                  </p>
                  <p class="text-muted-foreground text-lg leading-relaxed">
                    {{ missionP2 }}
                  </p>
                  <router-link
                    to="/tim"
                    class="inline-flex text-sm font-bold text-primary hover:underline"
                  >
                    {{ teamCta }} →
                  </router-link>
                  <p class="text-xs text-muted-foreground">
                    {{ teamCtaHint }}
                  </p>
                </div>
                            
                <!-- Stats -->
                <div
                  ref="aboutStats"
                  class="grid grid-cols-3 gap-8 pt-8 border-t border-border"
                >
                  <div class="motion-stat-item">
                    <div class="text-4xl font-black text-primary">
                      {{ stat1Value }}
                    </div>
                    <div class="text-xs font-bold text-muted-foreground uppercase mt-2 tracking-widest">
                      {{ stat1Label }}
                    </div>
                  </div>
                  <div class="motion-stat-item">
                    <div class="text-4xl font-black text-primary">
                      {{ stat2Value }}
                    </div>
                    <div class="text-xs font-bold text-muted-foreground uppercase mt-2 tracking-widest">
                      {{ stat2Label }}
                    </div>
                  </div>
                  <div class="motion-stat-item">
                    <div class="text-4xl font-black text-primary">
                      {{ stat3Value }}
                    </div>
                    <div class="text-xs font-bold text-muted-foreground uppercase mt-2 tracking-widest">
                      {{ stat3Label }}
                    </div>
                  </div>
                </div>
              </div>
                        
              <!-- Image Layout -->
              <div
                ref="imageBlock"
                class="relative"
              >
                <div class="absolute -inset-4 bg-primary/10 rounded-3xl -rotate-2" />
                <div class="relative rounded-3xl bg-muted overflow-hidden h-[500px] shadow-2xl border border-border">
                  <img
                    v-if="aboutHeroImage"
                    :src="aboutHeroImage"
                    :alt="pageTitle || t('theme.janari.pages.about.sectionLabel')"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="eager"
                    decoding="async"
                  >
                  <img
                    v-if="aboutTeamImage"
                    :src="aboutTeamImage"
                    :alt="t('theme.janari.pages.about.teamImageAlt')"
                    class="absolute bottom-6 right-6 w-40 h-40 md:w-52 md:h-52 rounded-2xl object-cover border-4 border-background shadow-xl z-10"
                    loading="lazy"
                    decoding="async"
                  >
                  <div
                    v-if="!aboutHeroImage"
                    class="absolute inset-0 flex items-center justify-center text-muted-foreground font-bold text-center px-6"
                  >
                    {{ t('theme.janari.pages.about.imageCaption') }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <AboutOfferingsSection />
        <UserSurveyDynamicSection />
        </template>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import JanariSplitText from '../components/shared/JanariSplitText.vue'
import AboutOfferingsSection from '../components/sections/AboutOfferingsSection.vue'
import UserSurveyDynamicSection from '../components/sections/UserSurveyDynamicSection.vue'
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue'
import type { BlockInstance } from '@/modules/Layout/types/builder'
import { ref, onMounted, nextTick, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue'
import { useRouter } from 'vue-router'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'
import PageDisabled from '../components/shared/PageDisabled.vue'
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion'
import { usePublicPageContent } from '@/modules/Layout/composables/usePublicPageContent'
import { resolveLocalizedPageHtml } from '@/modules/Layout/utils/resolveLocalizedContent'

const { t, locale } = useI18n({ useScope: 'global' })
const { pageData, loading } = usePublicPageContent('about')
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
const { getSetting } = useTheme()
const { localizedString } = useLocalizedThemeSetting()
const router = useRouter()
const { fadeInRight, splitTextRevealSafe, staggerChildren } = useThemeMotion()

const isEnabled = computed(() => getSetting('enable_about', true))
const behavior = computed(() => getSetting('disabled_page_behavior', 'message'))
const pageTitle = computed(() => localizedString('page_about_title') || t('theme.janari.pages.about.title'))
const pageSubtitle = computed(() => localizedString('page_about_subtitle') || t('theme.janari.pages.about.subtitle'))
const missionTitle = computed(() => localizedString('page_about_mission_title') || t('theme.janari.pages.about.missionTitle'))
const missionP1 = computed(() => localizedString('page_about_mission_p1') || t('theme.janari.pages.about.missionP1'))
const missionP2 = computed(() => localizedString('page_about_mission_p2') || t('theme.janari.pages.about.missionP2'))
const teamCta = computed(() => localizedString('page_about_team_cta') || t('theme.janari.pages.about.teamCta'))
const teamCtaHint = computed(() => localizedString('page_about_team_cta_hint') || t('theme.janari.pages.about.teamCtaHint'))
const stat1Value = computed(() => localizedString('page_about_stat1_value') || '20+')
const stat1Label = computed(() => localizedString('page_about_stat1_label') || t('theme.janari.pages.about.statYears'))
const stat2Value = computed(() => localizedString('page_about_stat2_value') || '1k+')
const stat2Label = computed(() => localizedString('page_about_stat2_label') || t('theme.janari.pages.about.statCustomers'))
const stat3Value = computed(() => localizedString('page_about_stat3_value') || '6')
const stat3Label = computed(() => localizedString('page_about_stat3_label') || t('theme.janari.pages.about.statModules'))
const aboutHeroImage = computed(() => {
  const raw = getSetting('page_about_hero')
  return typeof raw === 'string' ? raw.trim() : ''
})
const aboutTeamImage = computed(() => {
  const raw = getSetting('page_about_team_image')
  return typeof raw === 'string' ? raw.trim() : ''
})

const isAnimated = ref(false)

// Template refs for GSAP
const headerSection = ref<HTMLElement>()
const aboutTitle = ref<HTMLElement>()
const contentLeft = ref<HTMLElement>()
const aboutStats = ref<HTMLElement>()
const imageBlock = ref<HTMLElement>()
const initAnimations = () => {
    if (isAnimated.value) return
    isAnimated.value = true

    // Header section
    if (headerSection.value) {
        staggerChildren(headerSection.value, '.motion-fade', { distance: 30, stagger: 0.15 })
    }
    if (aboutTitle.value) {
        splitTextRevealSafe(aboutTitle.value, { delay: 0.2, stagger: 0.05 })
    }

    // Content left stagger
    if (contentLeft.value) {
        staggerChildren(contentLeft.value, '.motion-fade', { distance: 40, stagger: 0.2 })
    }

    // Stats stagger
    if (aboutStats.value) {
        staggerChildren(aboutStats.value, '.motion-stat-item', { distance: 30, stagger: 0.1 })
    }

    // Image parallax from right
    if (imageBlock.value) {
        fadeInRight(imageBlock.value, { distance: 60, duration: 0.9 })
    }

}

watch(loading, async (isLoading) => {
  if (!isLoading && isEnabled.value) {
    await nextTick()
    initAnimations()
  }
})

onMounted(async () => {
  if (!isEnabled.value && behavior.value === 'redirect') {
    router.push('/')
    return
  }

  if (!loading.value && isEnabled.value) {
    await nextTick()
    initAnimations()
  }
})
</script>


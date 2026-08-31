<template>
  <div class="min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col">
      <!-- Visual Builder Content if page was customized in Builder -->
      <BlockRenderer
        v-if="hasBuilderBlocks"
        :blocks="builderBlocks"
        :context="{ post: pageData, site: { name: 'Jejakawan' } }"
      />

      <div
        v-else-if="cmsLoading"
        class="flex-1 flex items-center justify-center py-24 text-sm text-muted-foreground"
      >
        {{ t('theme.janari.common.loading') }}
      </div>

      <!-- Dynamic Content if exists (e.g. from classic editor) -->
      <SafeHtml 
        v-else-if="cmsHtml" 
        class="Jejakawan-content"
        :html="cmsHtml"
        mode="publishing"
      />
        
      <!-- Default Janari home: render immediately to avoid full-viewport layout swap (CLS) -->
      <div
        v-else
        class="flex-1 flex flex-col"
      >
        <section class="flex-1 flex flex-col">
          <!-- LCP-critical: keep eager -->
          <div
            v-show="isSectionActive('hero')"
            data-ja-customizer-target="hero"
          >
            <Hero />
          </div>

          <PluginSlot name="after_hero" class="w-full" />

          <!-- Below-fold: async chunks reduce initial JS + long tasks -->


          <div
            class="below-fold-section"
            data-ja-customizer-target="products"
          >
            <ProductsSection
              v-if="isSectionActive('products') && mountedSections.products"
            />
          </div>

          <div
            class="below-fold-section"
            data-ja-customizer-target="updates"
          >
            <UpdateInformation
              v-if="isSectionActive('updates') && mountedSections.updates"
            />
          </div>

          <div
            class="below-fold-section"
            data-ja-customizer-target="partners"
          >
            <PartnersSection
              v-if="isSectionActive('partners') && mountedSections.partners"
            />
          </div>

          <div
            class="below-fold-section"
            data-ja-customizer-target="testimonials"
          >
            <Testimonials
              v-if="isSectionActive('testimonials') && mountedSections.testimonials"
              :items="testimonialData"
            />
          </div>

          <div
            class="below-fold-section"
            data-ja-customizer-target="cta"
          >
            <CtaSection
              v-if="isSectionActive('cta') && mountedSections.cta"
            />
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { PluginSlot } from '@/shared/components'
import { ref, onMounted, computed, nextTick, onBeforeUnmount, defineAsyncComponent } from 'vue'
import { useI18n } from 'vue-i18n'
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue'
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue'
import type { BlockInstance } from '@/modules/Layout/types/builder'
import { usePublicPageContent } from '@/modules/Layout/composables/usePublicPageContent'
import { resolvePublicPageCmsBody } from '@/modules/Layout/utils/resolveLocalizedContent'
import { pageUsesBuilderOverride } from '@/modules/Layout/composables/useThemePageOverride'

// Above-the-fold (LCP): static import
import Hero from '../components/sections/Hero.vue'

// Removed organization components
const { t, locale } = useI18n({ useScope: 'global' })
const { pageData, loading: cmsLoading } = usePublicPageContent('home')
const cmsHtml = computed(() => resolvePublicPageCmsBody(pageData.value, locale.value))

const builderBlocks = computed<BlockInstance[]>(() => {
  const meta = pageData.value?.meta as Record<string, unknown> | undefined
  const blocks = meta?.builder_blocks || pageData.value?.blocks
  if (Array.isArray(blocks)) {
    return blocks as BlockInstance[]
  }
  return []
})
const hasBuilderBlocks = computed(() => pageUsesBuilderOverride(pageData.value))
const ProductsSection = defineAsyncComponent(() => import('../components/sections/ProductsSection.vue'))
const UpdateInformation = defineAsyncComponent(() => import('../components/sections/UpdateInformation.vue'))
const Testimonials = defineAsyncComponent(() => import('../components/sections/Testimonials.vue'))
const PartnersSection = defineAsyncComponent(() => import('../components/sections/PartnersSection.vue'))
const CtaSection = defineAsyncComponent(() => import('../components/sections/CtaSection.vue'))

// Helpers
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion'
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings'
import { useTheme } from '@/modules/Layout/composables/useTheme';

const { getSetting } = useTheme();
const { ScrollTrigger } = useThemeMotion()

interface Testimonial {
    name: string;
    role: string;
    content: string;
    avatar?: string;
}

const isComponentActive = ref(true);
let sectionObserver: IntersectionObserver | null = null;

const { data: dynamicTestimonials } = useThemeDataBindings('testimonials', 'items')

const DEFAULT_HOME_SECTIONS = ['hero', 'products', 'updates', 'partners', 'testimonials', 'cta'] as const

const activeSections = computed(() => {
  const raw = getSetting('home_sections', DEFAULT_HOME_SECTIONS)
  if (Array.isArray(raw) && raw.length > 0) {
    return raw.map(String)
  }
  return [...DEFAULT_HOME_SECTIONS]
});
const isSectionActive = (section: string) => activeSections.value.includes(section);

const testimonialData = computed<Testimonial[]>(() => dynamicTestimonials.value.map((item: any) => ({ 
    name: item.title, 
    role: item.excerpt || t('theme.janari.pages.home.visionDefault'), 
    content: item.body || item.content,
    avatar: item._raw?.featured_image || item._raw?.thumbnail || undefined
})))

const mountedSections = ref<Record<string, boolean>>({
    products: false,
    updates: false,
    partners: false,
    testimonials: false,
    cta: false,
});

const observeSectionMount = () => {
    // Mount below-fold sections immediately so homepage never looks "cut off"
    // after the hero (IntersectionObserver alone left empty placeholders).
    mountedSections.value = {
        products: true,
        updates: true,
        partners: true,
        testimonials: true,
        cta: true,
    };
};

onMounted(() => {
    isComponentActive.value = true;
    void nextTick().then(() => {
        observeSectionMount();
        setTimeout(() => {
            if (typeof ScrollTrigger !== 'undefined') {
                ScrollTrigger.refresh();
            }
        }, 800);
    });
})

onBeforeUnmount(() => {
    if (sectionObserver) {
        sectionObserver.disconnect();
        sectionObserver = null;
    }
    isComponentActive.value = false
})
</script>

<style scoped>
.Jejakawan-content :deep(p) { margin-bottom: 1rem; }
.below-fold-section {
  content-visibility: auto;
  contain-intrinsic-size: 800px;
}
</style>

<template>
  <div class="min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col">
      <!-- Dynamic Content if exists (e.g. from Jejakawan page editor) -->
      <ThemeSafeHtml 
        v-if="cmsHtml" 
        class="Jejakawan-content"
        :html="cmsHtml"
        mode="Jejakawan"
      />
        
      <!-- Default Janari home: render immediately to avoid full-viewport layout swap (CLS) -->
      <div
        v-else
        class="flex-1 flex flex-col"
      >
        <section class="flex-1 flex flex-col">
          <!-- LCP-critical: keep eager -->
          <Hero v-show="isSectionActive('hero')" />

          <PluginSlot name="after_hero" class="w-full" />

          <!-- Below-fold: async chunks reduce initial JS + long tasks -->


          <div
            ref="productsRef"
            class="below-fold-section"
          >
            <ProductsSection
              v-if="isSectionActive('products') && mountedSections.products"
            />
          </div>

          <div
            ref="updatesRef"
            class="below-fold-section"
          >
            <UpdateInformation
              v-if="isSectionActive('updates') && mountedSections.updates"
            />
          </div>

          <div
            ref="partnersRef"
            class="below-fold-section"
          >
            <PartnersSection
              v-if="isSectionActive('partners') && mountedSections.partners"
            />
          </div>

          <div
            ref="testimonialsRef"
            class="below-fold-section"
          >
            <Testimonials
              v-if="isSectionActive('testimonials') && mountedSections.testimonials"
              :items="testimonialData"
            />
          </div>

          <div
            ref="ctaRef"
            class="below-fold-section"
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
import ThemeSafeHtml from '@/modules/Content/Layout/components/themes/ThemeSafeHtml.vue'
import { usePublicPageContent } from '@/modules/Content/Layout/composables/usePublicPageContent'
import { resolveLocalizedPageHtml } from '@/modules/Content/Layout/utils/resolveLocalizedContent'

// Above-the-fold (LCP): static import
import Hero from '../components/sections/Hero.vue'

// Removed organization components
const { t, locale } = useI18n({ useScope: 'global' })
const { pageData } = usePublicPageContent('home')
const cmsHtml = computed(() => resolveLocalizedPageHtml(pageData.value, locale.value))
const ProductsSection = defineAsyncComponent(() => import('../components/sections/ProductsSection.vue'))
const UpdateInformation = defineAsyncComponent(() => import('../components/sections/UpdateInformation.vue'))
const Testimonials = defineAsyncComponent(() => import('../components/sections/Testimonials.vue'))
const PartnersSection = defineAsyncComponent(() => import('../components/sections/PartnersSection.vue'))
const CtaSection = defineAsyncComponent(() => import('../components/sections/CtaSection.vue'))

// Helpers
import { useThemeMotion } from '@/modules/Content/Layout/composables/useThemeMotion'
import { useThemeDataBindings } from '@/modules/Content/Layout/composables/useThemeDataBindings'
import { useTheme } from '@/modules/Content/Layout/composables/useTheme';

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

const activeSections = computed(() => (getSetting('home_sections') as string[]) || ['hero', 'products', 'updates', 'partners', 'testimonials', 'cta']);
const isSectionActive = (section: string) => activeSections.value.includes(section);

const testimonialData = computed<Testimonial[]>(() => dynamicTestimonials.value.map((item: any) => ({ 
    name: item.title, 
    role: item.excerpt || t('theme.janari.pages.home.visionDefault'), 
    content: item.body || item.content,
    avatar: item._raw?.featured_image || item._raw?.thumbnail || undefined
})))

const productsRef = ref<HTMLElement | null>(null);
const updatesRef = ref<HTMLElement | null>(null);
const partnersRef = ref<HTMLElement | null>(null);
const testimonialsRef = ref<HTMLElement | null>(null);
const ctaRef = ref<HTMLElement | null>(null);

const mountedSections = ref<Record<string, boolean>>({
    products: false,
    updates: false,
    partners: false,
    testimonials: false,
    cta: false,
});

const observeSectionMount = () => {
    if (typeof window === 'undefined' || !('IntersectionObserver' in window)) {
        mountedSections.value = {
            products: true,
            updates: true,
            partners: true,
            testimonials: true,
            cta: true,
        };
        return;
    }

    const map: Array<{ key: keyof typeof mountedSections.value; el: HTMLElement | null }> = [
        { key: 'products', el: productsRef.value },
        { key: 'updates', el: updatesRef.value },
        { key: 'partners', el: partnersRef.value },
        { key: 'testimonials', el: testimonialsRef.value },
        { key: 'cta', el: ctaRef.value },
    ];

    sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const key = (entry.target as HTMLElement).dataset.sectionKey as keyof typeof mountedSections.value | undefined;
            if (!key) return;
            mountedSections.value[key] = true;
            sectionObserver?.unobserve(entry.target);
        });
    }, { rootMargin: '300px 0px' });

    map.forEach(({ key, el }) => {
        if (!el || mountedSections.value[key]) return;
        el.dataset.sectionKey = key;
        sectionObserver?.observe(el);
    });
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

<template>
  <section class="testimonial-section isolate py-16 md:py-20 bg-black relative overflow-hidden border-y border-white/5">
    <div class="container mx-auto px-6 relative z-10">
      <div
        ref="headerRef"
        class="mb-10 md:mb-14"
      >
        <span class="inline-flex items-center px-4 py-2 rounded-full border border-primary/30 bg-primary/5 font-black tracking-[0.5em] uppercase text-[9px] mb-5 text-primary">
          <span class="w-1 h-1 bg-primary rounded-full mr-2" />
          {{ badgeText }}
        </span>
        <h2
          ref="headingRef"
          class="text-4xl md:text-6xl font-heading font-black leading-[0.95] uppercase tracking-tighter text-white"
        >
          <JanariSplitText class="text-white" :text="titleText" />
        </h2>
      </div>

      <div
        ref="gridRef"
        class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-white/10 divide-y md:divide-y-0 md:divide-x divide-white/15 bg-black/40 backdrop-blur-sm"
      >
        <div
          v-if="displayItems.length === 0"
          class="md:col-span-3 p-10 text-center text-white/70 text-sm"
        >
          {{ emptyText }}
        </div>
        <div
          v-for="(item, index) in displayItems"
          :key="index"
          class="motion-testimonial p-8 md:p-10 group hover:bg-primary transition-all duration-500 cubic-bezier(0.37, 0.01, 0, 0.98) cursor-default flex flex-col justify-between border-l border-white/10 first:border-l-0"
        >
          <div>
            <div class="relative mb-8">
              <span class="motion-quote text-6xl text-primary/40 group-hover:text-primary-foreground/25 font-heading leading-none absolute -top-6 -left-3 font-black">“</span>
              <p class="text-white/80 group-hover:text-primary-foreground leading-relaxed italic text-base relative z-10 pt-3">
                {{ item.content }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-5 mt-8 border-t border-white/15 pt-6 group-hover:border-primary-foreground/30 transition-colors duration-500">
            <div class="w-12 h-12 border border-white/20 overflow-hidden group-hover:border-primary-foreground/40 shrink-0 rounded-lg group-hover:shadow-xl group-hover:shadow-primary/20 transition-all duration-500">
              <img
                v-if="item.avatar"
                :src="item.avatar"
                :alt="item.name"
                class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"
                width="56"
                height="56"
                loading="lazy"
                decoding="async"
                sizes="56px"
              >
              <div
                v-else
                class="w-full h-full flex items-center justify-center bg-white/10 text-primary font-black group-hover:bg-primary-foreground group-hover:text-primary"
              >
                {{ item.name.charAt(0) }}
              </div>
            </div>
            <div>
              <h4 class="font-black text-white group-hover:text-primary-foreground text-sm uppercase tracking-tight transition-colors">
                {{ item.name }}
              </h4>
              <p class="text-[10px] text-primary group-hover:text-primary-foreground/80 font-black uppercase tracking-[0.2em] mt-1 transition-colors">
                {{ item.role }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import JanariSplitText from '../shared/JanariSplitText.vue'
import { ref, computed, onMounted, nextTick } from 'vue'
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'

const { t } = useThemeI18n('janari')
const { localizedString } = useLocalizedThemeSetting()

interface Testimonial {
  name: string;
  role: string;
  content: string;
  avatar?: string;
}

const props = defineProps<{
  items: Testimonial[];
}>();

const displayItems = computed(() => props.items?.length ? props.items : [])

const badgeText = computed(() => localizedString('home_testimonials_badge') || t('testimonials.badge'))
const titleText = computed(() => localizedString('home_testimonials_title') || t('testimonials.title'))
const emptyText = computed(() => localizedString('home_testimonials_empty') || t('testimonials.empty'))

const { fadeInUp, staggerChildren, splitTextRevealSafe, motion } = useThemeMotion()

const headerRef = ref<HTMLElement>()
const headingRef = ref<HTMLElement>()
const gridRef = ref<HTMLElement>()
const isAnimated = ref(false)

onMounted(() => {
  if (isAnimated.value) return
  isAnimated.value = true

  const init = async () => {
    await nextTick()
    if (!gridRef.value) return

    // Header
    if (headerRef.value) {
      fadeInUp(headerRef.value, { distance: 30 })
    }
    if (headingRef.value) {
      splitTextRevealSafe(headingRef.value, { delay: 0.2, stagger: 0.04 })
    }

    // Cards stagger in
    if (gridRef.value) {
      staggerChildren(gridRef.value, '.motion-testimonial', {
        distance: 50,
        stagger: 0.15,
        duration: 0.8,
      })

      // Quote mark scale animation
      const quotes = gridRef.value.querySelectorAll('.motion-quote')
      quotes.forEach((q, i) => {
        motion.set(q, { scale: 0, opacity: 0 })
        motion.to(q, {
          scale: 1,
          opacity: 1,
          duration: 0.6,
          delay: 0.8 + i * 0.15,
          ease: 'back.out(2)',
          scrollTrigger: {
            trigger: gridRef.value!,
            start: 'top 85%',
          },
        })
      })
    }
  }
  init()
})
</script>

<style scoped>
.testimonial-section {
  background-color: #000 !important;
}
</style>

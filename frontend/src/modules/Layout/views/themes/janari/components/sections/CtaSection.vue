<template>
  <section class="py-14 md:py-16 bg-muted/20 relative overflow-hidden">
    <div class="container mx-auto px-6">
      <div
        ref="cardRef"
        class="max-w-7xl mx-auto px-6 py-10 md:px-12 md:py-14 border border-border bg-card/50 backdrop-blur-3xl relative overflow-hidden"
      >
        <div class="relative z-10 flex flex-col items-center text-center">
          <span class="inline-flex items-center px-4 py-1.5 bg-background/80 backdrop-blur-md border border-primary/20 text-[9px] font-black tracking-[0.5em] uppercase text-primary mb-5 rounded-full">
            <span class="w-1 h-1 bg-primary rounded-full mr-2" />
            {{ badgeText }}
          </span>
          <h2
            ref="titleRef"
            class="text-4xl md:text-6xl lg:text-7xl font-heading font-black mb-5 leading-[0.95] uppercase tracking-tighter text-foreground"
          >
            <JanariSplitText :text="titleText" />
          </h2>
          <p
            v-if="subtitleText"
            class="max-w-2xl text-foreground/60 mb-7 text-base leading-relaxed"
          >
            {{ subtitleText }}
          </p>
          <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4">
            <router-link
              :to="primaryUrl"
              class="px-8 py-3 text-xs font-bold tracking-[0.5px] uppercase bg-primary text-primary-foreground rounded-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300"
            >
              {{ buttonText }}
            </router-link>
            <router-link
              :to="secondaryUrl"
              class="px-8 py-3 text-xs font-bold tracking-[0.5px] uppercase border border-border rounded-lg hover:bg-muted/50 transition-all"
            >
              {{ secondaryButtonText }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import JanariSplitText from '../shared/JanariSplitText.vue'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion'

const { t } = useI18n({ useScope: 'global' })
const { localizedString } = useLocalizedThemeSetting()
const { getSetting } = useTheme()
const { scaleReveal, splitTextRevealSafe } = useThemeMotion()

const cardRef = ref<HTMLElement>()
const titleRef = ref<HTMLElement>()

const badgeText = computed(() => localizedString('cta_badge') || t('theme.janari.cta.badgeDefault'))
const titleText = computed(() => localizedString('cta_title') || t('theme.janari.cta.titleDefault'))
const subtitleText = computed(() => localizedString('cta_subtitle') || '')
const buttonText = computed(() => localizedString('cta_button_text') || t('theme.janari.cta.buttonDefault'))
const secondaryButtonText = computed(() => localizedString('cta_secondary_text') || t('theme.janari.cta.pricingDefault'))
const primaryUrl = computed(() => {
  const raw = getSetting('cta_button_url', '/contact')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/contact'
})
const secondaryUrl = computed(() => {
  const raw = getSetting('cta_secondary_url', '/pricing')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/pricing'
})

onMounted(() => {
    if (cardRef.value) scaleReveal(cardRef.value)
    if (titleRef.value) splitTextRevealSafe(titleRef.value, { delay: 0.2, stagger: 0.05 })
})
</script>

<template>
  <section class="py-40 bg-muted/20 relative overflow-hidden">
    <div class="container mx-auto px-6">
      <div
        ref="cardRef"
        class="max-w-7xl mx-auto p-12 md:p-32 border border-border bg-card/50 backdrop-blur-3xl relative overflow-hidden"
      >
        <div class="relative z-10 flex flex-col items-center text-center">
          <span class="inline-flex items-center px-4 py-2 bg-background/80 backdrop-blur-md border border-primary/20 text-[9px] font-black tracking-[0.5em] uppercase text-primary mb-12 rounded-full">
            <span class="w-1 h-1 bg-primary rounded-full mr-2" />
            {{ badgeText }}
          </span>
          <h2
            ref="titleRef"
            class="text-5xl md:text-9xl font-heading font-black mb-16 leading-[0.85] uppercase tracking-tighter text-foreground"
          >
            <JanariSplitText :text="titleText" />
          </h2>
          <p
            v-if="subtitleText"
            class="max-w-2xl text-foreground/60 mb-16 text-lg leading-relaxed"
          >
            {{ subtitleText }}
          </p>
          <div class="flex flex-col sm:flex-row items-center gap-6">
            <router-link
              to="/contact"
              class="px-10 py-4 text-xs font-bold tracking-[0.5px] uppercase bg-primary text-primary-foreground rounded-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300"
            >
              {{ buttonText }}
            </router-link>
            <router-link
              to="/pricing"
              class="px-10 py-4 text-xs font-bold tracking-[0.5px] uppercase border border-border rounded-lg hover:bg-muted/50 transition-all"
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
import { useLocalizedThemeSetting } from '@/modules/Content/Layout/composables/useLocalizedThemeSetting'
import { useThemeMotion } from '@/modules/Content/Layout/composables/useThemeMotion'

const { t } = useI18n({ useScope: 'global' })
const { localizedString } = useLocalizedThemeSetting()
const { scaleReveal, splitTextRevealSafe } = useThemeMotion()

const cardRef = ref<HTMLElement>()
const titleRef = ref<HTMLElement>()

const badgeText = computed(() => localizedString('cta_badge') || t('theme.janari.cta.badgeDefault'))
const titleText = computed(() => localizedString('cta_title') || t('theme.janari.cta.titleDefault'))
const subtitleText = computed(() => localizedString('cta_subtitle') || '')
const buttonText = computed(() => localizedString('cta_button_text') || t('theme.janari.cta.buttonDefault'))
const secondaryButtonText = computed(() => localizedString('cta_secondary_text') || t('theme.janari.cta.pricingDefault'))

onMounted(() => {
    if (cardRef.value) scaleReveal(cardRef.value)
    if (titleRef.value) splitTextRevealSafe(titleRef.value, { delay: 0.2, stagger: 0.05 })
})
</script>

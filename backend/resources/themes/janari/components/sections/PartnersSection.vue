<template>
  <section
    class="py-24 bg-background border-b border-border overflow-hidden"
  >
    <div class="container mx-auto px-6 text-center">
      <h2 class="text-4xl font-heading font-black text-foreground uppercase tracking-tighter mb-20">
        {{ titleText }}
      </h2>
      <div
        ref="marqueeRef"
        class="flex items-center gap-24 whitespace-nowrap opacity-40 hover:opacity-100 transition-opacity duration-700 cubic-bezier(0.37, 0.01, 0, 0.98)"
      >
        <!-- Duplicate items natively for seamless marquee loop -->
        <div
          v-for="(partner, idx) in [...items, ...items]"
          :key="partner.name + idx"
          class="grayscale hover:grayscale-0 transition-all duration-500 cubic-bezier(0.37, 0.01, 0, 0.98) cursor-pointer transform hover:scale-110"
        >
          <img
            v-if="partner.image"
            :src="partner.image"
            :alt="partner.name"
            class="h-12 object-contain"
            width="192"
            height="48"
            loading="lazy"
            decoding="async"
            sizes="192px"
          >
          <span
            v-else
            class="text-2xl font-black text-foreground/40"
          >{{ partner.name }}</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useLocalizedThemeSetting } from '@/modules/Content/Layout/composables/useLocalizedThemeSetting'
import { useTheme } from '@/modules/Content/Layout/composables/useTheme'
import { useThemeMotion } from '@/modules/Content/Layout/composables/useThemeMotion'
import { useThemeDataBindings } from '@/modules/Content/Layout/composables/useThemeDataBindings'

const { t } = useI18n({ useScope: 'global' })
const { localizedString } = useLocalizedThemeSetting()
const { getSetting } = useTheme()
const { marquee } = useThemeMotion()

const marqueeRef = ref<HTMLElement>()
const { data: dynamicItems } = useThemeDataBindings('partners', 'partners')

const titleText = computed(() => localizedString('partners_title') || t('theme.janari.partners.titleDefault'))
const marqueeSpeed = computed(() => parseInt(String(getSetting('partners_marquee_speed', 25)), 10))

const items = computed(() => dynamicItems.value.map((item: any) => ({ 
    name: item.title, 
    image: item._raw?.featured_image || item._raw?.thumbnail 
})))

onMounted(() => {
    if (marqueeRef.value) marquee(marqueeRef.value, { speed: marqueeSpeed.value })
})
</script>

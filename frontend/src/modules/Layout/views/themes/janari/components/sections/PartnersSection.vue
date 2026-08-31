<template>
  <section
    class="py-14 md:py-16 bg-background border-b border-border overflow-hidden"
  >
    <div class="container mx-auto px-6 text-center">
      <h2 class="text-2xl md:text-3xl font-heading font-black text-foreground uppercase tracking-tighter mb-8 md:mb-10">
        {{ titleText }}
      </h2>
      <div
        v-if="items.length === 0"
        class="text-sm text-muted-foreground py-6 border border-dashed border-border rounded-xl"
      >
        {{ emptyText }}
      </div>
      <div
        v-else
        ref="marqueeRef"
        class="flex items-center gap-12 md:gap-16 whitespace-nowrap opacity-80 hover:opacity-100 transition-opacity duration-700"
      >
        <div
          v-for="(partner, idx) in [...items, ...items]"
          :key="partner.name + idx"
          class="grayscale hover:grayscale-0 transition-all duration-500 cursor-pointer transform hover:scale-110"
        >
          <img
            v-if="partner.image"
            :src="partner.image"
            :alt="partner.name"
            class="h-10 md:h-12 object-contain"
            width="192"
            height="48"
            loading="lazy"
            decoding="async"
            sizes="192px"
          >
          <span
            v-else
            class="text-xl md:text-2xl font-black text-foreground/70 tracking-tight"
          >{{ partner.name }}</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion'
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings'

const { t } = useI18n({ useScope: 'global' })
const { localizedString } = useLocalizedThemeSetting()
const { getSetting } = useTheme()
const { marquee } = useThemeMotion()

const marqueeRef = ref<HTMLElement>()
const { data: dynamicItems } = useThemeDataBindings('partners', 'partners')

const titleText = computed(() => localizedString('partners_title') || t('theme.janari.partners.titleDefault'))
const emptyText = computed(() => localizedString('partners_empty') || t('theme.janari.partners.empty'))
const marqueeSpeed = computed(() => parseInt(String(getSetting('partners_marquee_speed', 25)), 10))

const items = computed(() =>
  dynamicItems.value
    .map((item: any) => ({
      name: item.title,
      image: item._raw?.featured_image || item._raw?.thumbnail,
    }))
    .filter((p: { name?: string }) => Boolean(p.name)),
)

onMounted(() => {
  if (marqueeRef.value && items.value.length > 0) {
    marquee(marqueeRef.value, { speed: marqueeSpeed.value })
  }
})
</script>

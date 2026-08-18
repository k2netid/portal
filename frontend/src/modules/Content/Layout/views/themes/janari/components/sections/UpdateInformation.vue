<template>
  <section class="py-24 bg-background relative border-y border-border overflow-hidden">
    <!-- Section Header Box -->
    <div class="container mx-auto px-6 mb-16 flex justify-center">
      <div class="px-6 py-3 border border-primary/20 bg-primary/5 backdrop-blur-md rounded-full relative group overflow-hidden">
        <div class="absolute inset-0 bg-primary/10 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-500 cubic-bezier(0.37, 0.01, 0, 0.98)" />
        <div class="flex items-center gap-3 relative z-10">
          <span class="w-1 h-1 bg-primary rounded-full" />
          <h2 class="text-xs md:text-sm font-bold tracking-[0.5em] uppercase text-primary">
            {{ t('theme.janari.updateInfo.sectionTitle') }}
          </h2>
        </div>
      </div>
    </div>

    <!-- 3-Column Layout -->
    <div class="container mx-auto px-6 lg:px-4">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-0 border-x border-border/50 divide-y lg:divide-y-0 lg:divide-x divide-border/50">
        <!-- COLUMN 1: PENGUMUMAN (Announcements) -->
        <div
          ref="col1"
          class="relative py-16 lg:pl-32 lg:pr-10 group/col border-border/50"
        >
          <!-- Vertical Label (Absolute Gutter System) -->
          <div class="absolute top-16 left-8 hidden lg:block pointer-events-none">
            <span class="inline-block [writing-mode:vertical-rl] rotate-180 whitespace-nowrap text-xs md:text-sm font-black tracking-[0.3em] uppercase text-foreground/65 group-hover/col:text-foreground transition-colors duration-500 cubic-bezier(0.37, 0.01, 0, 0.98)">
              {{ t('theme.janari.updateInfo.columnAnnouncements') }}
            </span>
          </div>
          <!-- Content -->
          <div class="flex-1 mt-2">
            <div class="space-y-1">
              <router-link 
                v-for="(item, idx) in announcements" 
                :key="'ann-'+idx" 
                :to="item?.url || '#'"
                class="block group/item info-item w-full p-4 md:p-6 md:-mx-6 border border-transparent transition-all duration-500 cubic-bezier(0.37, 0.01, 0, 0.98) hover:border-primary/60 hover:bg-primary/5 hover:shadow-[0_0_30px_hsl(var(--primary)/0.15)] relative z-10"
              >
                <div class="flex items-center justify-between gap-4 mb-3">
                  <div class="flex items-center gap-3">
                    <span class="w-2 h-px bg-foreground/30 group-hover/item:w-4 group-hover/item:bg-primary transition-all duration-500" />
                    <span class="text-[10px] font-bold tracking-[0.15em] text-foreground/80 group-hover/item:text-foreground transition-colors uppercase">{{ t('theme.janari.common.dateUpdate', { date: item?.date || '' }) }}</span>
                  </div>
                  <span class="text-[9px] px-3 py-1 border border-border text-foreground/85 uppercase tracking-[0.2em] shrink-0 group-hover/item:border-primary group-hover/item:text-primary group-hover/item:shadow-[0_0_10px_hsl(var(--primary)/0.2)] transition-all duration-300 cubic-bezier(0.37, 0.01, 0, 0.98)">
                    {{ item?.category || t('theme.janari.common.info') }}
                  </span>
                </div>
                <div class="flex items-center justify-between gap-6 pl-5 group-hover/item:pl-7 transition-all duration-500">
                  <h3 class="flex-1 text-sm md:text-base font-medium text-foreground/70 group-hover/item:text-foreground leading-relaxed line-clamp-2">
                    {{ item?.title || '' }}
                  </h3>
                  <span class="text-foreground/30 group-hover/item:text-primary group-hover/item:translate-x-2 transition-all duration-500 shrink-0">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M9 5l7 7-7 7"
                      />
                    </svg>
                  </span>
                </div>
              </router-link>
            </div>
            <!-- View More -->
            <div class="mt-8">
              <router-link
                to="/blog"
                class="inline-flex items-center gap-6 px-8 py-3 border border-border bg-background text-[9px] font-black tracking-[0.3em] uppercase text-foreground hover:text-foreground hover:border-primary hover:bg-primary/10 hover:shadow-[0_0_20px_hsl(var(--primary)/0.15)] transition-all duration-500 group/btn relative z-10"
              >
                {{ t('theme.janari.common.viewAll') }}
                <span class="w-6 h-px bg-foreground/30 group-hover/btn:bg-primary transition-all duration-500" />
              </router-link>
            </div>
          </div>
        </div>

        <!-- COLUMN 2: AGENDA (Calendar/Events) -->
        <div
          ref="col2"
          class="relative py-16 lg:pl-32 lg:pr-10 group/col border-border/50"
        >
          <div class="absolute top-16 left-8 hidden lg:block pointer-events-none">
            <span class="inline-block [writing-mode:vertical-rl] rotate-180 whitespace-nowrap text-xs md:text-sm font-black tracking-[0.3em] uppercase text-foreground/65 group-hover/col:text-foreground transition-colors duration-500">
              {{ t('theme.janari.updateInfo.columnAgenda') }}
            </span>
          </div>
          <div class="flex-1 mt-2">
            <div class="space-y-1">
              <router-link 
                v-for="(item, idx) in agenda" 
                :key="'age-'+idx" 
                :to="item?.url || '#'"
                class="block group/item info-item w-full p-4 md:p-6 md:-mx-6 border border-transparent transition-all duration-500 cubic-bezier(0.37, 0.01, 0, 0.98) hover:border-primary/60 hover:bg-primary/5 hover:shadow-[0_0_30px_hsl(var(--primary)/0.15)] relative z-10"
              >
                <div class="flex items-center justify-between gap-4 mb-3">
                  <div class="flex items-center gap-3">
                    <span class="w-2 h-px bg-foreground/30 group-hover/item:w-4 group-hover/item:bg-primary transition-all duration-500" />
                    <span class="text-[10px] font-bold tracking-[0.15em] text-foreground/80 group-hover/item:text-foreground transition-colors uppercase">{{ item?.date || '' }} event</span>
                  </div>
                  <span class="text-[9px] px-3 py-1 border border-border text-foreground/85 uppercase tracking-[0.2em] shrink-0 group-hover/item:border-primary group-hover/item:text-primary group-hover/item:shadow-[0_0_10px_hsl(var(--primary)/0.2)] transition-all duration-300 cubic-bezier(0.37, 0.01, 0, 0.98)">
                    {{ item?.category || t('theme.janari.common.info') }}
                  </span>
                </div>
                <div class="flex items-center justify-between gap-6 pl-5 group-hover/item:pl-7 transition-all duration-500">
                  <h3 class="flex-1 text-sm md:text-base font-medium text-foreground/70 group-hover/item:text-foreground leading-relaxed line-clamp-2">
                    {{ item?.title || '' }}
                  </h3>
                  <span class="text-foreground/30 group-hover/item:text-primary group-hover/item:translate-x-2 transition-all duration-500 shrink-0">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M9 5l7 7-7 7"
                      />
                    </svg>
                  </span>
                </div>
              </router-link>
            </div>
            <div class="mt-8">
              <router-link
                to="/blog"
                class="inline-flex items-center gap-6 px-8 py-3 border border-border bg-background text-[9px] font-black tracking-[0.3em] uppercase text-foreground hover:text-foreground hover:border-primary hover:bg-primary/10 hover:shadow-[0_0_20px_hsl(var(--primary)/0.15)] transition-all duration-500 group/btn relative z-10"
              >
                {{ t('theme.janari.common.viewAll') }}
                <span class="w-6 h-px bg-foreground/30 group-hover/btn:bg-primary transition-all duration-500" />
              </router-link>
            </div>
          </div>
        </div>

        <!-- COLUMN 3: LIBUR (Holidays) -->
        <div
          ref="col3"
          class="relative py-16 lg:pl-32 lg:pr-10 group/col border-border/50"
        >
          <div class="absolute top-16 left-8 hidden lg:block pointer-events-none">
            <span class="inline-block [writing-mode:vertical-rl] rotate-180 whitespace-nowrap text-xs md:text-sm font-black tracking-[0.3em] uppercase text-foreground/65 group-hover/col:text-foreground transition-colors duration-500">
              {{ t('theme.janari.updateInfo.columnHolidays') }}
            </span>
          </div>
          <div class="flex-1 mt-2">
            <div class="space-y-1">
              <router-link 
                v-for="(item, idx) in holidays" 
                :key="'hol-'+idx" 
                :to="item?.url || '#'"
                class="block group/item info-item w-full p-4 md:p-6 md:-mx-6 border border-transparent transition-all duration-500 cubic-bezier(0.37, 0.01, 0, 0.98) hover:border-primary/60 hover:bg-primary/5 hover:shadow-[0_0_30px_hsl(var(--primary)/0.15)] relative z-10"
              >
                <div class="flex items-center justify-between gap-4 mb-3">
                  <div class="flex items-center gap-3">
                    <span class="w-2 h-px bg-foreground/30 group-hover/item:w-4 group-hover/item:bg-primary transition-all duration-500" />
                    <span class="text-[10px] font-bold tracking-[0.15em] text-foreground/80 group-hover/item:text-foreground transition-colors uppercase">{{ t('theme.janari.common.dateDayOff', { date: item?.date || '' }) }}</span>
                  </div>
                  <span class="text-[9px] px-3 py-1 border border-border text-foreground/85 uppercase tracking-[0.2em] shrink-0 group-hover/item:border-primary group-hover/item:text-primary group-hover/item:shadow-[0_0_10px_hsl(var(--primary)/0.2)] transition-all duration-300 cubic-bezier(0.37, 0.01, 0, 0.98)">
                    {{ item?.category || t('theme.janari.common.info') }}
                  </span>
                </div>
                <div class="flex items-center justify-between gap-6 pl-5 group-hover/item:pl-7 transition-all duration-500">
                  <h3 class="flex-1 text-sm md:text-base font-medium text-foreground/70 group-hover/item:text-foreground leading-relaxed line-clamp-2">
                    {{ item?.title || '' }}
                  </h3>
                  <span class="text-foreground/30 group-hover/item:text-primary group-hover/item:translate-x-2 transition-all duration-500 shrink-0">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M9 5l7 7-7 7"
                      />
                    </svg>
                  </span>
                </div>
              </router-link>
            </div>
            <div class="mt-8">
              <router-link
                to="/blog"
                class="inline-flex items-center gap-6 px-8 py-3 border border-border bg-background text-[9px] font-black tracking-[0.3em] uppercase text-foreground hover:text-foreground hover:border-primary hover:bg-primary/10 hover:shadow-[0_0_20px_hsl(var(--primary)/0.15)] transition-all duration-500 group/btn relative z-10"
              >
                {{ t('theme.janari.common.viewAll') }}
                <span class="w-6 h-px bg-foreground/30 group-hover/btn:bg-primary transition-all duration-500" />
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useThemeMotion } from '@/modules/Content/Layout/composables/useThemeMotion'
import { useThemeComponentBindings } from '@/modules/Content/Layout/composables/useThemeDataBindings'

// Dynamic Data Integration
const { t } = useI18n()
const { slots: dynamicSlots, hasAnyBinding } = useThemeComponentBindings('info')

const demoItem = (prefix: string, index: number, url: string) => ({
    date: t(`theme.janari.demo.${prefix}${index}.date`),
    category: t(`theme.janari.demo.${prefix}${index}.category`),
    title: t(`theme.janari.demo.${prefix}${index}.title`),
    url,
})

const mockAnnouncements = [0, 1, 2].map((i) => demoItem('announcement', i, '/blog'))
const mockAgenda = [0, 1, 2].map((i) => demoItem('agenda', i, '/blog'))
const mockHolidays = [0, 1, 2].map((i) => demoItem('holiday', i, '/blog'))

// Helper to transform API data to display format
function transformApiData(items: any[], defaultUrl: string) {
    if (!Array.isArray(items)) return []
    return items.map(item => {
        if (!item) return null
        const raw = item._raw || item
        return {
            date: raw?.published_at ? new Date(raw.published_at).toLocaleDateString('en-CA').replace(/-/g, '.') : (item?.date || ''),
            category: raw?.category?.name || raw?.category_name || item?.category || t('theme.janari.common.info'),
            title: raw?.title || item?.title || '',
            url: raw?.slug ? `/blog/${raw.slug}` : (item?.url || defaultUrl),
        }
    }).filter(i => i !== null)
}

// Computed data that prioritizes dynamic bindings over mock data
const announcements = computed(() => {
    if (hasAnyBinding.value && dynamicSlots.value.announcements && dynamicSlots.value.announcements.length > 0) {
        return transformApiData(dynamicSlots.value.announcements, '/pengumuman')
    }
    return mockAnnouncements
})

const agenda = computed(() => {
    if (hasAnyBinding.value && dynamicSlots.value.agenda && dynamicSlots.value.agenda.length > 0) {
        return transformApiData(dynamicSlots.value.agenda, '/agenda')
    }
    return mockAgenda
})

const holidays = computed(() => {
    if (hasAnyBinding.value && dynamicSlots.value.holidays && dynamicSlots.value.holidays.length > 0) {
        return transformApiData(dynamicSlots.value.holidays, '/agenda')
    }
    return mockHolidays
})

// GSAP
const { staggerChildren } = useThemeMotion()
const col1 = ref<HTMLElement>()
const col2 = ref<HTMLElement>()
const col3 = ref<HTMLElement>()
const isComponentActive = ref(true)
const isAnimated = ref(false)

import { onBeforeUnmount, watch } from 'vue'
onBeforeUnmount(() => {
    isComponentActive.value = false
})

const runAnimations = async () => {
    if (!isComponentActive.value || isAnimated.value) return
    isAnimated.value = true
    await nextTick()
    
    if (col1.value) {
        staggerChildren(col1.value, '.info-item', { distance: 30, stagger: 0.1, delay: 0.2 })
    }
    if (col2.value) {
        staggerChildren(col2.value, '.info-item', { distance: 30, stagger: 0.1, delay: 0.4 })
    }
    if (col3.value) {
        staggerChildren(col3.value, '.info-item', { distance: 30, stagger: 0.1, delay: 0.6 })
    }
}

onMounted(() => {
    runAnimations()
})

// Re-run if dynamic data arrives (only if not already animated)
watch([announcements, agenda, holidays], (newVal, oldVal) => {
    // Simple length check or reference change is often enough for these arrays
    if (newVal.some((arr, i) => arr.length !== oldVal?.[i]?.length)) {
        isAnimated.value = false;
        runAnimations()
    }
}, { deep: true })
</script>

<style scoped>
/* Scoped adjustments if needed */
</style>

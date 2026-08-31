<template>
  <section class="py-14 md:py-16 bg-background relative border-y border-border overflow-hidden">
    <!-- Section Header Box -->
    <div class="container mx-auto px-6 mb-8 md:mb-10 flex justify-center">
      <div class="px-6 py-2.5 border border-primary/20 bg-primary/5 backdrop-blur-md rounded-full relative group overflow-hidden">
        <div class="absolute inset-0 bg-primary/10 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-500 cubic-bezier(0.37, 0.01, 0, 0.98)" />
        <div class="flex items-center gap-3 relative z-10">
          <span class="w-1 h-1 bg-primary rounded-full" />
          <h2 class="text-xs md:text-sm font-bold tracking-[0.5em] uppercase text-primary">
            {{ sectionTitle }}
          </h2>
        </div>
      </div>
    </div>

    <!-- 3-Column Layout -->
    <div class="container mx-auto px-6 lg:px-4">
      <div
        v-if="loading"
        class="grid grid-cols-1 lg:grid-cols-3 gap-6"
      >
        <div
          v-for="i in 3"
          :key="'sk-'+i"
          class="h-48 rounded-xl bg-muted/40 animate-pulse border border-border/40"
        />
      </div>

      <div
        v-else-if="!hasAnyItems"
        class="text-center py-10 text-sm text-muted-foreground border border-dashed border-border rounded-xl"
      >
        {{ emptyText }}
        <router-link
          :to="viewAllUrl"
          class="block mt-3 text-primary font-semibold hover:underline"
        >
          {{ viewAllText }}
        </router-link>
      </div>

      <div
        v-else
        class="grid grid-cols-1 lg:grid-cols-3 gap-0 border-x border-border/50 divide-y lg:divide-y-0 lg:divide-x divide-border/50"
      >
        <!-- COLUMN 1 -->
        <div
          ref="col1"
          class="relative py-8 md:py-10 lg:pl-24 lg:pr-8 group/col border-border/50"
        >
          <div class="absolute top-8 left-6 hidden lg:block pointer-events-none">
            <span class="inline-block [writing-mode:vertical-rl] rotate-180 whitespace-nowrap text-xs font-black tracking-[0.3em] uppercase text-foreground/70 group-hover/col:text-foreground transition-colors duration-500">
              {{ colAnnouncements }}
            </span>
          </div>
          <div class="flex-1 mt-2">
            <p class="lg:hidden text-[10px] font-black tracking-[0.3em] uppercase text-foreground/70 mb-4">
              {{ colAnnouncements }}
            </p>
            <div class="space-y-1">
              <router-link
                v-for="(item, idx) in announcements"
                :key="'ann-'+idx"
                :to="item?.url || '#'"
                class="block group/item info-item w-full p-4 md:p-5 md:-mx-4 border border-transparent transition-all duration-500 hover:border-primary/60 hover:bg-primary/5 relative z-10"
              >
                <div class="flex items-center justify-between gap-4 mb-2">
                  <div class="flex items-center gap-3">
                    <span class="w-2 h-px bg-foreground/50 group-hover/item:w-4 group-hover/item:bg-primary transition-all duration-500" />
                    <span class="text-[10px] font-bold tracking-[0.15em] text-foreground/80 group-hover/item:text-foreground transition-colors uppercase">{{ item?.date || '' }}</span>
                  </div>
                  <span class="text-[9px] px-2.5 py-1 border border-border text-foreground/80 uppercase tracking-[0.2em] shrink-0 group-hover/item:border-primary group-hover/item:text-primary transition-all">
                    {{ item?.category || t('common.info') }}
                  </span>
                </div>
                <div class="flex items-center justify-between gap-4 pl-4 group-hover/item:pl-6 transition-all duration-500">
                  <h3 class="flex-1 text-sm md:text-base font-medium text-foreground/85 group-hover/item:text-foreground leading-relaxed line-clamp-2">
                    {{ item?.title || '' }}
                  </h3>
                  <span class="text-foreground/55 group-hover/item:text-primary group-hover/item:translate-x-1 transition-all duration-500 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" /></svg>
                  </span>
                </div>
              </router-link>
              <p
                v-if="announcements.length === 0"
                class="text-sm text-muted-foreground px-4 py-6"
              >
                —
              </p>
            </div>
            <div class="mt-6">
              <router-link
                :to="viewAllUrl"
                class="inline-flex items-center gap-4 px-6 py-2.5 border border-border bg-background text-[9px] font-black tracking-[0.3em] uppercase text-foreground hover:border-primary hover:bg-primary/10 transition-all duration-500 group/btn relative z-10"
              >
                {{ viewAllText }}
                <span class="w-5 h-px bg-foreground/50 group-hover/btn:bg-primary transition-all duration-500" />
              </router-link>
            </div>
          </div>
        </div>

        <!-- COLUMN 2 -->
        <div
          ref="col2"
          class="relative py-8 md:py-10 lg:pl-24 lg:pr-8 group/col border-border/50"
        >
          <div class="absolute top-8 left-6 hidden lg:block pointer-events-none">
            <span class="inline-block [writing-mode:vertical-rl] rotate-180 whitespace-nowrap text-xs font-black tracking-[0.3em] uppercase text-foreground/70 group-hover/col:text-foreground transition-colors duration-500">
              {{ colAgenda }}
            </span>
          </div>
          <div class="flex-1 mt-2">
            <p class="lg:hidden text-[10px] font-black tracking-[0.3em] uppercase text-foreground/70 mb-4">
              {{ colAgenda }}
            </p>
            <div class="space-y-1">
              <router-link
                v-for="(item, idx) in agenda"
                :key="'age-'+idx"
                :to="item?.url || '#'"
                class="block group/item info-item w-full p-4 md:p-5 md:-mx-4 border border-transparent transition-all duration-500 hover:border-primary/60 hover:bg-primary/5 relative z-10"
              >
                <div class="flex items-center justify-between gap-4 mb-2">
                  <div class="flex items-center gap-3">
                    <span class="w-2 h-px bg-foreground/50 group-hover/item:w-4 group-hover/item:bg-primary transition-all duration-500" />
                    <span class="text-[10px] font-bold tracking-[0.15em] text-foreground/80 group-hover/item:text-foreground transition-colors uppercase">{{ item?.date || '' }}</span>
                  </div>
                  <span class="text-[9px] px-2.5 py-1 border border-border text-foreground/80 uppercase tracking-[0.2em] shrink-0 group-hover/item:border-primary group-hover/item:text-primary transition-all">
                    {{ item?.category || t('common.info') }}
                  </span>
                </div>
                <div class="flex items-center justify-between gap-4 pl-4 group-hover/item:pl-6 transition-all duration-500">
                  <h3 class="flex-1 text-sm md:text-base font-medium text-foreground/85 group-hover/item:text-foreground leading-relaxed line-clamp-2">
                    {{ item?.title || '' }}
                  </h3>
                  <span class="text-foreground/55 group-hover/item:text-primary group-hover/item:translate-x-1 transition-all duration-500 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" /></svg>
                  </span>
                </div>
              </router-link>
            </div>
            <div class="mt-6">
              <router-link
                :to="viewAllUrl"
                class="inline-flex items-center gap-4 px-6 py-2.5 border border-border bg-background text-[9px] font-black tracking-[0.3em] uppercase text-foreground hover:border-primary hover:bg-primary/10 transition-all duration-500 group/btn relative z-10"
              >
                {{ viewAllText }}
                <span class="w-5 h-px bg-foreground/50 group-hover/btn:bg-primary transition-all duration-500" />
              </router-link>
            </div>
          </div>
        </div>

        <!-- COLUMN 3 -->
        <div
          ref="col3"
          class="relative py-8 md:py-10 lg:pl-24 lg:pr-8 group/col border-border/50"
        >
          <div class="absolute top-8 left-6 hidden lg:block pointer-events-none">
            <span class="inline-block [writing-mode:vertical-rl] rotate-180 whitespace-nowrap text-xs font-black tracking-[0.3em] uppercase text-foreground/70 group-hover/col:text-foreground transition-colors duration-500">
              {{ colHolidays }}
            </span>
          </div>
          <div class="flex-1 mt-2">
            <p class="lg:hidden text-[10px] font-black tracking-[0.3em] uppercase text-foreground/70 mb-4">
              {{ colHolidays }}
            </p>
            <div class="space-y-1">
              <router-link
                v-for="(item, idx) in holidays"
                :key="'hol-'+idx"
                :to="item?.url || '#'"
                class="block group/item info-item w-full p-4 md:p-5 md:-mx-4 border border-transparent transition-all duration-500 hover:border-primary/60 hover:bg-primary/5 relative z-10"
              >
                <div class="flex items-center justify-between gap-4 mb-2">
                  <div class="flex items-center gap-3">
                    <span class="w-2 h-px bg-foreground/50 group-hover/item:w-4 group-hover/item:bg-primary transition-all duration-500" />
                    <span class="text-[10px] font-bold tracking-[0.15em] text-foreground/80 group-hover/item:text-foreground transition-colors uppercase">{{ item?.date || '' }}</span>
                  </div>
                  <span class="text-[9px] px-2.5 py-1 border border-border text-foreground/80 uppercase tracking-[0.2em] shrink-0 group-hover/item:border-primary group-hover/item:text-primary transition-all">
                    {{ item?.category || t('common.info') }}
                  </span>
                </div>
                <div class="flex items-center justify-between gap-4 pl-4 group-hover/item:pl-6 transition-all duration-500">
                  <h3 class="flex-1 text-sm md:text-base font-medium text-foreground/85 group-hover/item:text-foreground leading-relaxed line-clamp-2">
                    {{ item?.title || '' }}
                  </h3>
                  <span class="text-foreground/55 group-hover/item:text-primary group-hover/item:translate-x-1 transition-all duration-500 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" /></svg>
                  </span>
                </div>
              </router-link>
            </div>
            <div class="mt-6">
              <router-link
                :to="viewAllUrl"
                class="inline-flex items-center gap-4 px-6 py-2.5 border border-border bg-background text-[9px] font-black tracking-[0.3em] uppercase text-foreground hover:border-primary hover:bg-primary/10 transition-all duration-500 group/btn relative z-10"
              >
                {{ viewAllText }}
                <span class="w-5 h-px bg-foreground/50 group-hover/btn:bg-primary transition-all duration-500" />
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, computed, onBeforeUnmount, watch } from 'vue'
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion'
import { useThemeComponentBindings } from '@/modules/Layout/composables/useThemeDataBindings'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'
import api from '@/engine/api/client'
import { publishingPaths } from '@/engine/api/paths'

const { t } = useThemeI18n('janari')
const { getSetting } = useTheme()
const { localizedString } = useLocalizedThemeSetting()
const { slots: dynamicSlots, hasAnyBinding } = useThemeComponentBindings('info')

const sectionTitle = computed(() => localizedString('home_updates_title') || t('updateInfo.sectionTitle'))
const colAnnouncements = computed(() => localizedString('home_updates_col_announcements') || t('updateInfo.columnAnnouncements'))
const colAgenda = computed(() => localizedString('home_updates_col_agenda') || t('updateInfo.columnAgenda'))
const colHolidays = computed(() => localizedString('home_updates_col_holidays') || t('updateInfo.columnHolidays'))
const viewAllText = computed(() => localizedString('home_updates_view_all') || t('common.viewAll'))
const emptyText = computed(() => localizedString('home_updates_empty') || t('updateInfo.empty'))
const viewAllUrl = computed(() => {
  const raw = getSetting('home_updates_view_all_url', '/blog')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/blog'
})

type UpdateItem = { date: string; category: string; title: string; url: string }

const loading = ref(true)
const apiPosts = ref<UpdateItem[]>([])

function transformApiData(items: any[], defaultUrl: string): UpdateItem[] {
  if (!Array.isArray(items)) return []
  return items.map((item) => {
    if (!item) return null
    const raw = item._raw || item
    const slug = raw?.slug || item?.slug
    return {
      date: raw?.published_at
        ? new Date(raw.published_at).toLocaleDateString('en-CA').replace(/-/g, '.')
        : (item?.date || ''),
      category: raw?.category?.name || raw?.category_name || item?.category || t('common.info'),
      title: raw?.title || item?.title || '',
      url: slug ? `/blog/${slug}` : (item?.url || defaultUrl),
    }
  }).filter((i): i is UpdateItem => i !== null && Boolean(i.title))
}

function takeSlice(items: UpdateItem[], start: number, count: number): UpdateItem[] {
  if (!items.length) return []
  const out: UpdateItem[] = []
  for (let i = 0; i < count; i++) {
    out.push(items[(start + i) % items.length])
  }
  return out
}

const announcements = computed(() => {
  if (hasAnyBinding.value && dynamicSlots.value.announcements?.length) {
    return transformApiData(dynamicSlots.value.announcements, '/blog')
  }
  return takeSlice(apiPosts.value, 0, 3)
})

const agenda = computed(() => {
  if (hasAnyBinding.value && dynamicSlots.value.agenda?.length) {
    return transformApiData(dynamicSlots.value.agenda, '/blog')
  }
  return takeSlice(apiPosts.value, 1, 3)
})

const holidays = computed(() => {
  if (hasAnyBinding.value && dynamicSlots.value.holidays?.length) {
    return transformApiData(dynamicSlots.value.holidays, '/blog')
  }
  return takeSlice(apiPosts.value, 2, 3)
})

const hasAnyItems = computed(() =>
  announcements.value.length > 0 || agenda.value.length > 0 || holidays.value.length > 0,
)

async function loadPostsFromApi() {
  if (hasAnyBinding.value) {
    loading.value = false
    return
  }
  loading.value = true
  try {
    const res = await api.get(publishingPaths.publicContents, {
      params: { type: 'post', status: 'published', sort: '-published_at', per_page: 12 },
    })
    const rawData = res.data || []
    const posts = Array.isArray(rawData) ? rawData : (rawData?.data || [])
    apiPosts.value = transformApiData(posts, '/blog')
  } catch {
    apiPosts.value = []
  } finally {
    loading.value = false
  }
}

const { staggerChildren } = useThemeMotion()
const col1 = ref<HTMLElement>()
const col2 = ref<HTMLElement>()
const col3 = ref<HTMLElement>()
const isComponentActive = ref(true)
const isAnimated = ref(false)

onBeforeUnmount(() => {
  isComponentActive.value = false
})

const runAnimations = async () => {
  if (!isComponentActive.value || isAnimated.value) return
  isAnimated.value = true
  await nextTick()

  if (col1.value) staggerChildren(col1.value, '.info-item', { distance: 30, stagger: 0.1, delay: 0.2 })
  if (col2.value) staggerChildren(col2.value, '.info-item', { distance: 30, stagger: 0.1, delay: 0.3 })
  if (col3.value) staggerChildren(col3.value, '.info-item', { distance: 30, stagger: 0.1, delay: 0.4 })
}

onMounted(async () => {
  await loadPostsFromApi()
  await runAnimations()
})

watch(hasAnyItems, async (v) => {
  if (v) {
    isAnimated.value = false
    await runAnimations()
  }
})
</script>

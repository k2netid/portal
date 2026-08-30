<template>
  <div class="space-y-10 sidebar-scroll-anchor-off">
    <!-- Widget: Kategori -->
    <div class="space-y-6">
      <h3 class="text-sm font-bold text-muted-foreground uppercase tracking-wider flex items-center gap-2">
        <span class="w-1 h-4 bg-primary rounded-full" />
        {{ categoriesLabel }}
      </h3>
      
      <div class="bg-card rounded-2xl p-6 border border-border shadow-sm space-y-6">
        <!-- Search Input -->
        <div class="relative group">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground group-focus-within:text-primary transition-colors" />
          <input 
            v-model="searchQuery"
            type="text" 
            :placeholder="searchPlaceholder" 
            class="w-full bg-muted/50 border-none rounded-xl py-2.5 pl-10 pr-4 text-sm focus:ring-2 focus:ring-primary/20 transition-all"
            @keyup.enter="handleSearch"
            @input="onSearchInput"
            @focus="showSuggestions = true"
          >
          
          <!-- Search Suggestions Dropdown -->
          <div 
            v-if="showSuggestions && suggestions.length > 0" 
            class="absolute top-full left-0 right-0 mt-2 bg-card border border-border shadow-xl rounded-xl overflow-hidden z-50 animate-in fade-in slide-in-from-top-2 duration-200"
          >
            <div class="p-2 space-y-1">
              <button
                v-for="item in suggestions"
                :key="item.id"
                class="w-full flex items-center gap-3 p-2.5 hover:bg-primary/5 rounded-lg transition-colors text-left group"
                @click="selectSuggestion(item)"
              >
                <Search class="w-3.5 h-3.5 text-muted-foreground group-hover:text-primary" />
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-medium text-foreground truncate group-hover:text-primary transition-colors">{{ item.title }}</p>
                  <p class="text-[10px] text-muted-foreground truncate">{{ item.category }}</p>
                </div>
              </button>
            </div>
          </div>
        </div>

        <div v-if="loadingCategories" class="space-y-3">
          <div v-for="i in 5" :key="i" class="h-10 bg-muted animate-pulse rounded-lg" />
        </div>
        <div v-else class="space-y-1">
          <!-- Kategori Semua -->
          <button
            class="w-full flex items-center justify-between p-3 rounded-xl transition-all duration-300 group hover:bg-primary/5 border border-transparent"
            :class="{ 'bg-primary/5 border-primary/20': !currentCategory }"
            @click="selectCategory('')"
          >
            <div class="flex items-center gap-3">
              <div class="w-1.5 h-1.5 rounded-full" :class="!currentCategory ? 'bg-primary' : 'bg-muted-foreground/30 group-hover:bg-primary/50'" />
              <span 
                class="text-sm font-medium transition-colors"
                :class="!currentCategory ? 'text-primary' : 'text-muted-foreground group-hover:text-primary'"
              >
                {{ categoryAllLabel }}
              </span>
            </div>
            <span 
              class="text-[10px] font-bold px-2 py-0.5 rounded-full transition-colors"
              :class="!currentCategory ? 'bg-primary/10 text-primary' : 'bg-muted group-hover:bg-primary/10 group-hover:text-primary'"
            >
              {{ totalPostsCount }}
            </span>
          </button>

          <!-- Hierarchical Categories (Filtered) -->
          <div v-for="category in filteredCategories" :key="category.id" class="space-y-1">
            <div class="flex items-center gap-1">
              <button
                class="flex-1 flex items-center justify-between p-3 rounded-xl transition-all duration-300 group hover:bg-primary/5 border border-transparent"
                :class="{ 'bg-primary/5 border-primary/20': currentCategory === category.slug }"
                @click="selectCategory(category.slug)"
              >
                <div class="flex items-center gap-3">
                  <div class="w-1.5 h-1.5 rounded-full" :class="currentCategory === category.slug ? 'bg-primary' : 'bg-muted-foreground/30 group-hover:bg-primary/50'" />
                  <span 
                    class="text-sm font-medium transition-colors"
                    :class="currentCategory === category.slug ? 'text-primary' : 'text-muted-foreground group-hover:text-primary'"
                  >
                    {{ category.name }}
                  </span>
                </div>
                <span 
                  class="text-[10px] font-bold px-2 py-0.5 rounded-full transition-colors"
                  :class="currentCategory === category.slug ? 'bg-primary/10 text-primary' : 'bg-muted group-hover:bg-primary/10 group-hover:text-primary'"
                >
                  {{ category.contents_count || 0 }}
                </span>
              </button>
              
              <!-- Dropdown Trigger for children -->
              <button 
                v-if="category.children && category.children.length > 0"
                class="p-2 hover:bg-primary/5 rounded-lg transition-colors group"
                @click="toggleCategory(category.id)"
              >
                <ChevronDown 
                  class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-transform duration-300"
                  :class="{ 'rotate-180': expandedCategories.has(category.id) }"
                />
              </button>
            </div>

            <!-- Sub-categories (Child list) -->
            <div 
              v-if="category.children && category.children.length > 0 && expandedCategories.has(category.id)"
              class="pl-6 space-y-1 overflow-hidden transition-all duration-300"
            >
              <button
                v-for="child in category.children"
                :key="child.id"
                class="w-full flex items-center justify-between p-2.5 rounded-xl transition-all duration-300 group hover:bg-primary/5 border border-transparent"
                :class="{ 'bg-primary/5 border-primary/20': currentCategory === child.slug }"
                @click="selectCategory(child.slug)"
              >
                <div class="flex items-center gap-2">
                  <span class="text-muted-foreground/40">—</span>
                  <span 
                    class="text-xs font-medium transition-colors"
                    :class="currentCategory === child.slug ? 'text-primary' : 'text-muted-foreground group-hover:text-primary'"
                  >
                    {{ child.name }}
                  </span>
                </div>
                <span 
                  class="text-[9px] font-bold px-1.5 py-0.5 rounded-full transition-colors"
                  :class="currentCategory === child.slug ? 'bg-primary/10 text-primary' : 'bg-muted group-hover:bg-primary/10 group-hover:text-primary'"
                >
                  {{ child.contents_count || 0 }}
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Widget: Kalender profesional Minimalis -->
    <div class="space-y-6">
      <h3 class="text-sm font-bold text-muted-foreground uppercase tracking-wider flex items-center gap-2">
        <span class="w-1 h-4 bg-primary rounded-full" />
        {{ calendarTitle }}
      </h3>

      <div class="bg-card rounded-2xl p-6 border border-border shadow-sm overflow-hidden relative">
        <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
          <Calendar class="w-24 h-24 text-primary" />
        </div>

        <div class="space-y-6 relative z-10">
          <div 
            v-for="(event, idx) in upcomingEvents" 
            :key="idx"
            class="flex gap-4 group cursor-default"
          >
            <div class="flex flex-col items-center shrink-0">
              <div class="w-12 h-12 rounded-xl bg-primary/10 flex flex-col items-center justify-center border border-primary/20 group-hover:bg-primary group-hover:text-primary-foreground transition-colors duration-300">
                <span class="text-xs font-bold leading-none">{{ event.day }}</span>
                <span class="text-[10px] font-black uppercase leading-none mt-1">{{ event.month }}</span>
              </div>
              <div v-if="idx < upcomingEvents.length - 1" class="w-px h-full bg-border my-2" />
            </div>
            <div class="flex-1 pt-1">
              <h4 class="text-sm font-bold text-foreground group-hover:text-primary transition-colors line-clamp-2">
                {{ event.title }}
              </h4>
              <p class="text-[10px] text-muted-foreground mt-1 flex items-center gap-1">
                <Clock class="w-3.5 h-3.5" />
                {{ event.time }}
              </p>
            </div>
          </div>
        </div>

        <router-link 
          to="/agenda" 
          class="mt-8 w-full py-3 border border-dashed border-border rounded-xl text-xs font-bold text-muted-foreground hover:text-primary hover:border-primary hover:bg-primary/5 transition-all flex items-center justify-center gap-2 group"
        >
          {{ viewAllAgenda }}
          <ArrowRight class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition-transform" />
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter, useRoute } from 'vue-router'
import api from '@/engine/api/client'
import { libraryPaths } from '@/engine/api/paths'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'
import { logger } from '@/shared/utils/logger'
import {
  Calendar,
  Clock,
  ArrowRight,
  ChevronDown,
  Search,
} from 'lucide-vue-next'

const { t } = useI18n()
const { localizedString } = useLocalizedThemeSetting()

const categoriesLabel = computed(() => localizedString('page_blog_categories_label') || t('theme.janari.pages.blog.categoriesLabel'))
const searchPlaceholder = computed(() => localizedString('page_blog_search_placeholder') || t('theme.janari.pages.blog.searchPlaceholder'))
const categoryAllLabel = computed(() => localizedString('page_blog_category_all') || t('theme.janari.pages.blog.categoryAll'))
const calendarTitle = computed(() => localizedString('page_blog_calendar_title') || t('theme.janari.pages.blog.professionalCalendar'))
const viewAllAgenda = computed(() => localizedString('page_blog_view_all_agenda') || t('theme.janari.pages.blog.viewAllAgenda'))

interface Category {
  id: string;
  name: string;
  slug: string;
  contents_count?: number;
  children?: Category[];
}

interface SearchSuggestionItem {
  id: string;
  title: string;
  type: string;
  url: string | null;
  category: string;
}

const router = useRouter()
const route = useRoute()
const categories = ref<Category[]>([])
const loadingCategories = ref(true)
const expandedCategories = ref<Set<string>>(new Set())

const suggestions = ref<SearchSuggestionItem[]>([])
const showSuggestions = ref(false)
let searchDebounce: any = null

const currentCategory = computed(() => route.query.category as string || '')
const searchQuery = ref(route.query.q as string || '')

const typeLabel = (type: string): string => {
  switch (type) {
    case 'post':
      return t('theme.janari.common.article')
    case 'page':
      return t('theme.janari.common.page')
    case 'category':
      return t('theme.janari.pages.blog.contentTypeCategory')
    case 'tag':
      return t('theme.janari.pages.blog.contentTypeTag')
    default:
      return type
  }
}

const toInternalPath = (rawUrl: string | null | undefined): string | null => {
  if (!rawUrl) return null
  if (rawUrl.startsWith('/')) return rawUrl

  try {
    const parsed = new URL(rawUrl)
    return `${parsed.pathname}${parsed.search}${parsed.hash}`
  } catch {
    return null
  }
}

const filteredCategories = computed(() => {
  return categories.value
    .filter(cat => {
      const childCount = cat.children?.reduce((acc, child) => acc + (child.contents_count || 0), 0) || 0
      return (cat.contents_count || 0) > 0 || childCount > 0
    })
    .map(cat => ({
      ...cat,
      children: cat.children?.filter(child => (child.contents_count || 0) > 0)
    }))
})

const totalPostsCount = computed(() => {
  return categories.value.reduce((acc, cat) => {
    let count = cat.contents_count || 0
    // If you want to include children counts in the parent/total
    if (cat.children) {
      count += cat.children.reduce((childAcc, child) => childAcc + (child.contents_count || 0), 0)
    }
    return acc + count
  }, 0)
})

const toggleCategory = (id: string) => {
  if (expandedCategories.value.has(id)) {
    expandedCategories.value.delete(id)
  } else {
    expandedCategories.value.add(id)
  }
}

const upcomingEvents = [0, 1, 2, 3].map((i) => ({
  day: t(`theme.janari.demo.event${i}.day`),
  month: t(`theme.janari.demo.event${i}.month`),
  title: t(`theme.janari.demo.event${i}.title`),
  time: t(`theme.janari.demo.event${i}.time`),
}))

const fetchCategories = async () => {
  try {
    const response = await api.get(libraryPaths.publicCategories, { params: { tree: true } })
    // Data is already unwrapped by api.ts interceptor
    categories.value = Array.isArray(response.data) ? response.data : (response.data as any)?.data || []
  } catch (error) {
    logger.error('Failed to fetch categories:', error)
  } finally {
    loadingCategories.value = false
  }
}

const handleSearch = () => {
  showSuggestions.value = false
  router.push({
    name: 'blog',
    query: {
      ...route.query,
      q: searchQuery.value || undefined
    }
  })
}

const onSearchInput = () => {
  if (searchDebounce) clearTimeout(searchDebounce)
  
  searchDebounce = setTimeout(async () => {
    // Always update the router even if empty to restore articles
    router.replace({
      name: 'blog',
      query: {
        ...route.query,
        q: searchQuery.value || undefined
      }
    })

    if (!searchQuery.value || searchQuery.value.length < 2) {
      suggestions.value = []
      showSuggestions.value = false
      return
    }

    try {
      const response = await api.get('/public/search/suggestions', {
        params: {
          q: searchQuery.value,
          limit: 5
        }
      })
      
      // Data is unwrapped by interceptor: it should be { suggestions: [...] }
      const payload = response.data as any
      const items = Array.isArray(payload.suggestions) ? payload.suggestions : []
      
      suggestions.value = items.map((item: any, idx: number) => {
        return {
          id: item.id || `${item.type || 'item'}-${idx}-${item.text}`,
          title: item.text,
          type: item.type || 'post',
          url: toInternalPath(item.url),
          category: typeLabel(item.type || 'post')
        }
      })
      
      showSuggestions.value = suggestions.value.length > 0
    } catch (error) {
      logger.error('Failed to fetch search suggestions:', error)
      suggestions.value = []
      showSuggestions.value = false
    }
  }, 600)
}

const selectSuggestion = (item: SearchSuggestionItem) => {
  searchQuery.value = item.title
  showSuggestions.value = false
  const path = toInternalPath(item.url)
  if (path) {
    router.push(path)
    return
  }

  router.push({
    name: 'blog',
    query: {
      ...route.query,
      q: item.title || searchQuery.value || undefined
    }
  })
}

const selectCategory = (slug: string) => {
  if (slug === '') {
    router.push({ name: 'blog' })
  } else {
    router.push({ name: 'blog', query: { category: slug } })
  }
}

onMounted(() => {
  fetchCategories()
})
</script>

<style scoped>
/* Sidebar search suggestions can appear/disappear quickly while typing. */
.sidebar-scroll-anchor-off {
  overflow-anchor: none;
}
</style>

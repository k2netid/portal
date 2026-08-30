<template>
  <div class="min-h-screen flex flex-col" data-ja-customizer-target="search">
    <div class="py-12 bg-background flex-1">
      <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-center mb-4 text-foreground">
          {{ pageTitle }}
        </h1>
        <p class="text-center text-lg text-muted-foreground mb-12">
          {{ queryLabel }} <strong class="text-foreground">{{ searchQuery }}</strong>
        </p>
            
        <div
          v-if="loading"
          class="text-center py-16 text-lg text-muted-foreground"
        >
          {{ loadingText }}
        </div>
        <div
          v-else-if="results.length > 0"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
        >
          <PostCard 
            v-for="post in results" 
            :key="post.id" 
            :post="post"
          />
        </div>
        <div
          v-else
          class="text-center py-16 text-lg text-muted-foreground"
        >
          <p>{{ emptyText }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted, watch, defineAsyncComponent } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '@/engine/api/client'
import { publishingPaths } from '@/engine/api/paths'
const PostCard = defineAsyncComponent(() => import('../components/blog/PostCard.vue'))
import { useAnalytics } from '@/shared/composables/useAnalytics'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'

import type { Content } from '@/modules/Publishing/types/content'

const { t } = useI18n()
const { localizedString } = useLocalizedThemeSetting()
const { trackSearch } = useAnalytics()

const route = useRoute()
const searchQuery = ref('')
const results = ref<Content[]>([])
const loading = ref(false)
const pageData = ref<Content | null>(null)

const pageTitle = computed(() => localizedString('page_search_title') || t('publishing.frontend.search.title'))
const queryLabel = computed(() => localizedString('page_search_query_label') || t('publishing.frontend.search.query'))
const loadingText = computed(() => localizedString('page_search_loading') || t('publishing.frontend.search.loading'))
const emptyText = computed(() => {
  const tpl = localizedString('page_search_empty') || t('publishing.frontend.search.empty')
  return String(tpl).replace(/\{query\}/g, searchQuery.value || '')
})

const fetchPageData = async () => {
    try {
        const response = await api.get(publishingPaths.publicContent('search'))
        pageData.value = response.data
    } catch {
        // Silent fail
    }
}

const search = async () => {
  if (!searchQuery.value) return
  
  loading.value = true
  try {
    const response = await api.get('/public/search', {
      params: { q: searchQuery.value }
    })
    results.value = response.data || []
    
    trackSearch(searchQuery.value, results.value.length)
  } catch (error) {
    logger.error('Search failed:', error)
  } finally {
    loading.value = false
  }
}

watch(() => route.query.q, (newQuery) => {
  searchQuery.value = (newQuery as string) || ''
  if (newQuery) search()
})

onMounted(() => {
  fetchPageData()
  searchQuery.value = (route.query.q as string) || ''
  if (searchQuery.value) search()
})
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <div
      v-if="!isEnabled"
      class="flex-1"
    >
      <PageDisabled 
        :title="(pageTitle as string) || t('theme.janari.pages.blog.title')" 
        :message="(getSetting('disabled_page_message') as string)" 
      />
    </div>

    <!-- Main Content -->
    <div v-else class="flex-1 flex flex-col bg-background">
        <!-- Blog header -->
        <section class="py-20 bg-gradient-to-b from-primary/10 to-background dark:from-primary/20">
          <div class="container mx-auto px-4 text-center">
            <span class="text-primary font-bold tracking-wider uppercase text-xs mb-4 block">{{ pageTitle || t('theme.janari.pages.blog.sectionLabel') }}</span>
            <h1
              ref="blogTitle"
              class="text-4xl md:text-5xl font-extrabold mb-4 text-foreground"
            >
              <JanariSplitText :text="pageTitle || t('theme.janari.pages.blog.headline')" />
            </h1>
            <p class="text-muted-foreground max-w-lg mx-auto font-medium">
              {{ pageSubtitle || t('theme.janari.pages.blog.subtitle') }}
            </p>
          </div>
        </section>

        <!-- Blog Grid -->
        <PluginSlot name="after_hero" class="w-full" />

        <section class="flex-1 py-16 bg-background">
          <div class="container mx-auto px-4">
            <!-- Featured Post (Hidden in search mode or when loading) -->
            <div
              v-if="featuredPost && !isSearchMode && !loading"
              ref="featuredRef"
              class="mb-12"
            >
              <h2 class="text-sm font-bold text-muted-foreground uppercase tracking-wider mb-6">
                Featured Article
              </h2>
              <router-link
                :to="featuredPost.link || '#'"
                class="block bg-card rounded-2xl overflow-hidden shadow-lg border border-border group hover:shadow-xl transition-colors cursor-pointer"
              >
                <div class="grid grid-cols-1 lg:grid-cols-2">
                  <div class="aspect-video lg:aspect-[4/3] overflow-hidden">
                    <img 
                      v-if="featuredPost.image"
                      :src="featuredPost.image" 
                      :alt="featuredPost.title"
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                      width="960"
                      height="720"
                      loading="eager"
                      fetchpriority="high"
                      decoding="async"
                      sizes="(max-width: 1024px) 100vw, 50vw"
                    >
                    <div v-else class="w-full h-full bg-primary/5 flex items-center justify-center">
                      <span class="text-primary/20 font-bold text-4xl">JA</span>
                    </div>
                  </div>
                  <div class="p-8 lg:p-10 flex flex-col justify-center">
                    <div class="flex items-center gap-3 mb-4">
                      <span class="px-3 py-1 bg-primary text-primary-foreground text-xs font-bold rounded-full">{{ featuredPost.category }}</span>
                      <span class="text-muted-foreground text-sm">{{ formatDate(featuredPost.published_at) }}</span>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold mb-4 text-foreground group-hover:text-primary transition-colors">
                      {{ featuredPost.title }}
                    </h3>
                    <p class="text-muted-foreground mb-6 line-clamp-3">
                      {{ featuredPost.excerpt }}
                    </p>
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center font-bold text-primary text-sm">
                        JA
                      </div>
                      <div>
                        <p class="font-semibold text-foreground">
                          {{ t('theme.janari.blog.editorialTeam') }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                          {{ featuredPost.readTime }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </router-link>
            </div>

            <!-- Main Content Grid with Sidebar -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
              <!-- Latest Articles -->
              <div class="lg:col-span-9 articles-scroll-anchor-off">
                <div class="flex items-center justify-between mb-6">
                  <h2 class="text-sm font-bold text-muted-foreground uppercase tracking-wider">
                    {{ isSearchMode ? t('theme.janari.pages.blog.searchResultsHeading') : t('theme.janari.pages.blog.latestArticlesHeading') }}
                  </h2>
                  <!-- Subtle Search Indicator -->
                  <div v-if="searching || loading" class="flex items-center gap-2 text-primary">
                    <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-current" />
                    <span class="text-[10px] font-bold uppercase tracking-widest">{{ searching ? t('theme.janari.pages.blog.searching') : t('theme.janari.common.loading') }}</span>
                  </div>
                </div>

                <div
                  v-if="searching || (loading && !loadingMore)"
                  class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
                >
                  <!-- SKELETON CARDS -->
                  <div v-for="i in 6" :key="i" class="bg-card/40 border border-border/50 rounded-2xl overflow-hidden animate-pulse">
                    <div class="aspect-[4/3] bg-muted" />
                    <div class="p-6 space-y-4">
                      <div class="h-4 bg-muted rounded w-1/3" />
                      <div class="h-6 bg-muted rounded w-3/4" />
                      <div class="h-4 bg-muted rounded w-full" />
                      <div class="h-4 bg-muted rounded w-5/6" />
                    </div>
                  </div>
                </div>

                <div
                  v-else-if="displayArticles.length > 0"
                  ref="articlesGrid"
                  class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
                >
                  <router-link 
                    v-for="article in displayArticles" 
                    :key="article.id" 
                    :to="article.link || '#'"
                    class="motion-article group block"
                  >
                    <article class="h-full bg-card/40 hover:bg-card border border-border/50 hover:border-primary/30 rounded-2xl overflow-hidden transition-all duration-500 shadow-sm hover:shadow-xl hover:-translate-y-1 flex flex-col">
                      <!-- Image Section (Sleek) -->
                      <div v-if="article.image" class="relative aspect-[4/3] overflow-hidden">
                        <img 
                          :src="article.image" 
                          :alt="article.title"
                          class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                          width="480"
                          height="360"
                          loading="lazy"
                          fetchpriority="low"
                          decoding="async"
                          sizes="(max-width: 768px) 100vw, (max-width: 1280px) 50vw, 33vw"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-card via-transparent to-transparent opacity-60" />
                        <div class="absolute bottom-4 left-6">
                          <span class="px-3 py-1 bg-primary/20 backdrop-blur-md text-primary text-[10px] font-bold rounded-lg uppercase tracking-wider border border-primary/20">
                            {{ article.category }}
                          </span>
                        </div>
                      </div>

                      <!-- Content Section -->
                      <div class="p-6 flex-1 flex flex-col">
                        <div class="flex-1">
                          <!-- Category Badge (If no image) -->
                          <div v-if="!article.image" class="mb-4">
                            <span class="px-2.5 py-1 bg-primary/10 text-primary text-[9px] font-bold rounded-md uppercase tracking-wider border border-primary/10">
                              {{ article.category }}
                            </span>
                          </div>

                          <div class="flex gap-4">
                            <!-- Accent Line (Parsinta Style) -->
                            <div class="w-1 bg-primary/20 group-hover:bg-primary transition-colors rounded-full shrink-0" />
                            <div class="flex-1">
                              <h3 class="text-lg font-bold text-foreground leading-tight group-hover:text-primary transition-colors line-clamp-2">
                                {{ article.title }}
                              </h3>
                              <p class="text-xs text-muted-foreground mt-3 line-clamp-3 leading-relaxed">
                                {{ article.excerpt }}
                              </p>
                            </div>
                          </div>
                        </div>

                        <!-- Footer Metadata -->
                        <div class="mt-6 pt-4 border-t border-border/50 flex items-center justify-between">
                          <div class="flex items-center gap-3 text-[10px] font-medium text-muted-foreground uppercase tracking-widest">
                            <span class="flex items-center gap-1">
                              <Clock class="w-3 h-3 text-primary" />
                              {{ article.readTime }}
                            </span>
                            <span class="w-1 h-1 bg-border rounded-full" />
                            <span>{{ formatDate(article.published_at) }}</span>
                          </div>
                          
                          <div class="flex items-center gap-1 text-[10px] font-bold text-primary opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                            BACA
                            <ArrowRight class="w-3 h-3" />
                          </div>
                        </div>
                      </div>
                    </article>
                  </router-link>
                </div>

                <!-- Infinite Scroll Sentinel -->
                <div 
                  v-if="hasMore" 
                  ref="sentinel" 
                  class="py-12 flex flex-col items-center gap-4"
                >
                  <div v-if="loadingMore" class="flex flex-col items-center gap-2">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary" />
                    <span class="text-xs text-muted-foreground animate-pulse">{{ t('theme.janari.pages.blog.loadingMore') }}</span>
                  </div>
                </div>

                <div 
                  v-else-if="displayArticles.length === 0 && !loading && !searching"
                  class="bg-muted/30 rounded-2xl p-12 text-center"
                >
                  <p class="text-muted-foreground italic">{{ t('theme.janari.pages.blog.emptyResults') }}</p>
                  <div
                    v-if="searchSuggestions.length > 0"
                    class="mt-4 text-xs text-muted-foreground"
                  >
                    Mungkin maksud Anda:
                    <button
                      v-for="(suggestion, idx) in searchSuggestions"
                      :key="`${suggestion.text}-${idx}`"
                      class="ml-2 text-primary hover:underline"
                      @click="router.push({ name: 'blog', query: { ...route.query, q: suggestion.text } })"
                    >
                      {{ suggestion.text }}
                    </button>
                  </div>
                  <button 
                    class="mt-4 text-primary font-bold text-sm"
                    @click="router.push('/blog')"
                  >
                    {{ t('theme.janari.pages.blog.viewAllArticles') }}
                  </button>
                </div>
              </div>

              <!-- Sidebar -->
              <aside class="lg:col-span-3">
                <BlogSidebar />
              </aside>
            </div>
          </div>
        </section>
      </div>
  </div>
</template>

<script setup lang="ts">
import { PluginSlot } from '@/shared/components'
import JanariSplitText from '../components/shared/JanariSplitText.vue'
import BlogSidebar from '../components/blog/BlogSidebar.vue'
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed, nextTick, watch, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter, useRoute } from 'vue-router'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'
import PageDisabled from '../components/shared/PageDisabled.vue'
import api from '@/engine/api/client'
import { publishingPaths } from '@/engine/api/paths'
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion'
import { ArrowRight, Clock } from 'lucide-vue-next';

import type { Content } from '@/modules/Publishing/types/content'

interface Article extends Omit<Partial<Content>, 'category'> {
    category: string;
    readTime: string;
    image: string | null; // Alias for featured_image
    link: string;
}

const { t } = useI18n()
const { getSetting } = useTheme()
const { localizedString } = useLocalizedThemeSetting()
const router = useRouter()
const route = useRoute()

const isEnabled = computed(() => getSetting('enable_blog', true))
const behavior = computed(() => getSetting('disabled_page_behavior', 'message'))
const pageTitle = computed(() => localizedString('page_blog_title') || t('theme.janari.pages.blog.title'))
const pageSubtitle = computed(() => localizedString('page_blog_subtitle') || t('theme.janari.pages.blog.subtitle'))

const pageData = ref<Content | null>(null)
const articles = ref<Article[]>([])
const loading = ref(true)
const searching = ref(false)
const loadingMore = ref(false)
const isAnimated = ref(false)

// Pagination state
const currentPage = ref(1)
const lastPage = ref(1)
const isSearchMode = computed(() => Boolean((route.query.q as string || '').trim()))
const hasMore = computed(() => !isSearchMode.value && currentPage.value < lastPage.value)
const searchSuggestions = ref<Array<{ text: string; type: string }>>([])

// GSAP
const { splitTextRevealSafe, scaleReveal, staggerChildren, motion, ScrollTrigger } = useThemeMotion()
const blogTitle = ref<HTMLElement>()
const featuredRef = ref<HTMLElement>()
const articlesGrid = ref<HTMLElement>()
const sentinel = ref<HTMLElement>()

const animateArticleCards = async (onlyNew = false): Promise<void> => {
    await nextTick()
    const grid = articlesGrid.value
    if (!grid) return

    if (!onlyNew) {
        staggerChildren(grid, '.motion-article', { distance: 40, stagger: 0.1, duration: 0.6 })
        grid.querySelectorAll('.motion-article').forEach((el) => {
            (el as HTMLElement).dataset.motionAnimated = '1'
        })
        return
    }

    const newCards = Array.from(grid.querySelectorAll('.motion-article')).filter((el) => !(el as HTMLElement).dataset.motionAnimated)
    if (!newCards.length) return
    newCards.forEach((el) => {
        (el as HTMLElement).dataset.motionAnimated = '1'
    })
    motion.fromTo(newCards, { opacity: 0, y: 32 }, { opacity: 1, y: 0, duration: 0.55, ease: 'power3.out', stagger: 0.08 })
    ScrollTrigger.refresh()
}

const featuredPost = computed(() => {
    return articles.value.length > 0 ? articles.value[0] : null
})

const displayArticles = computed(() => {
    if (isSearchMode.value) return articles.value
    return articles.value.slice(1)
})

const formatDate = (dateString: string | undefined) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
}

const normalizePath = (raw: string | undefined | null): string => {
    if (!raw) return '#'
    if (raw.startsWith('/')) return raw
    try {
        const parsed = new URL(raw)
        return `${parsed.pathname}${parsed.search}${parsed.hash}`
    } catch {
        return '#'
    }
}

const typeLabel = (type: string | undefined): string => {
    switch ((type || '').toLowerCase()) {
        case 'post':
            return t('theme.janari.common.article')
        case 'page':
            return t('theme.janari.common.page')
        case 'category':
            return t('theme.janari.pages.blog.contentTypeCategory')
        case 'tag':
            return t('theme.janari.pages.blog.contentTypeTag')
        default:
            return t('theme.janari.pages.blog.contentTypeDefault')
    }
}

const estimateReadTime = (text: string | undefined): string => {
    const minutes = text ? Math.max(1, Math.ceil(text.split(' ').length / 200)) : 1
    return t('theme.janari.common.readTimeMinutes', { n: minutes })
}

const mapContentToArticle = (post: Content): Article => {
    const categoryData = (post as any).category
    const contentType = (post.type || 'post').toLowerCase()
    const link = contentType === 'page' ? `/${post.slug || ''}` : `/blog/${post.slug || ''}`

    return {
        id: post.id,
        title: post.title,
        slug: post.slug,
        excerpt: post.excerpt,
        category: categoryData?.name || typeLabel(post.type),
        readTime: estimateReadTime(post.body),
        image: post.featured_image || null,
        published_at: post.published_at,
        link,
    } as Article
}

const mapSearchResultToArticle = (item: any, idx: number): Article => {
    const title = String(item.title || '')
    const excerpt = String(item.excerpt || '')
    const itemType = String(item.type || 'post').toLowerCase()

    return {
        id: String(item.searchable_id || item.id || idx + 1),
        title,
        slug: '',
        excerpt,
        category: typeLabel(itemType),
        readTime: estimateReadTime(excerpt),
        image: null,
        published_at: undefined,
        link: normalizePath(item.url),
    }
}

const fetchPosts = async (page = 1, append = false) => {
    try {
        const searchQuery = (route.query.q as string || '').trim();

        if (append) {
            loadingMore.value = true
        } else if (searchQuery) {
            searching.value = true
        } else {
            loading.value = true
            articles.value = [] // Clear old articles only when resetting/loading new category
        }

        const categorySlug = route.query.category as string;

        if (searchQuery) {
            const searchResponse = await api.get('/public/search', {
                params: {
                    q: searchQuery,
                    limit: 60,
                }
            });

            const payload = searchResponse.data as any
            const searchResults = Array.isArray(payload?.results) ? payload.results : []
            searchSuggestions.value = Array.isArray(payload?.suggestions) ? payload.suggestions : []

            const mapped = searchResults
                .filter((item: any) => item && ['post', 'page', 'category', 'tag'].includes(String(item.type || '').toLowerCase()))
                .map((item: any, idx: number) => mapSearchResultToArticle(item, idx))

            articles.value = mapped
            currentPage.value = 1
            lastPage.value = 1
            return
        }

        searchSuggestions.value = []
        
        const response = await api.get(publishingPaths.publicContents, {
            params: {
                type: 'post',
                status: 'published',
                sort: '-published_at',
                per_page: 9,
                page: page,
                category: categorySlug,
            }
        });
        
        // Data is already unwrapped by api.ts interceptor
        const payload = response.data as any
        const postsData: Content[] = Array.isArray(payload) ? payload : (payload.data || [])
        
        // Update pagination info (Laravel standard or wrapped)
        if (payload && payload.meta) {
            currentPage.value = payload.meta.current_page
            lastPage.value = payload.meta.last_page
        } else if (payload && payload.current_page) {
            currentPage.value = payload.current_page
            lastPage.value = payload.last_page
        }
        
        const mappedArticles = postsData.map(mapContentToArticle);

        if (append) {
            articles.value = [...articles.value, ...mappedArticles]
        } else {
            articles.value = mappedArticles
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch posts:', error);
        if (!append) articles.value = [];
    } finally {
        loading.value = false
        loadingMore.value = false
        searching.value = false
        if (append) {
            await animateArticleCards(true)
        }
    }
}

// Infinite Scroll Observer
let observer: IntersectionObserver | null = null

const setupObserver = () => {
    if (observer) observer.disconnect()
    
    observer = new IntersectionObserver((entries) => {
        if (entries[0]?.isIntersecting && hasMore.value && !loadingMore.value) {
            fetchPosts(currentPage.value + 1, true)
        }
    }, { threshold: 0.1, rootMargin: '100px' })

    if (sentinel.value) {
        observer.observe(sentinel.value)
    }
}

onMounted(async () => {
  if (!isEnabled.value && behavior.value === 'redirect') {
    router.push('/')
    return
  }
  
  if (!isEnabled.value) {
    loading.value = false
    return
  }

  try {
    const response = await api.get(publishingPaths.publicContent('blog'))
    pageData.value = response.data
  } catch (_error: unknown) {
    // 404 is fine, we fallback to fetchPosts
  }

  if (!pageData.value || !pageData.value.body) {
    await fetchPosts(1)
  }
  
  loading.value = false

  await nextTick()
  setupObserver()

  if (isAnimated.value) return
  isAnimated.value = true

  if (blogTitle.value) splitTextRevealSafe(blogTitle.value, { stagger: 0.05 })
  if (featuredRef.value) scaleReveal(featuredRef.value, { duration: 0.8 })
  await animateArticleCards(false)
})

onUnmounted(() => {
    if (observer) observer.disconnect()
})

// Re-fetch posts when category or search query param changes
watch(() => [route.query.category, route.query.q], async () => {
  currentPage.value = 1
  await fetchPosts(1, false)
  
  await nextTick()
  setupObserver()
  await animateArticleCards(false)
})
</script>

<style scoped>
/* Search/result rendering updates this list frequently; disable scroll anchoring here
   to prevent browser consecutive-adjustment warnings and scroll jitter. */
.articles-scroll-anchor-off {
  overflow-anchor: none;
}
</style>

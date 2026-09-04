<template>
  <div
    class="ja-plugin-block instagram-feed-block plugin-slot-isolate w-full py-8 md:py-14"
    :data-plugin="pluginSlug"
  >
    <!-- 1. PREVIEW FALLBACK FOR ADMIN / PAGE BUILDER CANVAS WHEN INACTIVE -->
    <div
      v-if="isPreview && (!feedData.enabled || feedData.items.length === 0)"
      class="container mx-auto px-4"
    >
      <div class="rounded-2xl border-2 border-dashed border-primary/20 bg-primary/5 p-8 text-center space-y-3">
        <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center mx-auto">
          <Instagram class="w-6 h-6" />
        </div>
        <h4 class="font-bold text-base text-foreground">
          {{ t('plugin.instagram.builderPlaceholderTitle') }}
        </h4>
        <p class="text-xs text-muted-foreground max-w-md mx-auto">
          {{ t('plugin.instagram.builderPlaceholderDesc') }}
        </p>
      </div>
    </div>

    <!-- 2. PUBLIC VIEW: ONLY RENDERS WHEN FEED DATA IS ACTIVE AND AVAILABLE (ZERO BROKEN UI) -->
    <section
      v-else-if="feedData.enabled && feedData.items.length > 0"
      class="container mx-auto px-4 space-y-8"
      :aria-label="resolvedTitle"
    >
      <!-- SECTION HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-2 border-b border-border/40">
        <div class="space-y-1.5 max-w-2xl">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gradient-to-r from-purple-500/10 via-pink-500/10 to-orange-500/10 border border-pink-500/20 text-xs font-semibold text-pink-600 dark:text-pink-400">
            <Instagram class="w-3.5 h-3.5" />
            <span>{{ t('plugin.instagram.badge') }}</span>
          </div>
          <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-foreground">
            {{ resolvedTitle }}
          </h2>
          <p class="text-sm text-muted-foreground">
            {{ resolvedSubtitle }}
          </p>
        </div>

        <a
          v-if="feedData.username"
          :href="`https://instagram.com/${feedData.username}`"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-2 self-start sm:self-auto px-4 py-2 rounded-xl text-xs font-bold text-foreground bg-card hover:bg-muted border border-border shadow-sm hover:shadow transition-all group"
        >
          <span class="bg-gradient-to-tr from-yellow-500 via-pink-500 to-purple-600 text-white p-1 rounded-lg">
            <Instagram class="w-3.5 h-3.5" />
          </span>
          <span>@{{ feedData.username }}</span>
          <ExternalLink class="w-3 h-3 text-muted-foreground group-hover:text-foreground transition-colors" />
        </a>
      </div>

      <!-- LAYOUT VARIANT: BENTO GRID (DEFAULT) -->
      <div
        v-if="activeLayout === 'bento'"
        class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 auto-rows-[160px] sm:auto-rows-[190px] md:auto-rows-[220px]"
      >
        <div
          v-for="(item, idx) in feedData.items"
          :key="item.id || idx"
          class="group relative overflow-hidden rounded-2xl cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-1 bg-muted/60"
          :class="getBentoSpanClass(idx)"
          @click="openLightbox(item, idx)"
        >
          <img
            :src="item.thumbnail_url || item.media_url"
            :alt="item.caption ? item.caption.substring(0, 60) : 'Instagram Post'"
            loading="lazy"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          />

          <!-- TYPE BADGE -->
          <div
            v-if="item.media_type === 'VIDEO' || item.media_type === 'CAROUSEL_ALBUM'"
            class="absolute top-2.5 right-2.5 z-10 p-1.5 rounded-lg bg-black/60 backdrop-blur-md text-white shadow-sm"
          >
            <Play v-if="item.media_type === 'VIDEO'" class="w-3 h-3 fill-current" />
            <Layers v-else class="w-3 h-3" />
          </div>

          <!-- HOVER OVERLAY -->
          <div class="absolute inset-0 z-20 bg-gradient-to-t from-black/85 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-between p-4 text-white">
            <div class="flex items-center justify-end">
              <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md bg-white/20 backdrop-blur-md">
                {{ formatRelativeTime(item.timestamp) }}
              </span>
            </div>

            <div class="space-y-2">
              <p
                v-if="item.caption"
                class="text-xs line-clamp-2 text-white/90 leading-snug font-medium"
              >
                {{ item.caption }}
              </p>

              <div class="flex items-center gap-4 text-xs font-semibold">
                <span v-if="item.like_count !== null" class="inline-flex items-center gap-1">
                  <Heart class="w-3.5 h-3.5 fill-red-500 text-red-500" />
                  {{ formatMetric(item.like_count) }}
                </span>
                <span v-if="item.comments_count !== null" class="inline-flex items-center gap-1">
                  <MessageCircle class="w-3.5 h-3.5" />
                  {{ formatMetric(item.comments_count) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- LAYOUT VARIANT: CLASSIC GRID -->
      <div
        v-else-if="activeLayout === 'grid'"
        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"
      >
        <div
          v-for="(item, idx) in feedData.items"
          :key="item.id || idx"
          class="group relative aspect-square overflow-hidden rounded-2xl cursor-pointer transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 bg-muted/60"
          @click="openLightbox(item, idx)"
        >
          <img
            :src="item.thumbnail_url || item.media_url"
            :alt="item.caption ? item.caption.substring(0, 50) : 'Instagram Post'"
            loading="lazy"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          />

          <div
            v-if="item.media_type === 'VIDEO' || item.media_type === 'CAROUSEL_ALBUM'"
            class="absolute top-2 right-2 z-10 p-1.5 rounded-lg bg-black/60 backdrop-blur-md text-white"
          >
            <Play v-if="item.media_type === 'VIDEO'" class="w-3 h-3 fill-current" />
            <Layers v-else class="w-3 h-3" />
          </div>

          <div class="absolute inset-0 z-20 bg-black/65 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-4 text-white font-bold text-sm">
            <span v-if="item.like_count !== null" class="inline-flex items-center gap-1.5">
              <Heart class="w-4 h-4 fill-red-500 text-red-500" />
              {{ formatMetric(item.like_count) }}
            </span>
            <span v-if="item.comments_count !== null" class="inline-flex items-center gap-1.5">
              <MessageCircle class="w-4 h-4" />
              {{ formatMetric(item.comments_count) }}
            </span>
          </div>
        </div>
      </div>

      <!-- LAYOUT VARIANT: CAROUSEL -->
      <div v-else class="relative group/carousel">
        <button
          type="button"
          class="absolute -left-3 top-1/2 -translate-y-1/2 z-20 p-2 rounded-full bg-card/90 border border-border shadow-md hover:bg-muted text-foreground opacity-0 group-hover/carousel:opacity-100 transition-opacity"
          @click="scrollCarousel('left')"
        >
          <ChevronLeft class="w-5 h-5" />
        </button>
        <div
          ref="carouselRef"
          class="flex gap-4 overflow-x-auto snap-x snap-mandatory scrollbar-none pb-2 scroll-smooth"
        >
          <div
            v-for="(item, idx) in feedData.items"
            :key="item.id || idx"
            class="snap-start shrink-0 w-64 sm:w-72 aspect-square relative overflow-hidden rounded-2xl cursor-pointer transition-all duration-300 hover:shadow-lg bg-muted/60"
            @click="openLightbox(item, idx)"
          >
            <img
              :src="item.thumbnail_url || item.media_url"
              :alt="item.caption ? item.caption.substring(0, 50) : 'Instagram Post'"
              loading="lazy"
              class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
            />
          </div>
        </div>
        <button
          type="button"
          class="absolute -right-3 top-1/2 -translate-y-1/2 z-20 p-2 rounded-full bg-card/90 border border-border shadow-md hover:bg-muted text-foreground opacity-0 group-hover/carousel:opacity-100 transition-opacity"
          @click="scrollCarousel('right')"
        >
          <ChevronRight class="w-5 h-5" />
        </button>
      </div>
    </section>

    <!-- 3. INTERACTIVE LIGHTBOX MODAL -->
    <div
      v-if="selectedPost"
      class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/80 backdrop-blur-md transition-opacity animate-in fade-in"
      @click.self="closeLightbox"
    >
      <div class="relative w-full max-w-4xl bg-card border border-border/80 rounded-3xl overflow-hidden shadow-2xl flex flex-col md:flex-row max-h-[90vh]">
        <!-- CLOSE BUTTON -->
        <button
          type="button"
          class="absolute top-3 right-3 z-30 p-2 rounded-full bg-black/60 hover:bg-black/80 text-white transition-colors"
          :title="t('common.actions.close')"
          @click="closeLightbox"
        >
          <X class="w-5 h-5" />
        </button>

        <!-- MEDIA DISPLAY -->
        <div class="relative w-full md:w-3/5 bg-black flex items-center justify-center overflow-hidden min-h-[260px] md:min-h-[480px]">
          <video
            v-if="selectedPost.media_type === 'VIDEO' && selectedPost.media_url"
            :src="selectedPost.media_url"
            controls
            autoplay
            playsinline
            class="max-h-[85vh] w-full object-contain"
          />
          <img
            v-else
            :src="selectedPost.media_url || selectedPost.thumbnail_url"
            :alt="selectedPost.caption || 'Instagram Post'"
            class="max-h-[85vh] w-full object-contain"
          />

          <!-- PREV / NEXT NAVIGATION -->
          <button
            v-if="selectedIndex > 0"
            type="button"
            class="absolute left-3 top-1/2 -translate-y-1/2 p-2 rounded-full bg-black/60 hover:bg-black/80 text-white transition-colors"
            @click.stop="prevPost"
          >
            <ChevronLeft class="w-5 h-5" />
          </button>
          <button
            v-if="selectedIndex < feedData.items.length - 1"
            type="button"
            class="absolute right-3 top-1/2 -translate-y-1/2 p-2 rounded-full bg-black/60 hover:bg-black/80 text-white transition-colors"
            @click.stop="nextPost"
          >
            <ChevronRight class="w-5 h-5" />
          </button>
        </div>

        <!-- DETAILS & COMMENTS SIDEBAR -->
        <div class="w-full md:w-2/5 p-6 flex flex-col justify-between overflow-y-auto space-y-4 bg-card">
          <div class="space-y-4">
            <!-- AUTHOR HEADER -->
            <div class="flex items-center gap-3 pb-3 border-b border-border">
              <div class="w-10 h-10 rounded-full p-0.5 bg-gradient-to-tr from-yellow-500 via-pink-500 to-purple-600">
                <div class="w-full h-full rounded-full bg-background flex items-center justify-center text-foreground font-bold text-xs">
                  <Instagram class="w-4 h-4 text-pink-500" />
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <h5 class="font-bold text-sm text-foreground truncate">
                  @{{ feedData.username || 'instagram' }}
                </h5>
                <p class="text-xs text-muted-foreground flex items-center gap-1">
                  <Clock class="w-3 h-3" />
                  <span>{{ formatRelativeTime(selectedPost.timestamp) }}</span>
                </p>
              </div>
            </div>

            <!-- CAPTION -->
            <div v-if="selectedPost.caption" class="text-xs leading-relaxed text-foreground whitespace-pre-line max-h-40 overflow-y-auto pr-1">
              {{ selectedPost.caption }}
            </div>

            <!-- COMMENTS LIST (IF AVAILABLE) -->
            <div v-if="selectedPost.comments && selectedPost.comments.length > 0" class="space-y-3 pt-2 border-t border-border/60">
              <span class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
                {{ t('plugin.instagram.commentsTitle') }} ({{ selectedPost.comments.length }})
              </span>
              <div class="space-y-2.5 max-h-36 overflow-y-auto pr-1">
                <div
                  v-for="comm in selectedPost.comments"
                  :key="comm.id"
                  class="text-xs space-y-0.5 bg-muted/40 p-2.5 rounded-xl border border-border/40"
                >
                  <span class="font-semibold text-foreground">@{{ comm.username }}: </span>
                  <span class="text-muted-foreground">{{ comm.text }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- FOOTER ACTIONS -->
          <div class="pt-4 border-t border-border space-y-3">
            <div class="flex items-center justify-between text-xs font-semibold text-muted-foreground">
              <span v-if="selectedPost.like_count !== null" class="inline-flex items-center gap-1.5 text-foreground">
                <Heart class="w-4 h-4 text-red-500 fill-red-500" />
                <span>{{ formatMetric(selectedPost.like_count) }} {{ t('plugin.instagram.likes') }}</span>
              </span>
              <span v-if="selectedPost.comments_count !== null" class="inline-flex items-center gap-1.5 text-foreground">
                <MessageCircle class="w-4 h-4 text-blue-500" />
                <span>{{ formatMetric(selectedPost.comments_count) }} {{ t('plugin.instagram.comments') }}</span>
              </span>
            </div>

            <a
              v-if="selectedPost.permalink"
              :href="selectedPost.permalink"
              target="_blank"
              rel="noopener noreferrer"
              class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-bold text-xs bg-gradient-to-r from-purple-600 via-pink-600 to-orange-500 text-white shadow hover:opacity-95 transition-opacity"
            >
              <span>{{ t('plugin.instagram.viewOnInstagram') }}</span>
              <ExternalLink class="w-3.5 h-3.5" />
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import {
  Instagram,
  Heart,
  MessageCircle,
  ExternalLink,
  ChevronLeft,
  ChevronRight,
  X,
  Play,
  Layers,
  Clock,
} from 'lucide-vue-next';

interface CommentItem {
  id: string;
  username: string;
  text: string;
  timestamp: string;
}

interface FeedItem {
  id: string;
  media_type: 'IMAGE' | 'VIDEO' | 'CAROUSEL_ALBUM';
  media_url: string;
  thumbnail_url?: string;
  caption?: string;
  permalink?: string;
  like_count?: number | null;
  comments_count?: number | null;
  timestamp: string;
  comments?: CommentItem[];
}

interface FeedResponse {
  enabled: boolean;
  username?: string;
  account_id?: string;
  items: FeedItem[];
}

const props = withDefaults(
  defineProps<{
    pluginSlug?: string;
    layout?: 'bento' | 'grid' | 'carousel';
    title?: string;
    subtitle?: string;
    limit?: number;
    isPreview?: boolean;
    context?: Record<string, unknown>;
  }>(),
  {
    pluginSlug: 'instagram-feed',
    layout: 'bento',
    title: '',
    subtitle: '',
    limit: 8,
    isPreview: false,
    context: () => ({}),
  }
);

const { t } = useI18n();

const feedData = ref<FeedResponse>({
  enabled: false,
  items: [],
});

const selectedPost = ref<FeedItem | null>(null);
const selectedIndex = ref<number>(-1);
const carouselRef = ref<HTMLElement | null>(null);

const activeLayout = computed(() => props.layout || 'bento');

const resolvedTitle = computed(() => {
  if (props.title && props.title.trim() !== '') {
    return props.title;
  }
  return t('plugin.instagram.defaultTitle');
});

const resolvedSubtitle = computed(() => {
  if (props.subtitle && props.subtitle.trim() !== '') {
    return props.subtitle;
  }
  return t('plugin.instagram.defaultSubtitle');
});

function getBentoSpanClass(index: number): string {
  // Bento pattern:
  // Tile 0: large 2x2 featured item
  // Tiles 1-4: 1x1 standard square items
  // Tile 5: 2x1 wide item
  if (index === 0) {
    return 'col-span-2 row-span-2';
  }
  if (index === 5) {
    return 'col-span-2 row-span-1';
  }
  return 'col-span-1 row-span-1';
}

function formatMetric(num: number | null | undefined): string {
  if (num === null || num === undefined) return '0';
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'k';
  return num.toString();
}

function formatRelativeTime(dateStr: string): string {
  try {
    const diffSeconds = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
    if (diffSeconds < 60) return t('plugin.instagram.timeJustNow');
    const diffMinutes = Math.floor(diffSeconds / 60);
    if (diffMinutes < 60) return `${diffMinutes}m`;
    const diffHours = Math.floor(diffMinutes / 60);
    if (diffHours < 24) return `${diffHours}h`;
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 30) return `${diffDays}d`;
    return new Date(dateStr).toLocaleDateString();
  } catch {
    return '';
  }
}

function openLightbox(item: FeedItem, idx: number) {
  selectedPost.value = item;
  selectedIndex.value = idx;
}

function closeLightbox() {
  selectedPost.value = null;
  selectedIndex.value = -1;
}

function prevPost() {
  if (selectedIndex.value > 0) {
    selectedIndex.value--;
    selectedPost.value = feedData.value.items[selectedIndex.value] || null;
  }
}

function nextPost() {
  if (selectedIndex.value < feedData.value.items.length - 1) {
    selectedIndex.value++;
    selectedPost.value = feedData.value.items[selectedIndex.value] || null;
  }
}

function scrollCarousel(dir: 'left' | 'right') {
  if (!carouselRef.value) return;
  const offset = dir === 'left' ? -300 : 300;
  carouselRef.value.scrollBy({ left: offset, behavior: 'smooth' });
}

async function loadFeed() {
  try {
    const response = await api.get('/public/social-feed/instagram');
    const res = (response.data ?? response) as { success?: boolean; data?: FeedResponse } | FeedResponse;
    if ('data' in res && res.data) {
      feedData.value = res.data;
    } else if ('enabled' in res) {
      feedData.value = res as FeedResponse;
    }
  } catch {
    // Fail-safe: leave enabled as false
    feedData.value = { enabled: false, items: [] };
  }
}

onMounted(() => {
  loadFeed();
});
</script>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>

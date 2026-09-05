<template>
  <div class="universal-widget recent-posts-widget rounded-2xl border border-border/70 bg-card p-5 shadow-sm space-y-4">
    <div class="flex items-center justify-between border-b border-border/60 pb-3">
      <div class="flex items-center gap-2">
        <span class="w-1 h-4 bg-primary rounded-full" />
        <h3 class="text-sm font-bold text-foreground font-heading tracking-tight">
          {{ widgetTitle }}
        </h3>
      </div>
      <Flame class="w-4 h-4 text-amber-500" />
    </div>

    <!-- Loading state -->
    <div
      v-if="loading && postList.length === 0"
      class="space-y-3 animate-pulse py-1"
    >
      <div
        v-for="i in 3"
        :key="i"
        class="flex gap-3 items-center"
      >
        <div class="w-14 h-14 bg-muted rounded-xl shrink-0" />
        <div class="flex-1 space-y-1.5">
          <div class="h-3.5 bg-muted rounded w-3/4" />
          <div class="h-2.5 bg-muted rounded w-1/2" />
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div
      v-else-if="postList.length === 0"
      class="text-xs text-muted-foreground py-3 text-center"
    >
      {{ emptyText }}
    </div>

    <!-- Posts List -->
    <ul
      v-else
      class="space-y-3.5"
    >
      <li
        v-for="post in postList"
        :key="post.slug || post.id"
      >
        <router-link
          :to="`/blog/${post.slug}`"
          class="flex items-start gap-3 group p-1.5 -mx-1.5 rounded-xl transition-all duration-200 hover:bg-muted/50"
        >
          <!-- Thumbnail / Image -->
          <div
            v-if="showThumbnail"
            class="relative w-14 h-14 rounded-xl overflow-hidden bg-muted shrink-0 border border-border/50"
          >
            <img
              v-if="post.featured_image || post.image"
              :src="post.featured_image || post.image"
              :alt="post.title"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              loading="lazy"
            >
            <div
              v-else
              class="w-full h-full flex items-center justify-center bg-primary/5 text-primary/60"
            >
              <FileText class="w-5 h-5" />
            </div>
          </div>

          <!-- Content Info -->
          <div class="flex-1 min-w-0 space-y-1">
            <span
              v-if="resolveCategoryName(post.category)"
              class="text-[10px] font-bold text-primary tracking-wide uppercase block truncate"
            >
              {{ resolveCategoryName(post.category) }}
            </span>
            <h4 class="text-xs sm:text-sm font-semibold text-foreground line-clamp-2 leading-snug group-hover:text-primary transition-colors">
              {{ post.title }}
            </h4>
            <div
              v-if="showDate && (post.published_at || post.created_at)"
              class="flex items-center gap-1 text-[11px] text-muted-foreground"
            >
              <Calendar class="w-3 h-3 shrink-0 opacity-70" />
              <span>{{ formatDate(post.published_at || post.created_at) }}</span>
            </div>
          </div>
        </router-link>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { Flame, FileText, Calendar } from 'lucide-vue-next';

interface PostItem {
  id?: string | number;
  title: string;
  slug: string;
  featured_image?: string;
  image?: string;
  published_at?: string;
  created_at?: string;
  category?: { name?: string } | string;
}

const props = withDefaults(
  defineProps<{
    widget?: Record<string, any>;
    posts?: PostItem[];
    limit?: number;
    title?: string;
    showDate?: boolean;
    showThumbnail?: boolean;
    currentPostSlug?: string;
  }>(),
  {
    widget: undefined,
    posts: undefined,
    limit: 5,
    title: undefined,
    showDate: true,
    showThumbnail: true,
    currentPostSlug: undefined,
  }
);

const { t, te } = useI18n();

const widgetTitle = computed(() => {
  if (props.title) return props.title;
  if (props.widget?.title) return props.widget.title;
  return te('layout.widgets.universal.recentPosts.title')
    ? t('layout.widgets.universal.recentPosts.title')
    : 'Warta Terbaru';
});

const emptyText = computed(() => {
  return te('layout.widgets.universal.recentPosts.empty')
    ? t('layout.widgets.universal.recentPosts.empty')
    : 'Belum ada warta terbaru';
});

const rawPostList = ref<PostItem[]>(props.posts || props.widget?.items || []);
const loading = ref(false);

const postList = computed(() => {
  let list = rawPostList.value;
  if (props.currentPostSlug) {
    list = list.filter(p => p.slug !== props.currentPostSlug);
  }
  return list.slice(0, props.limit);
});

const formatDate = (val?: string) => {
  if (!val) return '';
  try {
    return new Date(val).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    });
  } catch {
    return val;
  }
};

const resolveCategoryName = (cat: unknown): string => {
  if (!cat) return '';
  if (typeof cat === 'string') return cat;
  if (typeof cat === 'object' && 'name' in (cat as Record<string, any>)) {
    return String((cat as Record<string, any>).name || '');
  }
  return '';
};

const fetchRecentPosts = async () => {
  if (props.posts && props.posts.length > 0) {
    rawPostList.value = props.posts;
    return;
  }
  if (props.widget?.items && Array.isArray(props.widget.items) && props.widget.items.length > 0) {
    rawPostList.value = props.widget.items;
    return;
  }

  loading.value = true;
  try {
    const res = await api.get('/public/publishing/contents', {
      params: {
        type: 'post',
        limit: (props.limit || 5) + 1, // fetch 1 extra in case current article is filtered
      }
    });
    const data = res.data as any;
    const list = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []);
    rawPostList.value = list;
  } catch {
    rawPostList.value = [];
  } finally {
    loading.value = false;
  }
};

watch(() => props.posts, (newVal) => {
  if (newVal && newVal.length > 0) {
    rawPostList.value = newVal;
  }
}, { immediate: true });

onMounted(() => {
  if (rawPostList.value.length === 0) {
    fetchRecentPosts();
  }
});
</script>

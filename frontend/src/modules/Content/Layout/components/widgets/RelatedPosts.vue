<template>
  <div
    v-if="related && related.length > 0"
    class="related-posts py-8 border-t border-border"
  >
    <h2 class="text-2xl font-bold text-foreground mb-6">
      {{ displayTitle }}
    </h2>
    
    <div
      v-if="loading"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <!-- Loading skeleton -->
      <div
        v-for="i in 3"
        :key="i"
        class="animate-pulse"
      >
        <div class="bg-muted h-48 rounded-lg mb-3" />
        <div class="h-4 bg-muted rounded w-3/4 mb-2" />
        <div class="h-3 bg-muted rounded w-1/2" />
      </div>
    </div>
    
    <div
      v-else-if="related && related.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <article
        v-for="post in related"
        :key="post.id"
        class="group bg-card rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200 border border-border"
      >
        <router-link
          :to="getPostUrl(post.slug)"
          class="block"
        >
          <!-- Featured Image -->
          <div
            v-if="post.featured_image"
            class="relative h-48 overflow-hidden bg-secondary dark:bg-background"
          >
            <img
              v-lazy="post.featured_image"
              :alt="post.title"
              src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200'%3E%3Crect width='400' height='200' fill='%23e5e7eb'/%3E%3C/svg%3E"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            >
          </div>
          <div
            v-else
            class="relative h-48 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center"
          >
            <Image class="w-12 h-12 text-muted-foreground" />
          </div>
          
          <!-- Content -->
          <div class="p-4">
            <!-- Category -->
            <div
              v-if="post.category"
              class="mb-2"
            >
              <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-blue-500/20 text-blue-400 dark:bg-blue-900 dark:text-blue-200">
                {{ post.category.name }}
              </span>
            </div>
            
            <!-- Title -->
            <h3 class="text-lg font-semibold text-foreground mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
              {{ post.title }}
            </h3>
            
            <!-- Excerpt -->
            <p
              v-if="post.excerpt"
              class="text-sm text-muted-foreground mb-3 line-clamp-2"
            >
              {{ post.excerpt }}
            </p>
            
            <!-- Meta -->
            <div class="flex items-center justify-between text-xs text-muted-foreground">
              <span
                v-if="post.author"
                class="flex items-center"
              >
                <User class="w-4 h-4 mr-1" />
                {{ post.author.name }}
              </span>
              <span
                v-if="post.published_at"
                class="flex items-center"
              >
                <Calendar class="w-4 h-4 mr-1" />
                {{ formatDate(post.published_at) }}
              </span>
            </div>
          </div>
        </router-link>
      </article>
    </div>
    
    <div
      v-else
      class="text-center py-8 text-muted-foreground"
    >
      Tidak ada artikel terkait
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import {
  Calendar,
  Image,
  User,
} from 'lucide-vue-next';

interface Post {
    id: string;
    title: string;
    slug: string;
    excerpt?: string;
    featured_image?: string;
    published_at?: string;
    category?: {
        name: string;
    };
    author?: {
        name: string;
    };
}

const props = withDefaults(defineProps<{
  slug: string;
  title?: string;
  limit?: number;
}>(), {
  title: undefined,
  limit: 6,
});

const displayTitle = computed(() => props.title || t('theme.janari.widgets.relatedPosts'));
const related = ref<Post[]>([]);
const loading = ref(true);

const getPostUrl = (slug: string) => {
  return `/blog/${slug}`;
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const fetchRelated = async () => {
  loading.value = true;
  try {
    const response = await api.get(publishingPaths.publicContentRelated(props.slug));
    related.value = response.data.slice(0, props.limit);
  } catch (error) {
    logger.error('Failed to fetch related posts:', error);
    related.value = [];
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchRelated();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>


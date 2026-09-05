<template>
  <LayungPageGate
    setting-key="enable_blog"
    :title="t('pages.blog.title', 'Berita')"
  >
  <div
    data-ja-customizer-target="news"
    class="layung-page flex-1 flex flex-col py-10 md:py-12"
  >
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: displayCompanyName } }"
    />

    <template v-else>
      <ThemeSafeHtml
        v-if="cmsBody"
        class="sr-only"
        :html="cmsBody"
        mode="publishing"
      />

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full">
        <div class="space-y-4">
          <Breadcrumb :items="[{ name: t('pages.blog.title', 'Berita') }]" />
          <div class="max-w-3xl space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 font-mono">
              <Newspaper class="w-3.5 h-3.5" />
              {{ t('pages.blog.sectionBadge', 'Berita') }}
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.blog.title', 'Berita') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.blog.subtitle', 'Pengumuman layanan, jangkauan, dan operasional Kami.') }}
            </p>
          </div>
        </div>

        <div class="flex flex-wrap gap-2 text-xs font-mono">
          <button
            v-for="cat in categories"
            :key="cat.slug"
            type="button"
            class="px-3.5 py-1.5 rounded-full border transition-all"
            :class="selectedCategory === cat.slug ? 'bg-sky-500 text-white font-bold border-sky-500 shadow-sm' : 'border-border text-muted-foreground hover:bg-muted/80 hover:text-foreground'"
            @click="selectedCategory = cat.slug"
          >
            {{ cat.name }}
          </button>
        </div>

        <div
          v-if="loading"
          class="min-h-[300px] flex items-center justify-center font-mono text-xs text-muted-foreground"
        >
          <div class="w-8 h-8 rounded-full border-2 border-sky-500 border-t-transparent animate-spin" />
        </div>

        <div
          v-else-if="filteredPosts.length > 0"
          class="grid grid-cols-1 md:grid-cols-3 gap-6"
        >
          <PostCard
            v-for="post in filteredPosts"
            :key="post.id"
            :post="post"
          />
        </div>

        <div
          v-else
          class="py-12 text-center text-muted-foreground border-2 border-dashed border-border rounded-2xl space-y-2"
        >
          <Newspaper class="w-12 h-12 mx-auto opacity-70" />
          <p class="text-sm font-semibold text-foreground">
            {{ t('pages.blog.noPosts', 'Belum ada artikel untuk kategori ini.') }}
          </p>
          <p class="text-xs text-muted-foreground">
            {{ t('pages.blog.noPostsHint', 'Pilih kategori lain atau tunggu publikasi artikel baru.') }}
          </p>
        </div>
      </div>
    </template>
  </div>
  </LayungPageGate>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Newspaper } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '@/modules/Layout/views/themes/layung/components/shared/Breadcrumb.vue';
import LayungPageGate from '@/modules/Layout/views/themes/layung/components/shared/LayungPageGate.vue';
import PostCard from '@/modules/Layout/views/themes/layung/components/blog/PostCard.vue';
import { useLayungIdentity } from '@/modules/Layout/views/themes/layung/composables/useLayungIdentity';
import apiClient from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('blog');

const posts = ref<any[]>([]);
const loading = ref(true);
const selectedCategory = ref('');

const categories = computed(() => [
  { name: t('pages.blog.allCategories', 'Semua berita'), slug: '' },
  { name: t('pages.blog.catInfra', 'Internet'), slug: 'infrastruktur' },
  { name: t('pages.blog.catSecurity', 'Operasional'), slug: 'security' },
  { name: t('pages.blog.catCloud', 'Managed Services'), slug: 'cloud' },
  { name: t('pages.blog.catMaintenance', 'Pemeliharaan'), slug: 'maintenance' },
]);

const filteredPosts = computed(() => {
  if (!selectedCategory.value) return posts.value;
  return posts.value.filter((p) => {
    return (
      p.category?.slug === selectedCategory.value ||
      (p.category?.name || '').toLowerCase().includes(selectedCategory.value)
    );
  });
});

onMounted(async () => {
  loading.value = true;
  try {
    const res = await apiClient.get(publishingPaths.publicContents, {
      params: { type: 'post', per_page: 12, status: 'published', sort: '-published_at' },
    });
    const data = res.data;
    posts.value = Array.isArray(data) ? data : data?.data || [];
  } catch {
    posts.value = [];
  } finally {
    loading.value = false;
  }
});
</script>

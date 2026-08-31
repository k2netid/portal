<template>
  <div
    data-ja-customizer-target="news"
    class="sarangenge-theme flex-1 flex flex-col py-10 md:py-12"
  >
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: displaySchoolName } }"
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
          <Breadcrumb :items="[{ name: t('pages.blog.title', 'Kabar & Pengumuman Sekolah') }]" />
          <div class="max-w-3xl space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
              <Newspaper class="w-3.5 h-3.5" />
              {{ t('pages.blog.sectionBadge', 'Warta Sivitas Akademika') }}
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.blog.title', 'Berita, Pengumuman & Agenda Sekolah') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.blog.subtitle', 'Informasi resmi, liputan prestasi, edaran akademik, dan agenda kegiatan kesiswaan.') }}
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          <div class="lg:col-span-8 space-y-8">
            <div
              v-if="loading"
              class="min-h-[300px] flex items-center justify-center"
            >
              <div class="w-8 h-8 rounded-full border-2 border-[var(--sarangenge-teal,#0f766e)] border-t-transparent animate-spin" />
            </div>

            <div
              v-else-if="filteredPosts.length > 0"
              class="grid grid-cols-1 md:grid-cols-2 gap-6"
            >
              <PostCard
                v-for="post in filteredPosts"
                :key="post.id"
                :post="post"
              />
            </div>

            <div
              v-else
              class="sarangenge-panel p-10 text-center text-muted-foreground space-y-3"
            >
              <Newspaper class="w-10 h-10 mx-auto opacity-70 text-muted-foreground" />
              <p class="text-base font-semibold text-foreground">
                {{ t('pages.blog.noPosts', 'Belum ada warta untuk kategori ini.') }}
              </p>
              <p class="text-xs text-muted-foreground">
                {{ t('pages.blog.noPostsHint', 'Silakan pilih kategori lain atau gunakan kolom pencarian.') }}
              </p>
            </div>
          </div>

          <div class="lg:col-span-4">
            <BlogSidebar
              :categories="categories"
              :active-category="selectedCategory"
              @select-category="handleCategorySelect"
            />
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import PostCard from '@/modules/Layout/views/themes/sarangenge/components/blog/PostCard.vue';
import BlogSidebar from '@/modules/Layout/views/themes/sarangenge/components/blog/BlogSidebar.vue';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import type { Content } from '@/modules/Publishing/types/content';
import { Newspaper } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName } = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('blog');

const posts = ref<Content[]>([]);
const loading = ref(true);
const selectedCategory = ref('');

const categories = computed(() => [
  { name: t('pages.blog.allCategories', 'Semua Warta'), slug: '' },
  { name: t('pages.blog.catPpdb', 'Pengumuman & PPDB'), slug: 'ppdb' },
  { name: t('pages.blog.catPrestasi', 'Prestasi Siswa'), slug: 'prestasi' },
  { name: t('pages.blog.catKegiatan', 'Kegiatan Kesiswaan'), slug: 'kegiatan' },
  { name: t('pages.blog.catAkademik', 'Akademik & Kurikulum'), slug: 'akademik' },
]);

const filteredPosts = computed(() => {
  if (!selectedCategory.value) return posts.value;
  return posts.value.filter((p) => {
    return p.category?.slug === selectedCategory.value || (p.category?.name || '').toLowerCase().includes(selectedCategory.value);
  });
});

const handleCategorySelect = (slug: string) => {
  selectedCategory.value = slug;
};

onMounted(async () => {
  loading.value = true;
  try {
    const res = await api.get(publishingPaths.publicContents, {
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

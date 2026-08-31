<template>
  <div
    data-ja-customizer-target="news"
    class="sarangenge-theme flex-1 flex flex-col py-10 sm:py-16"
  >
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: displaySchoolName } }"
    />

    <ThemeSafeHtml
      v-else-if="cmsBody"
      class="container mx-auto px-4 py-16"
      :html="cmsBody"
      mode="publishing"
    />

    <template v-else>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 w-full">
        <!-- Breadcrumb & Header -->
        <div class="space-y-4">
          <Breadcrumb :items="[{ name: t('pages.blog.title', 'Kabar & Pengumuman Sekolah') }]" />
          <div class="max-w-3xl space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
              <Newspaper class="w-3.5 h-3.5" />
              Warta Sivitas Akademika
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.blog.title', 'Berita, Pengumuman & Agenda Sekolah') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.blog.subtitle', 'Informasi resmi, liputan prestasi, edaran akademik, dan agenda kegiatan kesiswaan.') }}
            </p>
          </div>
        </div>

        <!-- Main Layout: Grid Posts + Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
          <div class="lg:col-span-8 space-y-8">
            <!-- Loading -->
            <div
              v-if="loading"
              class="min-h-[300px] flex items-center justify-center"
            >
              <div class="w-8 h-8 rounded-full border-2 border-[var(--sarangenge-teal,#0f766e)] border-t-transparent animate-spin" />
            </div>

            <!-- Posts Grid -->
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

            <!-- Empty -->
            <div
              v-else
              class="sarangenge-panel p-12 text-center text-muted-foreground space-y-3"
            >
              <Newspaper class="w-10 h-10 mx-auto opacity-40 text-muted-foreground" />
              <p class="text-base font-semibold text-foreground">
                {{ t('pages.blog.noPosts', 'Belum ada warta untuk kategori ini.') }}
              </p>
              <p class="text-xs text-muted-foreground">
                Silakan pilih kategori lain atau gunakan kolom pencarian.
              </p>
            </div>
          </div>

          <!-- Sidebar -->
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
  { name: 'Semua Warta', slug: '' },
  { name: 'Pengumuman & PPDB', slug: 'ppdb' },
  { name: 'Prestasi Siswa', slug: 'prestasi' },
  { name: 'Kegiatan Kesiswaan', slug: 'kegiatan' },
  { name: 'Akademik & Kurikulum', slug: 'akademik' },
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
  try {
    const res = await api.get('/public/publishing/contents', {
      params: { type: 'post', per_page: 12, status: 'published' }
    });
    if (res.data && Array.isArray(res.data.data) && res.data.data.length > 0) {
      posts.value = res.data.data;
    } else {
      throw new Error('No posts returned');
    }
  } catch {
    // School-themed default posts
    posts.value = [
      {
        id: '1',
        title: 'Pembukaan Pendaftaran Siswa Baru (PPDB) 2026/2027 Gelombang 1',
        slug: 'pembukaan-ppdb-2026-2027-gelombang-1',
        excerpt: 'Pendaftaran peserta didik baru resmi dibuka dengan jalur prestasi akademik, tahfidz, dan tes bakat minat.',
        published_at: new Date().toISOString(),
        category: { name: 'Pengumuman & PPDB', slug: 'ppdb' } as { name: string; slug: string },
        author: { name: 'Sekretariat PPDB' } as { name: string },
      } as Content,
      {
        id: '2',
        title: 'Siswa Sarangenge Raih Medali Emas Olimpiade Matematika Internasional 2026',
        slug: 'siswa-raih-medali-emas-imo-2026',
        excerpt: 'Prestasi membanggakan kembali ditorehkan ananda Ahmad Fadhil dalam ajang bergengsi IMO di Tokyo.',
        published_at: new Date(Date.now() - 86400000 * 3).toISOString(),
        category: { name: 'Prestasi Siswa', slug: 'prestasi' } as { name: string; slug: string },
        author: { name: 'Humas Sekolah' } as { name: string },
      } as Content,
      {
        id: '3',
        title: 'Gelar Karya P5 & Pameran Robotika AI Karya Siswa Sarangenge',
        slug: 'gelar-karya-p5-pameran-robotika-2026',
        excerpt: 'Eksplorasi proyek riset sains dan demonstrasi kecerdasan buatan menyedot antusiasme ratusan orang tua dan tamu undangan.',
        published_at: new Date(Date.now() - 86400000 * 7).toISOString(),
        category: { name: 'Kegiatan Kesiswaan', slug: 'kegiatan' } as { name: string; slug: string },
        author: { name: 'Tim Kesiswaan' } as { name: string },
      } as Content,
      {
        id: '4',
        title: 'Sosialisasi Kurikulum Merdeka & Kelas Persiapan Masuk PTN Top Indonesia',
        slug: 'sosialisasi-kurikulum-merdeka-ptn-2026',
        excerpt: 'Pendampingan khusus persiapan seleksi SNBP dan UTBK SNBT bagi siswa kelas XI dan XII.',
        published_at: new Date(Date.now() - 86400000 * 12).toISOString(),
        category: { name: 'Akademik & Kurikulum', slug: 'akademik' } as { name: string; slug: string },
        author: { name: 'Bimbingan Konseling' } as { name: string },
      } as Content,
    ];
  } finally {
    loading.value = false;
  }
});
</script>

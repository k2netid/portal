<template>
  <div
    data-ja-customizer-target="news"
    class="layung-theme flex-1 flex flex-col py-10 sm:py-16"
  >
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: displayCompanyName } }"
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
          <Breadcrumb :items="[{ name: t('pages.blog.title', 'Warta & Berita Jaringan') }]" />
          <div class="max-w-3xl space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-500 font-mono">
              <Newspaper class="w-3.5 h-3.5" />
              Warta Teknologi & Notifikasi Jaringan
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.blog.title', 'Warta Teknologi & Notifikasi Jaringan') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.blog.subtitle', 'Artikel seputar tren cyber security, teknologi cloud, dan jadwal pemeliharaan jaringan berkala.') }}
            </p>
          </div>
        </div>

        <!-- Category Filters -->
        <div class="flex flex-wrap gap-2 text-xs font-mono">
          <button
            v-for="cat in categories"
            :key="cat.slug"
            type="button"
            class="px-3.5 py-1.5 rounded-full border transition-all"
            :class="selectedCategory === cat.slug ? 'bg-orange-500 text-white font-bold border-orange-500 shadow-sm' : 'border-border text-muted-foreground hover:bg-muted/80'"
            @click="selectedCategory = cat.slug"
          >
            {{ cat.name }}
          </button>
        </div>

        <!-- Posts Grid -->
        <div
          v-if="loading"
          class="min-h-[300px] flex items-center justify-center font-mono text-xs text-muted-foreground"
        >
          <div class="w-8 h-8 rounded-full border-2 border-orange-500 border-t-transparent animate-spin" />
        </div>

        <div
          v-else-if="filteredPosts.length > 0"
          class="grid grid-cols-1 md:grid-cols-3 gap-8"
        >
          <PostCard
            v-for="post in filteredPosts"
            :key="post.id"
            :post="post"
          />
        </div>

        <div
          v-else
          class="py-16 text-center text-muted-foreground border-2 border-dashed border-border rounded-2xl"
        >
          <Newspaper class="w-12 h-12 mx-auto mb-3 opacity-40" />
          <p class="text-sm">
            Belum ada artikel untuk kategori ini.
          </p>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Newspaper } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '@/modules/Layout/views/themes/layung/components/shared/Breadcrumb.vue';
import PostCard from '@/modules/Layout/views/themes/layung/components/blog/PostCard.vue';
import { useLayungIdentity } from '@/modules/Layout/views/themes/layung/composables/useLayungIdentity';
import apiClient from '@/engine/api/client';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('blog');

const posts = ref<any[]>([]);
const loading = ref(true);
const selectedCategory = ref('');

const categories = [
  { name: 'Semua Warta', slug: '' },
  { name: 'Infrastruktur Fiber', slug: 'infrastruktur' },
  { name: 'Cyber Security', slug: 'security' },
  { name: 'Cloud & SD-WAN', slug: 'cloud' },
  { name: 'Notifikasi Maintenance', slug: 'maintenance' },
];

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
  try {
    const res = await apiClient.get('/public/publishing/contents', {
      params: { type: 'post', limit: 12 }
    });
    if (res.data?.data && Array.isArray(res.data.data) && res.data.data.length > 0) {
      posts.value = res.data.data;
    } else {
      throw new Error('No posts');
    }
  } catch {
    // Fallback static sample posts
    posts.value = [
      {
        id: '1',
        title: 'Upgrade Kapasitas Peering IIX APJII Menjadi 100 Gbps untuk Akselerasi CDN',
        slug: 'upgrade-peering-iix-100gbps',
        excerpt: 'Layung Network resmi mengaktifkan interkoneksi 100G ke gedung Cyber untuk mengantisipasi lonjakan trafik video streaming dan cloud gaming.',
        published_at: '2026-08-15',
        read_time: 4,
        category: { name: 'Infrastruktur Fiber', slug: 'infrastruktur' },
      },
      {
        id: '2',
        title: 'Mitigasi Serangan DDoS Multi-Vektor: Panduan Arsitektur SOC Korporat',
        slug: 'mitigasi-ddos-multi-vektor',
        excerpt: 'Pelajari bagaimana integrasi BGP Anycast dan scrub center lokal mampu menyaring serangan volume tinggi tanpa mengganggu operasional aplikasi.',
        published_at: '2026-08-20',
        read_time: 6,
        category: { name: 'Cyber Security', slug: 'security' },
      },
      {
        id: '3',
        title: 'Jadwal Pemeliharaan Berkala Kabel Bawah Laut Jalur Batam - Singapura',
        slug: 'pemeliharaan-kabel-batam-singapura',
        excerpt: 'Pemberitahuan resmi mengenai pemeliharaan preventif segmen kabel bawah laut internasional dengan pengalihan rute redundan otomatis.',
        published_at: '2026-08-28',
        read_time: 3,
        category: { name: 'Notifikasi Maintenance', slug: 'maintenance' },
      },
    ];
  } finally {
    loading.value = false;
  }
});
</script>

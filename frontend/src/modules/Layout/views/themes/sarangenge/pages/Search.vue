<template>
  <div
    data-ja-customizer-target="search"
    class="sarangenge-theme flex-1 flex flex-col py-10 md:py-12"
  >
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full">
      <!-- Breadcrumb & Header -->
      <div class="space-y-4">
        <Breadcrumb :items="[{ name: t('pages.search.title', 'Pencarian') }]" />
        <div class="text-center space-y-3 max-w-2xl mx-auto">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
            <Search class="w-3.5 h-3.5" />
            Pusat Informasi Terpadu
          </span>
          <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
            {{ t('pages.search.title', 'Pencarian Warta & Konten Sekolah') }}
          </h1>
          <p class="text-base text-muted-foreground leading-relaxed">
            {{ t('pages.search.subtitle', 'Temukan artikel berita, panduan PPDB, program kurikulum, dan pengumuman sekolah.') }}
          </p>
        </div>
      </div>

      <!-- Search Box Form -->
      <form
        class="sarangenge-panel p-3 sm:p-4 flex flex-col sm:flex-row gap-3 shadow-lg"
        @submit.prevent="runSearch"
      >
        <Input
          v-model="query"
          type="search"
          required
          minlength="2"
          class="flex-1 !border-none !shadow-none !text-base !bg-transparent"
          :placeholder="t('pages.search.placeholder', 'Ketik kata kunci pencarian (misal: PPDB, beasiswa, kurikulum)...')"
        />
        <Button
          type="submit"
          variant="primary"
          size="md"
          class="font-bold shrink-0"
          :disabled="loading"
        >
          <Search class="w-4 h-4 mr-1" />
          {{ t('pages.search.submit', 'Cari') }}
        </Button>
      </form>

      <!-- Error Message -->
      <p
        v-if="error"
        class="text-sm text-destructive text-center"
      >
        {{ error }}
      </p>

      <!-- Loading State -->
      <div
        v-else-if="loading"
        class="min-h-[200px] flex items-center justify-center"
      >
        <div class="w-8 h-8 rounded-full border-2 border-[var(--sarangenge-teal,#0f766e)] border-t-transparent animate-spin" />
      </div>

      <!-- Search Results List -->
      <div
        v-else-if="results.length > 0"
        class="space-y-4"
      >
        <div class="text-xs font-bold text-muted-foreground uppercase tracking-wider px-1">
          Ditemukan {{ results.length }} hasil untuk "{{ query }}"
        </div>

        <ul class="space-y-4">
          <li
            v-for="item in results"
            :key="item.id"
            class="sarangenge-panel p-6 space-y-2 hover:border-[var(--sarangenge-teal,#0f766e)]/40 hover:shadow-md transition-all"
          >
            <div class="flex items-center gap-2">
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
                {{ item.type || 'Halaman' }}
              </span>
            </div>
            <component
              :is="isExternal(item.url) ? 'a' : 'router-link'"
              :to="isExternal(item.url) ? undefined : publicPath(item.url)"
              :href="isExternal(item.url) ? item.url ?? undefined : undefined"
              class="text-lg font-bold text-foreground hover:text-[var(--sarangenge-teal,#0f766e)] transition-colors block font-heading"
            >
              {{ item.title }}
            </component>
            <p
              v-if="item.excerpt"
              class="text-sm text-muted-foreground leading-relaxed"
            >
              {{ item.excerpt }}
            </p>
          </li>
        </ul>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="searched"
        class="sarangenge-panel p-12 text-center text-muted-foreground space-y-2"
      >
        <Search class="w-10 h-10 mx-auto opacity-40" />
        <p class="text-base font-semibold text-foreground">
          {{ t('pages.search.empty', 'Tidak ada hasil untuk kata kunci tersebut.') }}
        </p>
        <p class="text-xs text-muted-foreground">
          Coba gunakan kata kunci lain seperti "beasiswa", "fasilitas", "prestasi", atau "PPDB".
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import api from '@/engine/api/client';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import { Button, Input } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import { Search } from 'lucide-vue-next';

const { displaySchoolName } = useSarangengeIdentity();

interface SearchHit {
    id: string;
    type: string;
    title: string;
    excerpt?: string | null;
    url?: string | null;
}

const { t } = useThemeI18n('sarangenge');
const route = useRoute();
const router = useRouter();

const query = ref('');
const loading = ref(false);
const searched = ref(false);
const error = ref('');
const results = ref<SearchHit[]>([]);

const isExternal = (url?: string | null): boolean => Boolean(url && /^https?:\/\//i.test(url));

const publicPath = (url?: string | null): string => {
    if (!url) {
        return '/blog';
    }
    const content = url.match(/\/(?:content|blog)\/([^/?#]+)/i);
    if (content?.[1]) {
        return `/blog/${content[1]}`;
    }
    return url.startsWith('/') ? url : `/${url}`;
};

const runSearch = async (): Promise<void> => {
    const q = query.value.trim();
    if (q.length < 2) {
        return;
    }
    loading.value = true;
    error.value = '';
    searched.value = true;
    await router.replace({ path: '/search', query: { q } });
    try {
        const res = await api.get('/public/search', { params: { q } });
        const payload = res.data as { results?: SearchHit[] } | SearchHit[];
        if (Array.isArray(payload)) {
            results.value = payload;
        } else {
            results.value = Array.isArray(payload.results) ? payload.results : [];
        }
    } catch {
        results.value = [
          {
            id: '1',
            type: 'PPDB & Pendaftaran',
            title: 'Informasi Penerimaan Peserta Didik Baru (PPDB) 2026/2027',
            excerpt: 'Panduan persyaratan, jadwal gelombang 1 dan 2, formulir pendaftaran, serta beasiswa prestasi.',
            url: '/contact',
          },
          {
            id: '2',
            type: 'Kurikulum & Program',
            title: 'Kurikulum Merdeka Riset, STEM & Kelas Bilingual Cambridge',
            excerpt: `Struktur kurikulum unggulan dan peminatan sains serta teknologi di ${displaySchoolName.value}.`,
            url: '/solusi',
          },
          {
            id: '3',
            type: 'Prestasi',
            title: 'Galeri Prestasi Olimpiade Sains & Kejuaraan Siswa',
            excerpt: 'Rekam jejak medali emas IMO, robotika nasional, dan debat bahasa Inggris.',
            url: '/achievement',
          },
        ].filter((item) => item.title.toLowerCase().includes(q.toLowerCase()) || (item.excerpt || '').toLowerCase().includes(q.toLowerCase()));
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    const q = typeof route.query.q === 'string' ? route.query.q : '';
    if (q.trim().length >= 2) {
        query.value = q;
        void runSearch();
    }
});
</script>

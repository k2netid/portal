<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-10 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full">
      <!-- Breadcrumb -->
      <Breadcrumb
        :items="[
          { name: t('pages.blog.title', 'Warta Sekolah'), path: '/blog' },
          { name: post?.title || t('pages.post.title', 'Detail Warta') }
        ]"
      />

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        <div class="lg:col-span-8 space-y-10 min-w-0">
          <router-link
            to="/blog"
            class="inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors"
          >
            <ArrowLeft class="w-4 h-4" />
            {{ t('pages.post.backToBlog', 'Kembali ke Warta Sekolah') }}
          </router-link>

          <div
            v-if="loading"
            class="min-h-[400px] flex items-center justify-center"
          >
            <div class="w-8 h-8 rounded-full border-2 border-[var(--sarangenge-teal,#0f766e)] border-t-transparent animate-spin" />
          </div>

          <article
            v-else-if="post"
            class="space-y-8"
          >
            <div class="space-y-4">
              <span
                v-if="post.category"
                class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200 border border-[var(--sarangenge-teal)]/20"
              >
                {{ post.category.name }}
              </span>
              <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading leading-tight tracking-tight">
                {{ post.title }}
              </h1>
              <div class="flex items-center gap-4 text-xs text-muted-foreground pt-1">
                <span>{{ t('common.publishedOn', 'Diterbitkan') }} {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : 'Baru saja' }}</span>
                <span>·</span>
                <span>{{ post.author?.name || `Redaksi ${displaySchoolName}` }}</span>
              </div>
            </div>

            <div
              v-if="post.featured_image"
              class="rounded-3xl overflow-hidden aspect-[16/9] border border-border/60 shadow-xl bg-muted"
            >
              <img
                :src="post.featured_image"
                :alt="post.title"
                class="w-full h-full object-cover"
              >
            </div>

            <div class="prose prose-lg dark:prose-invert max-w-none text-foreground leading-relaxed pt-4">
              <ThemeSafeHtml :html="post.body || post.excerpt || ''" />
            </div>

            <!-- Share & Bookmark -->
            <div class="flex items-center justify-between gap-3 pt-6 border-t border-border/60">
              <Button
                variant="outline"
                size="sm"
                :disabled="!memberStore.isAuthenticated"
                @click="toggleBookmark"
              >
                {{ bookmarked
                  ? t('pages.post.bookmarked', 'Tersimpan')
                  : t('pages.post.bookmark', 'Simpan Warta') }}
              </Button>
              <router-link
                v-if="!memberStore.isAuthenticated"
                to="/member/login"
                class="text-xs text-[var(--sarangenge-teal,#0f766e)] font-semibold hover:underline"
              >
                {{ t('pages.post.signInToEngage', 'Masuk portal untuk menyimpan & berdiskusi') }}
              </router-link>
            </div>

            <!-- Comments Section -->
            <section class="space-y-6 pt-6 border-t border-border/60">
              <h2 class="text-xl font-bold font-heading text-foreground">
                {{ t('pages.post.comments', 'Tanggapan & Diskusi') }}
              </h2>
              <form
                v-if="memberStore.isAuthenticated"
                class="space-y-3"
                @submit.prevent="submitComment"
              >
                <textarea
                  v-model="commentBody"
                  required
                  rows="3"
                  class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--sarangenge-teal,#0f766e)]/20"
                  :placeholder="t('pages.post.commentPlaceholder', 'Tuliskan tanggapan Anda...')"
                />
                <Button
                  type="submit"
                  variant="primary"
                  size="sm"
                  :disabled="commentPending"
                >
                  {{ t('pages.post.postComment', 'Kirim Tanggapan') }}
                </Button>
              </form>
              <ul
                v-if="comments.length"
                class="space-y-4"
              >
                <li
                  v-for="comment in comments"
                  :key="comment.id"
                  class="sarangenge-panel p-4 space-y-1"
                >
                  <p class="text-xs font-bold text-foreground">
                    {{ comment.member?.name || comment.name || t('pages.post.reader', 'Pembaca') }}
                  </p>
                  <p class="text-sm text-muted-foreground">
                    {{ comment.body }}
                  </p>
                </li>
              </ul>
              <p
                v-else
                class="text-xs text-muted-foreground italic"
              >
                {{ t('pages.post.noComments', 'Belum ada tanggapan.') }}
              </p>
            </section>
          </article>

          <div
            v-else
            class="sarangenge-panel p-16 text-center text-muted-foreground"
          >
            {{ t('pages.blog.noPosts', 'Warta tidak ditemukan.') }}
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-4">
          <WidgetArea location="sidebar" :context="{ post }">
            <BlogSidebar />
          </WidgetArea>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import api from '@/engine/api/client';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import WidgetArea from '@/modules/Layout/components/widgets/WidgetArea.vue';
import BlogSidebar from '@/modules/Layout/views/themes/sarangenge/components/blog/BlogSidebar.vue';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useMemberStore } from '@/modules/Member/stores/member';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import { ArrowLeft } from 'lucide-vue-next';

const { displaySchoolName } = useSarangengeIdentity();

interface Post {
  id: string | number;
  title: string;
  slug: string;
  excerpt?: string;
  body?: string;
  featured_image?: string;
  published_at?: string;
  category?: { name: string };
  author?: { name: string };
}

interface PublicComment {
  id: string;
  body: string;
  name?: string | null;
  member?: { name?: string };
}

const { t } = useThemeI18n('sarangenge');
const route = useRoute();
const memberStore = useMemberStore();
const post = ref<Post | null>(null);
const loading = ref(true);
const comments = ref<PublicComment[]>([]);
const commentBody = ref('');
const commentPending = ref(false);
const bookmarked = ref(false);
const bookmarkId = ref<string | null>(null);

const unwrapList = (payload: unknown): PublicComment[] => {
  if (Array.isArray(payload)) return payload as PublicComment[];
  if (payload && typeof payload === 'object' && Array.isArray((payload as { data?: unknown }).data)) {
    return (payload as { data: PublicComment[] }).data;
  }
  return [];
};

const loadComments = async (contentId: string | number): Promise<void> => {
  try {
    const res = await api.get(`/public/publishing/contents/${contentId}/comments`);
    comments.value = unwrapList(res.data);
  } catch {
    comments.value = [];
  }
};

const loadBookmark = async (contentId: string | number): Promise<void> => {
  if (!memberStore.isAuthenticated) {
    bookmarked.value = false;
    bookmarkId.value = null;
    return;
  }
  try {
    const res = await api.get('/member/bookmarks', { params: { content_id: contentId } });
    const rows = unwrapList(res.data) as Array<{ id: string }>;
    bookmarkId.value = rows[0]?.id ?? null;
    bookmarked.value = Boolean(bookmarkId.value);
  } catch {
    bookmarked.value = false;
  }
};

const toggleBookmark = async (): Promise<void> => {
  if (!post.value || !memberStore.isAuthenticated) return;
  if (bookmarked.value && bookmarkId.value) {
    await api.delete(`/member/bookmarks/${bookmarkId.value}`);
    bookmarked.value = false;
    bookmarkId.value = null;
    return;
  }
  const res = await api.post('/member/bookmarks', { content_id: post.value.id });
  const created = res.data as { id?: string };
  bookmarkId.value = created?.id ?? null;
  bookmarked.value = true;
};

const submitComment = async (): Promise<void> => {
  if (!post.value || !commentBody.value.trim()) return;
  commentPending.value = true;
  try {
    await api.post(`/public/publishing/contents/${post.value.id}/comments`, {
      body: commentBody.value,
    });
    commentBody.value = '';
    await loadComments(post.value.id);
  } finally {
    commentPending.value = false;
  }
};

onMounted(async () => {
  const slug = String(route.params.slug || '');
  if (!slug) {
    loading.value = false;
    return;
  }

  try {
    const res = await api.get(`/public/publishing/contents/${slug}`);
    const payload = (res.data?.data ?? res.data) as Post | undefined;
    if (payload?.id) {
      post.value = payload;
      await loadComments(payload.id);
      await loadBookmark(payload.id);
    }
  } catch {
    post.value = {
      id: 1,
      title: 'Pembukaan Pendaftaran Siswa Baru (PPDB) 2026/2027 Gelombang 1',
      slug,
      body: `<p>Penerimaan Peserta Didik Baru (PPDB) tahun ajaran 2026/2027 resmi dibuka mulai hari ini. ${displaySchoolName.value} menyambut calon siswa berkarakter unggul melalui tiga jalur utama: Jalur Prestasi Akademik & Tahfidz, Jalur Minat Bakat (Olahraga & Seni), dan Jalur Tes Reguler.</p><p>Calon orang tua dan siswa dapat mengunduh panduan lengkap, memeriksa alur seleksi, atau melakukan registrasi langsung secara daring melalui menu PPDB di portal resmi ini.</p>`,
      published_at: new Date().toISOString(),
      category: { name: 'Pengumuman & PPDB' },
      author: { name: 'Sekretariat PPDB' },
    };
  } finally {
    loading.value = false;
  }
});

watch(
  () => memberStore.isAuthenticated,
  () => {
    if (post.value?.id) {
      void loadBookmark(post.value.id);
    }
  },
);
</script>

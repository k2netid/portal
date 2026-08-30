<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full">
      <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_18rem] gap-10 items-start">
      <div class="space-y-10 min-w-0">
      <router-link
        to="/blog"
        class="inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors"
      >
        <ArrowLeft class="w-4 h-4" />
        {{ t('theme.sarangenge.pages.post.backToBlog', 'Back to Blog') }}
      </router-link>

      <div
        v-if="loading"
        class="min-h-[400px] flex items-center justify-center"
      >
        <div class="w-8 h-8 rounded-full border-2 border-primary border-t-transparent animate-spin" />
      </div>

      <article
        v-else-if="post"
        class="space-y-8"
      >
        <div class="space-y-4 text-center sm:text-left">
          <span
            v-if="post.category"
            class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20"
          >
            {{ post.category.name }}
          </span>
          <h1 class="text-3xl sm:text-5xl font-extrabold text-foreground font-heading leading-tight">
            {{ post.title }}
          </h1>
          <p class="text-sm text-muted-foreground">
            {{ t('theme.sarangenge.common.publishedOn', 'Published on') }} {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : 'Recently' }}
          </p>
        </div>

        <div
          v-if="post.featured_image"
          class="rounded-3xl overflow-hidden aspect-[16/9] border border-border/60 shadow-xl"
        >
          <img
            :src="post.featured_image"
            :alt="post.title"
            class="w-full h-full object-cover"
          >
        </div>

        <div class="prose prose-lg dark:prose-invert max-w-none text-foreground leading-relaxed pt-6">
          <ThemeSafeHtml :html="post.body || post.excerpt || ''" />
        </div>

        <div class="flex items-center justify-between gap-3 pt-4 border-t border-border/60">
          <Button
            variant="outline"
            size="sm"
            :disabled="!memberStore.isAuthenticated"
            @click="toggleBookmark"
          >
            {{ bookmarked
              ? t('theme.sarangenge.pages.post.bookmarked', 'Bookmarked')
              : t('theme.sarangenge.pages.post.bookmark', 'Bookmark') }}
          </Button>
          <router-link
            v-if="!memberStore.isAuthenticated"
            to="/member/login"
            class="text-sm text-primary font-semibold"
          >
            {{ t('theme.sarangenge.pages.post.signInToEngage', 'Sign in to comment or bookmark') }}
          </router-link>
        </div>

        <section class="space-y-4 pt-4">
          <h2 class="text-xl font-bold">
            {{ t('theme.sarangenge.pages.post.comments', 'Comments') }}
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
              class="w-full rounded-2xl border border-border bg-background px-3 py-2 text-sm"
              :placeholder="t('theme.sarangenge.pages.post.commentPlaceholder', 'Write a comment…')"
            />
            <Button
              type="submit"
              variant="primary"
              size="sm"
              :disabled="commentPending"
            >
              {{ t('theme.sarangenge.pages.post.postComment', 'Post comment') }}
            </Button>
          </form>
          <ul
            v-if="comments.length"
            class="space-y-4"
          >
            <li
              v-for="comment in comments"
              :key="comment.id"
              class="rounded-2xl border border-border/50 p-4"
            >
              <p class="text-sm font-semibold">
                {{ comment.member?.name || comment.name || t('theme.sarangenge.pages.post.reader', 'Reader') }}
              </p>
              <p class="text-sm text-muted-foreground mt-1">
                {{ comment.body }}
              </p>
            </li>
          </ul>
          <p
            v-else
            class="text-sm text-muted-foreground"
          >
            {{ t('theme.sarangenge.pages.post.noComments', 'No comments yet.') }}
          </p>
        </section>
      </article>

      <div
        v-else
        class="text-center py-20 text-muted-foreground"
      >
        {{ t('theme.sarangenge.pages.blog.noPosts', 'Article not found.') }}
      </div>
      </div>
        <WidgetArea location="sidebar" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import WidgetArea from '@/modules/Layout/components/widgets/WidgetArea.vue';
import { useMemberStore } from '@/modules/Member/stores/member';
import { ArrowLeft } from 'lucide-vue-next';

interface Post {
  id: string | number;
  title: string;
  slug: string;
  excerpt?: string;
  body?: string;
  featured_image?: string;
  published_at?: string;
  category?: { name: string };
}

interface PublicComment {
  id: string;
  body: string;
  name?: string | null;
  member?: { name?: string };
}

const { t } = useI18n();
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
  if (Array.isArray(payload)) {
    return payload as PublicComment[];
  }
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
  if (!post.value || !memberStore.isAuthenticated) {
    return;
  }
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
  if (!post.value || !commentBody.value.trim()) {
    return;
  }
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
      title: 'Building Modern Web Experiences with Vue 3 & Laravel',
      slug,
      body: '<p>Modern digital experiences demand lightning-fast reactivity paired with an uncompromising, rock-solid backend foundation. Jejakawan Core Engine delivers both through its modular monolith architecture.</p>',
      published_at: new Date().toISOString(),
      category: { name: 'Engineering' },
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

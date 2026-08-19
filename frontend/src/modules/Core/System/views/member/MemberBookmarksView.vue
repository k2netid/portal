<template>
  <div class="space-y-6">
    <!-- Header Hero Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl bg-card/80 border border-border/50 shadow-sm backdrop-blur-md">
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 border border-amber-500/20 flex items-center justify-center shrink-0 shadow-inner">
          <BookmarkIcon class="w-6 h-6" />
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground">
            {{ t('system.member.bookmarks.title', 'Artikel Tersimpan') }}
          </h1>
          <p class="text-xs sm:text-sm text-muted-foreground mt-0.5 font-medium">
            {{ t('system.member.bookmarks.subtitle', 'Daftar bacaan yang Anda simpan untuk dibaca kembali') }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <Button
          as-child
          size="sm"
          class="text-xs h-9 px-4 rounded-xl font-semibold gap-1.5 shadow-sm shadow-primary/20"
        >
          <router-link to="/blog">
            <Compass class="w-3.5 h-3.5" />
            <span>{{ t('system.member.bookmarks.explore', 'Jelajahi Artikel Lain') }}</span>
          </router-link>
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="flex items-center justify-center py-16"
    >
      <Spinner class="w-6 h-6 text-primary animate-spin" />
    </div>

    <!-- Bookmarks Container -->
    <div
      v-else
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5"
    >
      <Card
        v-for="item in bookmarks"
        :key="item.id"
        class="group overflow-hidden rounded-2xl border-border/50 hover:border-primary/40 transition-all hover:shadow-lg hover:shadow-primary/5 flex flex-col justify-between bg-card/60 backdrop-blur-sm"
      >
        <div class="p-5 space-y-3">
          <div class="flex items-center justify-between gap-2">
            <Badge
              v-if="item.content?.categories?.[0]"
              variant="outline"
              class="text-[10px] font-bold uppercase tracking-wider bg-primary/5 text-primary border-primary/20"
            >
              {{ item.content.categories[0].name }}
            </Badge>
            <span
              v-if="item.content?.reading_time"
              class="text-[11px] text-muted-foreground flex items-center gap-1"
            >
              <Clock class="w-3 h-3" />
              {{ item.content.reading_time }} {{ t('common.time.minutes', 'mnt') }}
            </span>
          </div>

          <h3 class="font-bold text-base text-foreground group-hover:text-primary transition-colors line-clamp-2">
            {{ item.content?.title || 'Untitled' }}
          </h3>

          <p
            v-if="item.content?.excerpt"
            class="text-xs text-muted-foreground line-clamp-3 leading-relaxed"
          >
            {{ item.content.excerpt }}
          </p>
        </div>

        <div class="p-5 pt-0 border-t border-border/30 mt-auto flex items-center justify-between gap-2">
          <span class="text-[11px] text-muted-foreground">
            {{ formatDate(item.created_at) }}
          </span>

          <div class="flex items-center gap-1.5">
            <Button
              as-child
              variant="ghost"
              size="sm"
              class="h-8 px-2.5 text-xs text-primary hover:text-primary hover:bg-primary/10 gap-1 rounded-xl"
            >
              <router-link :to="`/blog/${item.content?.slug || ''}`">
                <span>{{ t('common.actions.read', 'Baca') }}</span>
                <ArrowRight class="w-3 h-3" />
              </router-link>
            </Button>
            <Button
              variant="ghost"
              size="icon"
              class="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-xl"
              :title="t('common.actions.delete', 'Hapus dari Tersimpan')"
              :disabled="removingId === item.id"
              @click="removeBookmark(item.id)"
            >
              <Loader2
                v-if="removingId === item.id"
                class="w-3.5 h-3.5 animate-spin"
              />
              <Trash2
                v-else
                class="w-3.5 h-3.5"
              />
            </Button>
          </div>
        </div>
      </Card>

      <!-- Empty State -->
      <div
        v-if="bookmarks.length === 0"
        class="col-span-full py-16 px-4 text-center rounded-2xl border border-dashed border-border/60 bg-muted/10 space-y-4"
      >
        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto border border-amber-500/20 shadow-inner">
          <BookmarkIcon class="w-6 h-6" />
        </div>
        <div class="max-w-sm mx-auto">
          <h3 class="font-bold text-foreground text-sm">
            {{ t('system.member.bookmarks.emptyTitle', 'Belum Ada Artikel Tersimpan') }}
          </h3>
          <p class="text-xs text-muted-foreground mt-1">
            {{ t('system.member.bookmarks.emptySubtitle', 'Tandai artikel menarik saat membaca untuk menemukannya kembali di sini.') }}
          </p>
        </div>
        <Button
          as-child
          size="sm"
          class="text-xs h-9 rounded-xl"
        >
          <router-link to="/blog">
            <Compass class="w-3.5 h-3.5 mr-1.5" />
            {{ t('system.member.bookmarks.exploreNow', 'Mulai Eksplorasi') }}
          </router-link>
        </Button>
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="totalPages > 1"
      class="flex items-center justify-center gap-2 pt-4"
    >
      <Button
        variant="outline"
        size="sm"
        class="rounded-xl text-xs"
        :disabled="currentPage <= 1"
        @click="fetchBookmarks(currentPage - 1)"
      >
        {{ t('common.pagination.previous', 'Sebelumnya') }}
      </Button>
      <span class="text-xs text-muted-foreground font-medium px-2">
        {{ currentPage }} / {{ totalPages }}
      </span>
      <Button
        variant="outline"
        size="sm"
        class="rounded-xl text-xs"
        :disabled="currentPage >= totalPages"
        @click="fetchBookmarks(currentPage + 1)"
      >
        {{ t('common.pagination.next', 'Berikutnya') }}
      </Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/engine/api/client';
import { memberPaths } from '@/engine/api/paths';
import { Card, Badge, Button, Spinner } from '@/shared/components/ui';
import {
  Bookmark as BookmarkIcon,
  Clock,
  ArrowRight,
  Trash2,
  Compass,
  Loader2,
} from 'lucide-vue-next';

const { t } = useI18n();

interface BookmarkItem {
  id: string;
  content_id: string;
  created_at: string;
  content: {
    id: string;
    title: string;
    slug: string;
    excerpt: string;
    type: string;
    reading_time: number | null;
    published_at: string | null;
    featured_image: string | null;
    categories?: { id: string; name: string; slug: string }[];
  } | null;
}

const bookmarks = ref<BookmarkItem[]>([]);
const loading = ref(true);
const removingId = ref<string | null>(null);
const currentPage = ref(1);
const totalPages = ref(1);

const formatDate = (dateStr: string): string => {
  try {
    return new Date(dateStr).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    });
  } catch {
    return dateStr;
  }
};

const fetchBookmarks = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await apiClient.get(memberPaths.bookmarks, {
      params: { page, per_page: 12 },
    });
    bookmarks.value = data.data?.data || data.data || [];
    currentPage.value = data.data?.current_page || page;
    totalPages.value = data.data?.last_page || 1;
  } catch (e) {
    console.error('Failed to fetch bookmarks:', e);
    bookmarks.value = [];
  } finally {
    loading.value = false;
  }
};

const removeBookmark = async (id: string) => {
  removingId.value = id;
  try {
    await apiClient.delete(memberPaths.bookmark(id));
    bookmarks.value = bookmarks.value.filter((b) => b.id !== id);
  } catch (e) {
    console.error('Failed to remove bookmark:', e);
  } finally {
    removingId.value = null;
  }
};

onMounted(() => {
  fetchBookmarks();
});
</script>

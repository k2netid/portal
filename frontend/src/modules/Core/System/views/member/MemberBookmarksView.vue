<template>
  <div class="p-6 space-y-6">
    <!-- Page Header (Matches Console Standard) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-bold tracking-tight text-foreground">
          {{ t('system.member.bookmarks.title', 'Artikel Tersimpan') }}
        </h2>
        <p class="text-muted-foreground mt-1">
          {{ t('system.member.bookmarks.subtitle', 'Daftar bacaan yang Anda simpan untuk dibaca kembali') }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Button
          as-child
          size="sm"
          class="gap-2 rounded-xl"
        >
          <router-link to="/blog">
            <Compass class="w-4 h-4" />
            <span>{{ t('system.member.bookmarks.explore', 'Jelajahi Artikel Lain') }}</span>
          </router-link>
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="flex items-center justify-center py-20"
    >
      <Spinner class="w-8 h-8 text-primary animate-spin" />
    </div>

    <!-- Bookmarks Grid -->
    <div
      v-else-if="bookmarks.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <Card
        v-for="item in bookmarks"
        :key="item.id"
        class="group flex flex-col justify-between hover:border-primary/40 transition-all hover:shadow-md border-border bg-card"
      >
        <CardHeader class="p-5 pb-3 space-y-2.5">
          <div class="flex items-center justify-between gap-2">
            <Badge
              v-if="item.content?.categories?.[0]"
              variant="secondary"
              class="text-[11px] font-semibold"
            >
              {{ item.content.categories[0].name }}
            </Badge>
            <span
              v-if="item.content?.reading_time"
              class="text-xs text-muted-foreground flex items-center gap-1"
            >
              <Clock class="w-3 h-3" />
              {{ item.content.reading_time }} {{ t('common.time.minutes', 'mnt') }}
            </span>
          </div>

          <CardTitle class="text-base font-bold text-foreground group-hover:text-primary transition-colors line-clamp-2 leading-snug">
            {{ item.content?.title || 'Untitled' }}
          </CardTitle>

          <CardDescription
            v-if="item.content?.excerpt"
            class="text-xs text-muted-foreground line-clamp-3 leading-relaxed"
          >
            {{ item.content.excerpt }}
          </CardDescription>
        </CardHeader>

        <CardFooter class="p-5 pt-3 border-t border-border mt-auto flex items-center justify-between gap-2">
          <span class="text-xs text-muted-foreground">
            {{ formatDate(item.created_at) }}
          </span>

          <div class="flex items-center gap-1">
            <Button
              as-child
              variant="ghost"
              size="sm"
              class="h-8 px-2.5 text-xs text-primary hover:text-primary hover:bg-primary/10 gap-1 rounded-lg"
            >
              <router-link :to="`/blog/${item.content?.slug || ''}`">
                <span>{{ t('common.actions.read', 'Baca') }}</span>
                <ArrowRight class="w-3 h-3" />
              </router-link>
            </Button>
            <Button
              variant="ghost"
              size="icon"
              class="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg"
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
        </CardFooter>
      </Card>
    </div>

    <!-- Empty State -->
    <Card
      v-else
      class="border-dashed border-2 py-16 px-4 text-center bg-card/40"
    >
      <CardContent class="p-0 space-y-4 max-w-sm mx-auto">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mx-auto border border-primary/20">
          <BookmarkIcon class="w-6 h-6" />
        </div>
        <div>
          <h3 class="font-bold text-foreground text-base">
            {{ t('system.member.bookmarks.emptyTitle', 'Belum Ada Artikel Tersimpan') }}
          </h3>
          <p class="text-xs text-muted-foreground mt-1">
            {{ t('system.member.bookmarks.emptySubtitle', 'Tandai artikel menarik saat membaca untuk menemukannya kembali di sini.') }}
          </p>
        </div>
        <Button
          as-child
          size="sm"
          class="gap-1.5 rounded-xl"
        >
          <router-link to="/blog">
            <Compass class="w-4 h-4" />
            <span>{{ t('system.member.bookmarks.exploreNow', 'Mulai Eksplorasi') }}</span>
          </router-link>
        </Button>
      </CardContent>
    </Card>

    <!-- Pagination -->
    <div
      v-if="totalPages > 1"
      class="flex items-center justify-center gap-2 pt-2"
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
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter,
  Badge,
  Button,
  Spinner,
} from '@/shared/components/ui';
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

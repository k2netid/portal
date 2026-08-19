<template>
  <div class="p-6 space-y-6">
    <!-- Page Header (Matches Console Standard) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-bold tracking-tight text-foreground">
          {{ t('system.member.comments.title', 'Riwayat Komentar & Diskusi') }}
        </h2>
        <p class="text-muted-foreground mt-1">
          {{ t('system.member.comments.subtitle', 'Semua komentar dan partisipasi diskusi Anda pada artikel CMS') }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Button
          as-child
          variant="outline"
          size="sm"
          class="gap-2 rounded-xl"
        >
          <router-link to="/blog">
            <Compass class="w-4 h-4" />
            <span>{{ t('system.member.bookmarks.explore', 'Jelajahi Artikel') }}</span>
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

    <!-- Comments List -->
    <div
      v-else-if="comments.length > 0"
      class="space-y-4"
    >
      <Card
        v-for="item in comments"
        :key="item.id"
        class="border-border bg-card hover:border-border/80 transition-colors"
      >
        <CardContent class="p-5 space-y-3">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-border pb-3">
            <div class="flex items-center gap-2.5">
              <Badge
                :variant="badgeVariant(item.status)"
                class="text-[10px] font-bold uppercase tracking-wider"
              >
                {{ statusLabel(item.status) }}
              </Badge>
              <span class="text-xs text-muted-foreground">{{ formatDate(item.created_at) }}</span>
            </div>

            <router-link
              v-if="item.content"
              :to="`/blog/${item.content.slug}`"
              class="text-xs font-semibold text-primary hover:underline flex items-center gap-1 truncate max-w-sm"
            >
              <span>{{ item.content.title }}</span>
              <ArrowUpRight class="w-3.5 h-3.5 shrink-0" />
            </router-link>
          </div>

          <p class="text-sm text-foreground leading-relaxed bg-muted/30 p-4 rounded-xl border border-border/50">
            "{{ item.body }}"
          </p>
        </CardContent>
      </Card>
    </div>

    <!-- Empty State -->
    <Card
      v-else
      class="border-dashed border-2 py-16 px-4 text-center bg-card/40"
    >
      <CardContent class="p-0 space-y-4 max-w-sm mx-auto">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mx-auto border border-primary/20">
          <MessageSquare class="w-6 h-6" />
        </div>
        <div>
          <h3 class="font-bold text-foreground text-base">
            {{ t('system.member.comments.emptyTitle', 'Belum Ada Komentar') }}
          </h3>
          <p class="text-xs text-muted-foreground mt-1">
            {{ t('system.member.comments.emptySubtitle', 'Bergabunglah dalam diskusi artikel untuk melihat riwayat komentar Anda di sini.') }}
          </p>
        </div>
        <Button
          as-child
          size="sm"
          class="gap-1.5 rounded-xl"
        >
          <router-link to="/blog">
            <Compass class="w-4 h-4" />
            <span>{{ t('system.member.bookmarks.exploreNow', 'Jelajahi Artikel') }}</span>
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
        @click="fetchComments(currentPage - 1)"
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
        @click="fetchComments(currentPage + 1)"
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
  CardContent,
  Badge,
  Button,
  Spinner,
} from '@/shared/components/ui';
import {
  MessageSquare,
  ArrowUpRight,
  Compass,
} from 'lucide-vue-next';

const { t } = useI18n();

interface CommentItem {
  id: string;
  body: string;
  status: string;
  created_at: string;
  content: {
    id: string;
    title: string;
    slug: string;
    type: string;
  } | null;
}

const comments = ref<CommentItem[]>([]);
const loading = ref(true);
const currentPage = ref(1);
const totalPages = ref(1);

const formatDate = (dateStr: string): string => {
  try {
    return new Date(dateStr).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch {
    return dateStr;
  }
};

const badgeVariant = (status: string): 'default' | 'secondary' | 'outline' | 'destructive' => {
  switch (status) {
    case 'approved':
      return 'default';
    case 'pending':
      return 'secondary';
    case 'rejected':
      return 'destructive';
    default:
      return 'outline';
  }
};

const statusLabel = (status: string): string => {
  switch (status) {
    case 'approved':
      return t('common.status.approved', 'Disetujui');
    case 'pending':
      return t('common.status.pending', 'Menunggu Moderasi');
    case 'rejected':
      return t('common.status.rejected', 'Ditolak');
    case 'spam':
      return t('common.status.spam', 'Spam');
    default:
      return status;
  }
};

const fetchComments = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await apiClient.get(memberPaths.comments, {
      params: { page, per_page: 15 },
    });
    comments.value = data.data?.data || data.data || [];
    currentPage.value = data.data?.current_page || page;
    totalPages.value = data.data?.last_page || 1;
  } catch (e) {
    console.error('Failed to fetch comments:', e);
    comments.value = [];
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchComments();
});
</script>

<template>
  <div class="space-y-6">
    <!-- Header Hero Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl bg-card/80 border border-border/50 shadow-sm backdrop-blur-md">
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-500 border border-blue-500/20 flex items-center justify-center shrink-0 shadow-inner">
          <MessageSquare class="w-6 h-6" />
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground">
            {{ t('system.member.comments.title', 'Riwayat Komentar & Diskusi') }}
          </h1>
          <p class="text-xs sm:text-sm text-muted-foreground mt-0.5 font-medium">
            {{ t('system.member.comments.subtitle', 'Semua komentar dan partisipasi diskusi Anda pada artikel CMS') }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <Button
          as-child
          variant="outline"
          size="sm"
          class="text-xs h-9 px-4 rounded-xl font-semibold gap-1.5 border-border/50 hover:bg-muted/60"
        >
          <router-link to="/blog">
            <Compass class="w-3.5 h-3.5" />
            <span>{{ t('system.member.bookmarks.explore', 'Jelajahi Artikel') }}</span>
          </router-link>
        </Button>
      </div>
    </div>

    <!-- Comments List -->
    <div class="space-y-4">
      <Card
        v-for="item in comments"
        :key="item.id"
        class="rounded-2xl border-border/50 p-5 space-y-3.5 hover:border-border transition-all bg-card/60 backdrop-blur-sm"
      >
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-border/30 pb-3">
          <div class="flex items-center gap-2.5">
            <Badge
              :variant="item.status === 'approved' ? 'default' : 'secondary'"
              class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full"
              :class="item.status === 'approved' ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/20'"
            >
              {{ item.status === 'approved' ? t('common.status.approved', 'Disetujui') : t('common.status.pending', 'Menunggu Moderasi') }}
            </Badge>
            <span class="text-xs text-muted-foreground">{{ item.date }}</span>
          </div>

          <router-link
            :to="`/blog/${item.articleSlug}`"
            class="text-xs font-semibold text-primary hover:underline flex items-center gap-1 truncate max-w-sm"
          >
            <span>{{ item.articleTitle }}</span>
            <ArrowUpRight class="w-3.5 h-3.5 shrink-0" />
          </router-link>
        </div>

        <p class="text-xs sm:text-sm text-foreground/90 leading-relaxed bg-muted/20 p-4 rounded-xl border border-border/20">
          "{{ item.content }}"
        </p>
      </Card>

      <!-- Empty State -->
      <div
        v-if="comments.length === 0"
        class="py-16 px-4 text-center rounded-2xl border border-dashed border-border/60 bg-muted/10 space-y-4"
      >
        <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center mx-auto border border-blue-500/20 shadow-inner">
          <MessageSquare class="w-6 h-6" />
        </div>
        <div class="max-w-sm mx-auto">
          <h3 class="font-bold text-foreground text-sm">
            {{ t('system.member.comments.emptyTitle', 'Belum Ada Komentar') }}
          </h3>
          <p class="text-xs text-muted-foreground mt-1">
            {{ t('system.member.comments.emptySubtitle', 'Bergabunglah dalam diskusi artikel untuk melihat riwayat komentar Anda di sini.') }}
          </p>
        </div>
        <Button
          as-child
          size="sm"
          class="text-xs h-9 rounded-xl"
        >
          <router-link to="/blog">
            <Compass class="w-3.5 h-3.5 mr-1.5" />
            {{ t('system.member.bookmarks.exploreNow', 'Jelajahi Artikel') }}
          </router-link>
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Card, Badge, Button } from '@/shared/components/ui';
import {
  MessageSquare,
  ArrowUpRight,
  Compass,
} from 'lucide-vue-next';

const { t } = useI18n();

interface CommentItem {
  id: string;
  articleTitle: string;
  articleSlug: string;
  content: string;
  date: string;
  status: 'approved' | 'pending';
}

const comments = ref<CommentItem[]>([
  {
    id: '1',
    articleTitle: 'Transformasi Arsitektur Digital & Keamanan Web Modern 2026',
    articleSlug: 'transformasi-arsitektur-digital-2026',
    content: 'Artikel yang sangat membuka wawasan! Pendekatan multi-gate authentication dan anti-reconnaissance ini benar-benar penting untuk keamanan CMS modern.',
    date: '18 Agu 2026, 14:30',
    status: 'approved',
  },
]);
</script>

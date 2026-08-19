<template>
  <div class="space-y-6">
    <!-- Header Card -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl bg-gradient-to-br from-card/80 to-card/40 border border-border/50 shadow-sm backdrop-blur-md">
      <div>
        <div class="flex items-center gap-2.5">
          <div class="p-2 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20">
            <Bookmark class="w-5 h-5" />
          </div>
          <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-foreground">
              {{ t('system.member.bookmarks.title', 'Artikel Tersimpan') }}
            </h1>
            <p class="text-xs sm:text-sm text-muted-foreground mt-0.5">
              {{ t('system.member.bookmarks.subtitle', 'Daftar bacaan yang Anda simpan untuk dibaca kembali') }}
            </p>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <Button
          as-child
          size="sm"
          class="text-xs h-9 px-3.5 rounded-lg font-semibold gap-1.5"
        >
          <router-link to="/blog">
            <Compass class="w-3.5 h-3.5" />
            {{ t('system.member.bookmarks.explore', 'Jelajahi Artikel Lain') }}
          </router-link>
        </Button>
      </div>
    </div>

    <!-- Bookmarks Container -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <Card
        v-for="item in bookmarks"
        :key="item.id"
        class="group overflow-hidden rounded-2xl border-border/50 hover:border-primary/40 transition-all hover:shadow-lg hover:shadow-primary/5 flex flex-col justify-between"
      >
        <div class="p-5 space-y-3">
          <div class="flex items-center justify-between gap-2">
            <Badge
              variant="outline"
              class="text-[10px] font-bold uppercase tracking-wider bg-primary/5 text-primary border-primary/20"
            >
              {{ item.category }}
            </Badge>
            <span class="text-[11px] text-muted-foreground flex items-center gap-1">
              <Clock class="w-3 h-3" />
              {{ item.readTime }}
            </span>
          </div>

          <h3 class="font-bold text-base text-foreground group-hover:text-primary transition-colors line-clamp-2">
            {{ item.title }}
          </h3>

          <p class="text-xs text-muted-foreground line-clamp-3 leading-relaxed">
            {{ item.excerpt }}
          </p>
        </div>

        <div class="p-5 pt-0 border-t border-border/30 mt-auto flex items-center justify-between gap-2">
          <span class="text-[11px] text-muted-foreground">
            {{ item.date }}
          </span>

          <div class="flex items-center gap-1.5">
            <Button
              as-child
              variant="ghost"
              size="sm"
              class="h-8 px-2.5 text-xs text-primary hover:text-primary hover:bg-primary/10 gap-1 rounded-lg"
            >
              <router-link :to="`/blog/${item.slug}`">
                <span>{{ t('common.actions.read', 'Baca') }}</span>
                <ArrowRight class="w-3 h-3" />
              </router-link>
            </Button>
            <Button
              variant="ghost"
              size="icon"
              class="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg"
              :title="t('common.actions.delete', 'Hapus dari Tersimpan')"
              @click="removeBookmark(item.id)"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </Button>
          </div>
        </div>
      </Card>

      <!-- Empty State -->
      <div
        v-if="bookmarks.length === 0"
        class="col-span-full py-16 px-4 text-center rounded-2xl border border-dashed border-border/60 bg-muted/10 space-y-4"
      >
        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto border border-amber-500/20">
          <Bookmark class="w-6 h-6" />
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
          class="text-xs h-9 rounded-lg"
        >
          <router-link to="/blog">
            <Compass class="w-3.5 h-3.5 mr-1.5" />
            {{ t('system.member.bookmarks.exploreNow', 'Mulai Eksplorasi') }}
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
  Bookmark,
  Clock,
  ArrowRight,
  Trash2,
  Compass,
} from 'lucide-vue-next';

const { t } = useI18n();

interface BookmarkItem {
  id: string;
  title: string;
  slug: string;
  excerpt: string;
  category: string;
  date: string;
  readTime: string;
}

const bookmarks = ref<BookmarkItem[]>([
  {
    id: '1',
    title: 'Transformasi Arsitektur Digital & Keamanan Web Modern 2026',
    slug: 'transformasi-arsitektur-digital-2026',
    excerpt: 'Panduan komprehensif implementasi microfrontend, keamanan zero-trust, dan optimasi performa web skala besar.',
    category: 'Teknologi',
    date: '18 Agu 2026',
    readTime: '5 mnt',
  },
  {
    id: '2',
    title: 'Eksplorasi Inovasi Desain UI/UX: Glassmorphism & Micro-animations',
    slug: 'eksplorasi-inovasi-desain-ui-ux',
    excerpt: 'Membangun antarmuka modern yang memikat pengguna dengan prinsip tata letak dinamis dan estetika kelas atas.',
    category: 'Desain',
    date: '15 Agu 2026',
    readTime: '4 mnt',
  },
]);

const removeBookmark = (id: string) => {
  bookmarks.value = bookmarks.value.filter((b) => b.id !== id);
};
</script>

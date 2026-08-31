<template>
  <article
    class="sarangenge-bento__cell !p-0 overflow-hidden cursor-pointer flex flex-col h-full group"
    @click="navigateToPost"
  >
    <!-- Featured Image -->
    <div class="relative h-48 sm:h-52 w-full overflow-hidden bg-muted">
      <img
        v-if="post.featured_image"
        v-lazy="post.featured_image"
        :alt="post.title"
        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
        width="640"
        height="384"
        decoding="async"
        sizes="(max-width: 768px) 100vw, (max-width: 1280px) 50vw, 33vw"
      >
      <div
        v-else
        class="w-full h-full flex items-center justify-center bg-slate-100 dark:bg-slate-800/60 text-slate-400"
      >
        <Newspaper class="w-10 h-10 opacity-40" />
      </div>

      <div
        v-if="post.category"
        class="absolute top-3 left-3"
      >
        <span class="px-2.5 py-1 bg-background/90 backdrop-blur-md text-primary text-xs font-bold rounded-full shadow border border-border/60">
          {{ post.category.name }}
        </span>
      </div>
    </div>

    <!-- Body -->
    <div class="p-5 sm:p-6 flex flex-col flex-1 gap-3">
      <div class="flex items-center gap-3 text-xs text-muted-foreground">
        <span
          v-if="post.published_at"
          class="inline-flex items-center gap-1"
        >
          <Calendar class="w-3.5 h-3.5" />
          {{ formatDate(post.published_at) }}
        </span>
        <span class="inline-flex items-center gap-1">
          <Clock class="w-3.5 h-3.5" />
          {{ readTime }}
        </span>
      </div>

      <h3 class="text-lg font-bold text-foreground font-heading group-hover:text-[var(--sarangenge-teal,#0f766e)] transition-colors line-clamp-2">
        {{ post.title }}
      </h3>

      <p class="text-muted-foreground text-sm line-clamp-2 leading-relaxed">
        {{ excerpt }}
      </p>

      <div class="pt-3 mt-auto border-t border-border/60 flex items-center justify-between text-xs text-muted-foreground">
        <span class="font-semibold text-foreground/80">
          {{ post.author?.name || 'Redaksi Sekolah' }}
        </span>
        <span class="text-[var(--sarangenge-teal,#0f766e)] font-bold inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
          {{ t('common.readMore', 'Baca Selengkapnya') }}
          <ArrowRight class="w-3.5 h-3.5" />
        </span>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import type { Content } from '@/modules/Publishing/types/content';
import { Calendar, Clock, ArrowRight, Newspaper } from 'lucide-vue-next';

interface Props {
  post: Content;
}

const props = defineProps<Props>();
const { t, locale } = useThemeI18n('sarangenge');
const router = useRouter();

const excerpt = computed(() => {
  if (props.post.excerpt) return props.post.excerpt;
  if (props.post.body) return props.post.body.replace(/<[^>]*>/g, '').substring(0, 140) + '...';
  return '';
});

const readTime = computed(() => {
  const words = (props.post.body || '').replace(/<[^>]*>/g, '').split(/\s+/).length;
  const minutes = Math.max(1, Math.ceil(words / 200));
  return `${minutes} min`;
});

const formatDate = (date?: string) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString(locale.value, {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const navigateToPost = () => {
  router.push(`/blog/${props.post.slug}`);
};
</script>

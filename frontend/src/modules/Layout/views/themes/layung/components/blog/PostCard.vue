<template>
  <article class="layung-panel overflow-hidden flex flex-col justify-between group hover:-translate-y-1 transition-all duration-300">
    <div class="relative aspect-video w-full overflow-hidden bg-slate-900">
      <img
        v-if="post.featured_image"
        :src="post.featured_image"
        :alt="post.title"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
        loading="lazy"
      >
      <div
        v-else
        class="w-full h-full flex items-center justify-center bg-slate-900 text-slate-600"
      >
        <Newspaper class="w-10 h-10 opacity-40" />
      </div>

      <div
        v-if="post.category"
        class="absolute top-3 left-3"
      >
        <span class="px-2.5 py-1 bg-slate-950/80 backdrop-blur-md text-orange-400 text-xs font-bold rounded-full shadow border border-orange-500/30 font-mono">
          {{ post.category.name }}
        </span>
      </div>
    </div>

    <div class="p-6 space-y-3 flex-1 flex flex-col justify-between">
      <div class="space-y-2">
        <div class="flex items-center gap-3 text-xs text-muted-foreground font-mono">
          <span v-if="post.published_at">{{ formatDate(post.published_at) }}</span>
          <span v-if="post.read_time">· {{ post.read_time }} mnt baca</span>
        </div>

        <h3 class="text-lg font-bold text-foreground group-hover:text-primary transition-colors font-heading leading-snug line-clamp-2">
          <router-link :to="`/blog/${post.slug}`">
            {{ post.title }}
          </router-link>
        </h3>

        <p
          v-if="post.excerpt"
          class="text-xs text-muted-foreground line-clamp-3 leading-relaxed"
        >
          {{ post.excerpt }}
        </p>
      </div>

      <div class="pt-4 border-t border-border/60 flex items-center justify-between">
        <router-link
          :to="`/blog/${post.slug}`"
          class="text-xs font-bold text-primary hover:underline inline-flex items-center gap-1"
        >
          <span>Baca Selengkapnya</span>
          <ArrowRight class="w-3.5 h-3.5" />
        </router-link>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { Newspaper, ArrowRight } from 'lucide-vue-next';

interface PostItem {
  id?: string | number;
  title: string;
  slug: string;
  excerpt?: string;
  featured_image?: string;
  published_at?: string;
  read_time?: number;
  category?: { name: string };
  author?: { name: string };
}

defineProps<{
  post: PostItem;
}>();

const formatDate = (val: string) => {
  try {
    return new Date(val).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    });
  } catch {
    return val;
  }
};
</script>

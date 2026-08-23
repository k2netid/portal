<template>
  <article
    class="bg-card rounded-lg overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 cursor-pointer flex flex-col h-full border border-border group hover:border-primary/30"
    @click="navigateToPost"
  >
    <!-- Featured Image -->
    <div class="relative h-48 overflow-hidden bg-muted">
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
        v-if="post.category"
        class="absolute top-4 left-4"
      >
        <span class="px-3 py-1.5 bg-background/80 backdrop-blur-md border border-primary/20 text-primary text-xs font-bold rounded-full shadow-lg hover:shadow-xl transition-all">
          {{ post.category.name }}
        </span>
      </div>
    </div>
    
    <!-- Content -->
    <div class="p-6 flex flex-col flex-1 gap-4">
      <div class="flex-1 space-y-2">
        <h3 class="text-xl font-bold text-foreground group-hover:text-primary transition-colors line-clamp-2">
          {{ post.title }}
        </h3>
        <p class="text-muted-foreground text-sm line-clamp-3 leading-relaxed">
          {{ excerpt }}
        </p>
      </div>
      
      <!-- Meta -->
      <div class="border-t border-border pt-4 mt-auto flex items-center justify-between text-xs text-muted-foreground">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold">
            {{ post.author?.name ? post.author.name[0] : 'A' }}
          </div>
          <span>{{ post.author?.name || 'Author' }}</span>
        </div>
        <span class="flex items-center gap-1">
          <Calendar class="w-4 h-4" />
          {{ formatDate(post.published_at) }}
        </span>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import type { Content } from '@/modules/Publishing/types/content'
import { Calendar } from 'lucide-vue-next'

interface Props {
  post: Content;
}

const props = defineProps<Props>()
const { locale } = useI18n()
const router = useRouter()

const excerpt = computed(() => {
  if (props.post.excerpt) return props.post.excerpt
  if (props.post.body) return props.post.body.replace(/<[^>]*>/g, '').substring(0, 150) + '...'
  return ''
})

const formatDate = (date?: string) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString(locale.value, {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const navigateToPost = () => {
  router.push(`/blog/${props.post.slug}`)
}
</script>

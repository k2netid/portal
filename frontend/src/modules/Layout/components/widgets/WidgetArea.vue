<template>
  <aside
    v-if="widgets.length > 0"
    class="widget-area space-y-6"
    :data-widget-location="location"
  >
    <section
      v-for="widget in widgets"
      :key="widget.id"
      class="rounded-2xl border border-border/60 bg-card/60 p-5 space-y-3"
    >
      <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">
        {{ widget.title }}
      </h3>

      <ThemeSafeHtml
        v-if="widget.type === 'html' || widget.type === 'custom'"
        :html="widget.content || ''"
      />

      <p
        v-else-if="widget.type === 'text'"
        class="text-sm text-muted-foreground leading-relaxed whitespace-pre-wrap"
      >
        {{ widget.content }}
      </p>

      <ul
        v-else-if="widget.type === 'recent_posts' || widget.type === 'content_list'"
        class="space-y-2"
      >
        <li
          v-for="item in widget.items || []"
          :key="item.id"
        >
          <router-link
            :to="`/blog/${item.slug}`"
            class="text-sm font-medium hover:text-primary"
          >
            {{ item.title }}
          </router-link>
        </li>
      </ul>

      <ul
        v-else-if="widget.type === 'categories'"
        class="space-y-2"
      >
        <li
          v-for="item in widget.items || []"
          :key="item.id"
        >
          <router-link
            :to="{ path: '/blog', query: item.slug ? { category: item.slug } : {} }"
            class="text-sm text-muted-foreground hover:text-foreground"
          >
            {{ item.name }}
          </router-link>
        </li>
      </ul>

      <p
        v-else-if="widget.content"
        class="text-sm text-muted-foreground"
      >
        {{ widget.content }}
      </p>
    </section>
  </aside>
</template>

<script setup lang="ts">
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import { usePublicWidgets } from '@/modules/Layout/composables/usePublicWidgets';

const props = defineProps<{
    location: string;
}>();

const { widgets } = usePublicWidgets(props.location);
</script>

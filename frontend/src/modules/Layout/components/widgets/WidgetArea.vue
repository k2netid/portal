<template>
  <aside
    class="widget-area space-y-6"
    :data-widget-location="location"
  >
    <!-- Dynamic DB widgets when configured in lay_widgets -->
    <template v-if="widgets && widgets.length > 0">
      <template
        v-for="widget in widgets"
        :key="widget.id"
      >
        <!-- Search widget -->
        <SearchWidget
          v-if="widget.type === 'search'"
          :widget="widget"
        />

        <!-- Categories widget -->
        <CategoriesWidget
          v-else-if="widget.type === 'categories'"
          :widget="widget"
        />

        <!-- Recent posts widget -->
        <RecentPostsWidget
          v-else-if="widget.type === 'recent_posts' || widget.type === 'content_list'"
          :widget="widget"
          :current-post-slug="context?.post?.slug"
        />

        <!-- Newsletter widget -->
        <NewsletterWidget
          v-else-if="widget.type === 'newsletter'"
          :widget="widget"
        />

        <!-- Social share widget -->
        <SocialShareWidget
          v-else-if="widget.type === 'social_share'"
          :widget="widget"
          :post="context?.post"
        />

        <!-- HTML / Custom widget -->
        <div
          v-else-if="widget.type === 'html' || widget.type === 'custom'"
          class="universal-widget custom-html-widget rounded-2xl border border-border/70 bg-card p-5 shadow-sm space-y-3"
        >
          <h3
            v-if="widget.title"
            class="text-sm font-bold uppercase tracking-wider text-foreground font-heading"
          >
            {{ widget.title }}
          </h3>
          <ThemeSafeHtml :html="widget.content || ''" />
        </div>

        <!-- Plain text widget -->
        <div
          v-else-if="widget.type === 'text'"
          class="universal-widget text-widget rounded-2xl border border-border/70 bg-card p-5 shadow-sm space-y-3"
        >
          <h3
            v-if="widget.title"
            class="text-sm font-bold uppercase tracking-wider text-foreground font-heading"
          >
            {{ widget.title }}
          </h3>
          <p class="text-sm text-muted-foreground leading-relaxed whitespace-pre-wrap">
            {{ widget.content }}
          </p>
        </div>

        <!-- Generic fallback container -->
        <div
          v-else-if="widget.content"
          class="universal-widget generic-widget rounded-2xl border border-border/70 bg-card p-5 shadow-sm space-y-3"
        >
          <h3
            v-if="widget.title"
            class="text-sm font-bold uppercase tracking-wider text-foreground font-heading"
          >
            {{ widget.title }}
          </h3>
          <p class="text-sm text-muted-foreground">
            {{ widget.content }}
          </p>
        </div>
      </template>
    </template>

    <!-- Smart Fallback when database has no widgets configured -->
    <template v-else-if="!loading && enableFallback">
      <slot :context="context">
        <!-- Default standard Universal Widget Stack -->
        <SearchWidget />
        <CategoriesWidget />
        <RecentPostsWidget :current-post-slug="context?.post?.slug" />
        <SocialShareWidget :post="context?.post" />
        <NewsletterWidget />
      </slot>
    </template>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import { usePublicWidgets } from '@/modules/Layout/composables/usePublicWidgets';
import SearchWidget from './SearchWidget.vue';
import CategoriesWidget from './CategoriesWidget.vue';
import RecentPostsWidget from './RecentPostsWidget.vue';
import NewsletterWidget from './NewsletterWidget.vue';
import SocialShareWidget from './SocialShareWidget.vue';

const props = withDefaults(
  defineProps<{
    location: string;
    context?: Record<string, any>;
    fallback?: boolean;
  }>(),
  {
    context: () => ({}),
    fallback: true,
  }
);

const { widgets, loading } = usePublicWidgets(computed(() => props.location));
const enableFallback = computed(() => props.fallback !== false);
</script>

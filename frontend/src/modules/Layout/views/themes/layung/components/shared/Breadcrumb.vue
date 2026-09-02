<template>
  <div class="layung-breadcrumb-wrapper">
    <!-- Desktop: Full breadcrumb trail -->
    <nav
      class="layung-breadcrumb hidden sm:inline-flex"
      :aria-label="t('common.breadcrumbAria', 'Breadcrumb')"
    >
      <router-link
        to="/"
        class="layung-breadcrumb__home shrink-0"
      >
        <Home class="w-3.5 h-3.5" />
        <span>{{ t('header.home', 'Beranda') }}</span>
      </router-link>

      <template
        v-for="(item, idx) in items"
        :key="idx"
      >
        <ChevronRight class="w-3.5 h-3.5 layung-breadcrumb__sep shrink-0" />
        <router-link
          v-if="item.path"
          :to="item.path"
          class="layung-breadcrumb__link truncate max-w-[200px]"
        >
          {{ item.name }}
        </router-link>
        <span
          v-else
          class="layung-breadcrumb__current truncate max-w-[240px]"
          aria-current="page"
        >
          {{ item.name }}
        </span>
      </template>
    </nav>

    <!-- Mobile (< sm): Smart compact Back link that never overflows or blocks content -->
    <nav
      v-if="parentItem"
      class="layung-breadcrumb-mobile sm:hidden inline-flex items-center"
      :aria-label="t('common.breadcrumbAria', 'Breadcrumb')"
    >
      <router-link
        :to="parentItem.path || '/'"
        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-medium bg-card/85 backdrop-blur-md border border-border/80 text-muted-foreground hover:text-foreground shadow-sm transition-colors max-w-[calc(100vw-2.5rem)]"
      >
        <ChevronLeft class="w-3.5 h-3.5 text-sky-500 shrink-0" />
        <span class="truncate">{{ parentItem.name }}</span>
      </router-link>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Home, ChevronRight, ChevronLeft } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';

const props = defineProps<{
  items: Array<{ name: string; path?: string }>;
}>();

const { t } = useThemeI18n('layung');

const parentItem = computed(() => {
  if (!props.items || props.items.length === 0) return null;
  // If there are 2 or more items, the parent is the second to last item
  if (props.items.length >= 2) {
    const parent = props.items[props.items.length - 2];
    if (parent?.path) return parent;
  }
  // Otherwise return link to Home
  return { name: t('header.home', 'Beranda'), path: '/' };
});
</script>

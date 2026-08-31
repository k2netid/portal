<template>
  <nav
    class="flex items-center gap-2 text-xs text-muted-foreground font-mono"
    :aria-label="t('common.breadcrumbAria', 'Breadcrumb')"
  >
    <router-link
      to="/"
      class="hover:text-primary transition-colors flex items-center gap-1 font-semibold"
    >
      <Home class="w-3.5 h-3.5" />
      <span>{{ t('header.home', 'Beranda') }}</span>
    </router-link>

    <template
      v-for="(item, idx) in items"
      :key="idx"
    >
      <ChevronRight class="w-3.5 h-3.5 opacity-40 shrink-0" />
      <router-link
        v-if="item.path"
        :to="item.path"
        class="hover:text-primary transition-colors truncate"
      >
        {{ item.name }}
      </router-link>
      <span
        v-else
        class="text-foreground font-bold truncate"
        aria-current="page"
      >
        {{ item.name }}
      </span>
    </template>
  </nav>
</template>

<script setup lang="ts">
import { Home, ChevronRight } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';

defineProps<{
  items: Array<{ name: string; path?: string }>;
}>();

const { t } = useThemeI18n('layung');
</script>

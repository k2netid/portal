<template>
  <nav
    class="flex items-center gap-2 text-xs text-muted-foreground py-3"
    :aria-label="t('common.breadcrumbAria', 'Breadcrumb')"
  >
    <router-link
      to="/"
      class="hover:text-foreground transition-colors inline-flex items-center gap-1"
    >
      <Home class="w-3.5 h-3.5" />
      <span>{{ t('header.home', 'Beranda') }}</span>
    </router-link>

    <template
      v-for="(item, idx) in items"
      :key="idx"
    >
      <ChevronRight class="w-3.5 h-3.5 opacity-50 shrink-0" />
      <router-link
        v-if="item.path && idx < items.length - 1"
        :to="item.path"
        class="hover:text-foreground transition-colors truncate max-w-[160px] sm:max-w-xs"
      >
        {{ item.name }}
      </router-link>
      <span
        v-else
        class="text-foreground font-semibold truncate max-w-[180px] sm:max-w-md"
        aria-current="page"
      >
        {{ item.name }}
      </span>
    </template>
  </nav>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Home, ChevronRight } from 'lucide-vue-next';

interface BreadcrumbItem {
  name: string;
  path?: string;
}

defineProps<{
  items: BreadcrumbItem[];
}>();

const { t } = useThemeI18n('sarangenge');
</script>

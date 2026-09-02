<template>
  <nav
    class="layung-breadcrumb"
    :aria-label="t('common.breadcrumbAria', 'Breadcrumb')"
  >
    <router-link
      to="/"
      class="layung-breadcrumb__home"
    >
      <Home class="w-3.5 h-3.5" />
      <span>{{ t('header.home', 'Beranda') }}</span>
    </router-link>

    <template
      v-for="(item, idx) in items"
      :key="idx"
    >
      <ChevronRight class="w-3.5 h-3.5 layung-breadcrumb__sep" />
      <router-link
        v-if="item.path"
        :to="item.path"
        class="layung-breadcrumb__link truncate"
      >
        {{ item.name }}
      </router-link>
      <span
        v-else
        class="layung-breadcrumb__current truncate"
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


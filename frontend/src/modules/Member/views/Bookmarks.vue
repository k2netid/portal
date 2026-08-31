<template>
  <div class="space-y-6">
    <div class="space-y-1">
      <h2 class="text-xl font-bold">
        {{ t('member.nav.bookmarks', 'Bookmarks') }}
      </h2>
      <p class="text-sm text-muted-foreground">
        {{ t('member.portal.bookmarks.subtitle', 'Articles you saved for later.') }}
      </p>
    </div>

    <RecentBookmarksWidget
      :limit="50"
      :show-view-all="false"
    />
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import RecentBookmarksWidget from '@/modules/Member/components/RecentBookmarksWidget.vue';

const { t } = useI18n();
</script>

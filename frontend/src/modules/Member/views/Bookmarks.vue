<template>
  <MemberPage
    :title="t('member.nav.bookmarks', 'Bookmarks')"
    :subtitle="t('member.portal.bookmarks.subtitle', 'Articles you saved for later.')"
  >
    <RecentBookmarksWidget
      :limit="50"
      :show-header="false"
      :show-view-all="false"
    />
  </MemberPage>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import MemberPage from '@/modules/Member/components/MemberPage.vue';
import RecentBookmarksWidget from '@/modules/Member/components/RecentBookmarksWidget.vue';

const { t } = useI18n();
</script>

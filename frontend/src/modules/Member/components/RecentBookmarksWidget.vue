<template>
  <section class="rounded-lg border border-border/60 bg-card shadow-sm p-5 space-y-4">
    <div class="flex items-center justify-between gap-3">
      <h3 class="text-lg font-bold">
        {{ t('member.account.bookmarks', 'Bookmarks') }}
      </h3>
      <router-link
        v-if="showViewAll"
        :to="{ name: 'member.bookmarks' }"
        class="text-sm font-semibold text-primary"
      >
        {{ t('member.portal.widgets.viewAll', 'View all') }}
      </router-link>
    </div>
    <p
      v-if="loading"
      class="text-sm text-muted-foreground"
    >
      {{ t('member.account.loading', 'Loading…') }}
    </p>
    <ul
      v-else-if="bookmarks.length"
      class="space-y-3"
    >
      <li
        v-for="item in bookmarks"
        :key="item.id"
        class="flex items-center justify-between gap-3"
      >
        <router-link
          :to="item.content?.slug ? `/blog/${item.content.slug}` : '/blog'"
          class="font-medium hover:text-primary"
        >
          {{ item.content?.title || t('member.account.untitled', 'Untitled') }}
        </router-link>
        <Button
          variant="ghost"
          size="sm"
          @click="removeBookmark(item.id)"
        >
          {{ t('member.account.remove', 'Remove') }}
        </Button>
      </li>
    </ul>
    <p
      v-else
      class="text-sm text-muted-foreground"
    >
      {{ t('member.account.noBookmarks', 'No bookmarks yet.') }}
    </p>
  </section>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useMemberBookmarks } from '@/modules/Member/composables/useMemberBookmarks';

const props = withDefaults(defineProps<{
    limit?: number;
    showViewAll?: boolean;
}>(), {
    limit: 5,
    showViewAll: true,
});

const { t } = useI18n();
const { bookmarks, loading, load, removeBookmark } = useMemberBookmarks(props.limit);

onMounted(() => {
    void load();
});
</script>

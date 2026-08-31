<template>
  <ConsoleListCard>
    <template
      v-if="showHeader"
      #toolbar
    >
      <div class="flex w-full items-center justify-between gap-3">
        <h3 class="text-base font-semibold tracking-tight text-foreground">
          {{ t('member.account.bookmarks', 'Bookmarks') }}
        </h3>
        <router-link
          v-if="showViewAll"
          :to="{ name: 'member.bookmarks' }"
          class="text-sm font-semibold text-primary hover:underline underline-offset-4"
        >
          {{ t('member.portal.widgets.viewAll', 'View all') }}
        </router-link>
      </div>
    </template>

    <div class="p-5 sm:p-6 space-y-4">
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
    </div>
  </ConsoleListCard>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useMemberBookmarks } from '@/modules/Member/composables/useMemberBookmarks';
import { ConsoleListCard } from '@/shared/components/shell';

const props = withDefaults(defineProps<{
    limit?: number;
    showHeader?: boolean;
    showViewAll?: boolean;
}>(), {
    limit: 5,
    showHeader: true,
    showViewAll: true,
});

const { t } = useI18n();
const { bookmarks, loading, load, removeBookmark } = useMemberBookmarks(props.limit);

onMounted(() => {
    void load();
});
</script>

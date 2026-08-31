<template>
  <section class="rounded-3xl border border-border/60 bg-card/70 p-6 space-y-4">
    <div class="flex items-center justify-between gap-3">
      <h3 class="text-lg font-bold">
        {{ t('member.nav.comments', 'Comments') }}
      </h3>
      <router-link
        v-if="showViewAll"
        :to="{ name: 'member.comments' }"
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
      v-else-if="comments.length"
      class="space-y-3"
    >
      <li
        v-for="item in comments"
        :key="item.id"
        class="space-y-1"
      >
        <router-link
          v-if="item.content?.slug"
          :to="`/blog/${item.content.slug}`"
          class="font-medium hover:text-primary"
        >
          {{ item.content?.title || t('member.account.untitled', 'Untitled') }}
        </router-link>
        <p class="text-sm text-muted-foreground line-clamp-2">
          {{ item.body }}
        </p>
      </li>
    </ul>
    <p
      v-else
      class="text-sm text-muted-foreground"
    >
      {{ t('member.portal.comments.empty', 'No comments yet.') }}
    </p>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { extractPaginatedRows } from '@/modules/Member/utils/memberApi';

interface CommentRow {
    id: string;
    body: string;
    content?: { title?: string; slug?: string };
}

const props = withDefaults(defineProps<{
    limit?: number;
    showViewAll?: boolean;
}>(), {
    limit: 3,
    showViewAll: true,
});

const { t } = useI18n();
const comments = ref<CommentRow[]>([]);
const loading = ref(true);

const load = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/member/comments', { params: { per_page: props.limit } });
        comments.value = extractPaginatedRows<CommentRow>(response);
    } catch {
        comments.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    void load();
});
</script>

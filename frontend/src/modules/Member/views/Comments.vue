<template>
  <MemberPage
    :title="t('member.nav.comments', 'Comments')"
    :subtitle="t('member.portal.comments.subtitle', 'Comments you posted on this site.')"
  >
    <ConsoleListCard>
      <div
        v-if="loading"
        class="p-6 text-sm text-muted-foreground"
      >
        {{ t('member.account.loading', 'Loading…') }}
      </div>
      <ul
        v-else-if="comments.length"
        class="divide-y divide-border/50"
      >
        <li
          v-for="item in comments"
          :key="item.id"
          class="space-y-2 px-6 py-4"
        >
          <router-link
            v-if="item.content?.slug"
            :to="`/blog/${item.content.slug}`"
            class="font-medium hover:text-primary"
          >
            {{ item.content?.title || t('member.account.untitled', 'Untitled') }}
          </router-link>
          <p class="text-sm text-muted-foreground">
            {{ item.body }}
          </p>
          <p class="text-xs text-muted-foreground capitalize">
            {{ item.status }}
          </p>
        </li>
      </ul>
      <EmptyState
        v-else
        :title="t('member.portal.comments.empty', 'No comments yet.')"
        compact
      />
    </ConsoleListCard>
  </MemberPage>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import MemberPage from '@/modules/Member/components/MemberPage.vue';
import { extractPaginatedRows } from '@/modules/Member/utils/memberApi';
import { EmptyState } from '@/shared/components/feedback';
import { ConsoleListCard } from '@/shared/components/shell';

interface CommentRow {
    id: string;
    body: string;
    status: string;
    content?: { title?: string; slug?: string };
}

const { t } = useI18n();
const comments = ref<CommentRow[]>([]);
const loading = ref(true);

const load = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/member/comments', { params: { per_page: 50 } });
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

<template>
  <div class="space-y-6">
    <div class="space-y-1">
      <h2 class="text-xl font-bold">
        {{ t('member.nav.comments', 'Comments') }}
      </h2>
      <p class="text-sm text-muted-foreground">
        {{ t('member.portal.comments.subtitle', 'Comments you posted on this site.') }}
      </p>
    </div>

    <section class="rounded-2xl border border-border/60 bg-background/60 p-5 sm:p-6 space-y-4">
      <p
        v-if="loading"
        class="text-sm text-muted-foreground"
      >
        {{ t('member.account.loading', 'Loading…') }}
      </p>
      <ul
        v-else-if="comments.length"
        class="space-y-4"
      >
        <li
          v-for="item in comments"
          :key="item.id"
          class="space-y-2 border-b border-border/40 pb-4 last:border-0 last:pb-0"
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
      <p
        v-else
        class="text-sm text-muted-foreground"
      >
        {{ t('member.portal.comments.empty', 'No comments yet.') }}
      </p>
    </section>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { extractPaginatedRows } from '@/modules/Member/utils/memberApi';

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

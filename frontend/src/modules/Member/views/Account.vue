<template>
  <div class="zenith-theme flex-1 flex flex-col py-16">
    <div class="max-w-2xl mx-auto w-full px-4 space-y-8">
      <div class="flex items-start justify-between gap-4">
        <div class="space-y-1">
          <h1 class="text-3xl font-extrabold font-heading">
            {{ t('member.account.title', 'Your account') }}
          </h1>
          <p class="text-sm text-muted-foreground">
            {{ memberStore.member?.email }}
          </p>
        </div>
        <Button
          variant="outline"
          size="sm"
          @click="logout"
        >
          {{ t('member.account.logout', 'Sign out') }}
        </Button>
      </div>

      <section class="rounded-3xl border border-border/60 bg-card/70 p-6 space-y-4">
        <h2 class="text-lg font-bold">
          {{ t('member.account.bookmarks', 'Bookmarks') }}
        </h2>
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
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '@/engine/api/client';
import { Button } from '@/modules/Layout/views/themes/zenith/ui';
import { useMemberStore } from '@/modules/Member/stores/member';

interface BookmarkRow {
    id: string;
    content?: { title?: string; slug?: string };
}

const { t } = useI18n();
const router = useRouter();
const memberStore = useMemberStore();

const bookmarks = ref<BookmarkRow[]>([]);
const loading = ref(true);

const load = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/member/bookmarks');
        const payload = response.data as { data?: BookmarkRow[] } | BookmarkRow[];
        bookmarks.value = Array.isArray(payload) ? payload : (payload.data ?? []);
    } catch {
        bookmarks.value = [];
    } finally {
        loading.value = false;
    }
};

const removeBookmark = async (id: string): Promise<void> => {
    await api.delete(`/member/bookmarks/${id}`);
    bookmarks.value = bookmarks.value.filter((row) => row.id !== id);
};

const logout = async (): Promise<void> => {
    await memberStore.logout();
    await router.replace('/member/login');
};

onMounted(() => {
    void load();
});
</script>

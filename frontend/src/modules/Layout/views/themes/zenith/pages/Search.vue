<template>
  <div class="zenith-theme flex-1 flex flex-col py-12 sm:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full">
      <div class="text-center space-y-4">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-foreground font-heading">
          {{ t('theme.zenith.pages.search.title', 'Search') }}
        </h1>
        <p class="text-lg text-muted-foreground">
          {{ t('theme.zenith.pages.search.subtitle', 'Find articles and pages on this site.') }}
        </p>
      </div>

      <form
        class="flex flex-col sm:flex-row gap-3"
        @submit.prevent="runSearch"
      >
        <input
          v-model="query"
          type="search"
          required
          minlength="2"
          class="flex-1 px-4 py-2.5 rounded-xl border border-border/80 bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
          :placeholder="t('theme.zenith.pages.search.placeholder', 'Search posts…')"
        >
        <Button
          type="submit"
          variant="primary"
          :disabled="loading"
        >
          {{ t('theme.zenith.pages.search.submit', 'Search') }}
        </Button>
      </form>

      <p
        v-if="error"
        class="text-sm text-destructive text-center"
      >
        {{ error }}
      </p>

      <div
        v-else-if="loading"
        class="min-h-[160px] flex items-center justify-center"
      >
        <div class="w-8 h-8 rounded-full border-2 border-primary border-t-transparent animate-spin" />
      </div>

      <ul
        v-else-if="results.length > 0"
        class="space-y-4"
      >
        <li
          v-for="item in results"
          :key="item.id"
          class="rounded-2xl border border-border/60 bg-card/60 p-5 space-y-2"
        >
          <p class="text-xs uppercase tracking-wider text-muted-foreground">
            {{ item.type }}
          </p>
          <component
            :is="isExternal(item.url) ? 'a' : 'router-link'"
            :to="isExternal(item.url) ? undefined : publicPath(item.url)"
            :href="isExternal(item.url) ? item.url ?? undefined : undefined"
            class="text-lg font-semibold text-foreground hover:text-primary"
          >
            {{ item.title }}
          </component>
          <p
            v-if="item.excerpt"
            class="text-sm text-muted-foreground"
          >
            {{ item.excerpt }}
          </p>
        </li>
      </ul>

      <p
        v-else-if="searched"
        class="text-sm text-muted-foreground text-center"
      >
        {{ t('theme.zenith.pages.search.empty', 'No results for that query.') }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { Button } from '@/modules/Layout/views/themes/zenith/ui';

interface SearchHit {
    id: string;
    type: string;
    title: string;
    excerpt?: string | null;
    url?: string | null;
}

const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const query = ref('');
const loading = ref(false);
const searched = ref(false);
const error = ref('');
const results = ref<SearchHit[]>([]);

const isExternal = (url?: string | null): boolean => Boolean(url && /^https?:\/\//i.test(url));

const publicPath = (url?: string | null): string => {
    if (!url) {
        return '/blog';
    }
    const content = url.match(/\/(?:content|blog)\/([^/?#]+)/i);
    if (content?.[1]) {
        return `/blog/${content[1]}`;
    }
    return url.startsWith('/') ? url : `/${url}`;
};

const runSearch = async (): Promise<void> => {
    const q = query.value.trim();
    if (q.length < 2) {
        return;
    }
    loading.value = true;
    error.value = '';
    searched.value = true;
    await router.replace({ path: '/search', query: { q } });
    try {
        const res = await api.get('/public/search', { params: { q } });
        const payload = res.data as { results?: SearchHit[] } | SearchHit[];
        if (Array.isArray(payload)) {
            results.value = payload;
        } else {
            results.value = Array.isArray(payload.results) ? payload.results : [];
        }
    } catch {
        results.value = [];
        error.value = t(
            'theme.zenith.pages.search.unavailable',
            'Search is unavailable. Activate the search pack in the registry.',
        );
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    const q = typeof route.query.q === 'string' ? route.query.q : '';
    if (q.trim().length >= 2) {
        query.value = q;
        void runSearch();
    }
});
</script>

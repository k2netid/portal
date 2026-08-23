<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('search.title')"
      :subtitle="t('search.initial')"
    />
    <p
      v-if="query"
      class="text-sm text-muted-foreground mb-4"
    >
      {{ t('search.resultsFor') }} <span class="font-medium">{{ query }}</span>
    </p>

    <ConsoleListCard>
      <template #toolbar>
      <div class="flex flex-col gap-4 w-full">
      <div class="relative w-full sm:max-w-md">
        <input
          v-model="searchQuery"
          type="text"
          :aria-label="t('search.placeholder')"
          :placeholder="t('search.placeholder')"
          class="w-full pl-10 pr-4 py-3 border border-input rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          @keyup.enter="performSearch"
        >
        <Search class="absolute left-3 top-3.5 h-5 w-5 text-muted-foreground" />
      </div>
      <div class="flex flex-wrap items-center gap-2">
      <span class="text-sm text-foreground">{{ t('search.filterBy') }}</span>
      <button
        v-for="type in availableTypes"
        :key="type"
        :class="[
          'px-3 py-1 text-sm rounded-md transition-colors',
          typeFilters.includes(type)
            ? 'bg-primary text-primary-foreground'
            : 'bg-secondary text-foreground hover:bg-accent'
        ]"
        @click="toggleTypeFilter(type)"
      >
        {{ t(`search.types.${type}`) }}
      </button>
    </div>
      </div>
      </template>

    <div
      v-if="loading"
      class="text-center py-12"
    >
      <p class="text-muted-foreground">
        {{ t('search.searching') }}
      </p>
    </div>

    <div
      v-else-if="results.length === 0 && query"
      class="text-center py-12 space-y-6"
    >
      <div class="space-y-2">
        <Search class="mx-auto h-12 w-12 text-muted-foreground" />
        <p class="text-muted-foreground">
          {{ t('search.empty') }}
        </p>
      </div>

      <div
        v-if="suggestions.length > 0"
        class="inline-block bg-muted/40 border border-muted px-6 py-4 rounded-xl text-left max-w-md mx-auto"
      >
        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground block mb-2">
          Did you mean?
        </span>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="sug in suggestions"
            :key="sug.text"
            class="px-3 py-1.5 bg-background hover:bg-primary/10 hover:text-primary border border-border hover:border-primary/20 rounded-lg text-sm font-medium transition-all"
            @click="applySuggestion(sug.text)"
          >
            {{ sug.text }}
          </button>
        </div>
      </div>
    </div>

    <div
      v-else-if="results.length > 0"
      class="space-y-4"
    >
      <!-- Group results by type -->
      <div
        v-for="(items, type) in groupedResults"
        :key="type"
        class="bg-card border border-border rounded-lg p-6"
      >
        <h2 class="text-lg font-semibold text-foreground mb-4 capitalize">
          {{ t(`search.types.${type}`) }}
        </h2>
        <div class="space-y-3">
          <div
            v-for="result in items"
            :key="`${result.type}-${result.id}`"
            class="p-4 border border-border rounded-lg hover:bg-muted cursor-pointer transition-colors"
            @click="handleResultClick(result)"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="text-sm font-medium text-foreground">
                  {{ result.title }}
                </h3>
                <p
                  v-if="result.description"
                  class="text-sm text-muted-foreground mt-1"
                >
                  {{ result.description }}
                </p>
                <div class="mt-2 flex items-center space-x-4 text-xs text-muted-foreground">
                  <span v-if="result.created_at">{{ formatDate(result.created_at) }}</span>
                  <span v-if="result.author">{{ result.author }}</span>
                </div>
              </div>
              <ChevronRight class="w-5 h-5 text-muted-foreground" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-else
      class="text-center py-12"
    >
      <Search class="mx-auto h-12 w-12 text-muted-foreground" />
      <p class="mt-4 text-muted-foreground">
        {{ t('search.initial') }}
      </p>
    </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import {PageHeader, ConsoleListCard} from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import {
  ChevronRight,
  Search,
} from 'lucide-vue-next';
import { SearchService } from '@/modules/Search/services/searchService';
import { ensureArray } from '@/shared/utils/responseParser';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();

interface SearchResult {
    id: string;
    type: string;
    title: string;
    description?: string;
    created_at?: string;
    author?: string;
    url?: string;
    searchable_id?: string;
}

const query = ref(route.query.q || '');
const searchQuery = ref(route.query.q || '');
const results = ref<SearchResult[]>([]);
const suggestions = ref<any[]>([]);
const loading = ref(false);
const typeFilters = ref<string[]>([]);

const availableTypes = ['content', 'category', 'user', 'media', 'page', 'tag'];

const groupedResults = computed(() => {
    const grouped: Record<string, SearchResult[]> = {};
    
    let filteredResults = results.value;
    if (typeFilters.value.length > 0) {
        filteredResults = filteredResults.filter(r => typeFilters.value.includes(r.type));
    }
    
    filteredResults.forEach(result => {
        const type = result.type;
        if (!grouped[type]) {
            grouped[type] = [];
        }
        grouped[type].push(result);
    });
    
    return grouped;
});

const performSearch = async () => {
    if (!searchQuery.value || searchQuery.value.length < 2) {
        return;
    }
    
    query.value = searchQuery.value;
    loading.value = true;
    
    try {
        const response = await SearchService.search({ q: query.value });
        const responseData = response.data as any;
        results.value = ensureArray(responseData?.results || responseData || []);
        suggestions.value = ensureArray(responseData?.suggestions || []);
        
        // Update URL
        router.replace({ query: { q: query.value } });
    } catch (error: unknown) {
        logger.error('Failed to search:', error);
        results.value = [];
        suggestions.value = [];
    } finally {
        loading.value = false;
    }
};

function applySuggestion(text: string) {
    searchQuery.value = text;
    performSearch();
}

const toggleTypeFilter = (type: string) => {
    const index = typeFilters.value.indexOf(type);
    if (index > -1) {
        typeFilters.value.splice(index, 1);
    } else {
        typeFilters.value.push(type);
    }
};

function handleResultClick(result: SearchResult) {
    // Navigate based on result type
    const resourceId = result.searchable_id || result.id;
    
    if ((result.type === 'post' || result.type === 'page' || result.type === 'content') && resourceId) {
        router.push({ name: 'contents.edit', params: { id: resourceId } });
    } else if (result.type === 'category' && resourceId) {
        router.push({ name: 'categories.index' });
    } else if (result.type === 'user' && resourceId) {
        router.push({ name: 'users.edit', params: { id: resourceId } });
    } else if (result.type === 'media' && resourceId) {
        router.push({ name: 'media', query: { id: resourceId } });
    } else if (result.url) {
        router.push(result.url);
    }
}

function formatDate(date: string) {
    if (!date) return '';
    return new Date(date).toLocaleDateString();
}

onMounted(() => {
    if (query.value) {
        performSearch();
    }
});
</script>


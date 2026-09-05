<template>
  <div class="universal-widget search-widget rounded-2xl border border-border/70 bg-card p-5 shadow-sm space-y-3">
    <div class="flex items-center gap-2">
      <span class="w-1 h-4 bg-primary rounded-full" />
      <h3 class="text-sm font-bold text-foreground font-heading tracking-tight">
        {{ widgetTitle }}
      </h3>
    </div>

    <form
      class="relative"
      @submit.prevent="handleSearch"
    >
      <div class="relative group">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground group-focus-within:text-primary transition-colors pointer-events-none" />
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="inputPlaceholder"
          class="w-full bg-muted/60 hover:bg-muted/80 focus:bg-background border border-transparent focus:border-primary/30 rounded-xl py-2.5 pl-9 pr-8 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"
          @input="onSearchInput"
          @focus="showSuggestions = suggestions.length > 0"
          @keydown.down.prevent="navigateSuggestion(1)"
          @keydown.up.prevent="navigateSuggestion(-1)"
          @keydown.esc="showSuggestions = false"
        >
        <button
          v-if="searchQuery"
          type="button"
          class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-muted-foreground hover:text-foreground rounded-full hover:bg-muted transition-colors"
          @click="clearSearch"
        >
          <X class="w-3.5 h-3.5" />
        </button>
      </div>

      <!-- Realtime Suggestions Dropdown -->
      <div
        v-if="showSuggestions && suggestions.length > 0"
        class="absolute top-full left-0 right-0 mt-2 bg-card border border-border shadow-xl rounded-xl overflow-hidden z-50 animate-in fade-in slide-in-from-top-2 duration-200"
      >
        <div class="p-1.5 space-y-0.5">
          <button
            v-for="(item, idx) in suggestions"
            :key="item.id || idx"
            type="button"
            class="w-full flex items-center justify-between p-2 rounded-lg text-left text-xs text-foreground transition-colors hover:bg-primary/10"
            :class="{ 'bg-primary/10 text-primary font-medium': selectedSuggestionIndex === idx }"
            @click="selectSuggestion(item)"
          >
            <span class="truncate pr-2">{{ item.title }}</span>
            <span class="shrink-0 text-[10px] px-1.5 py-0.5 rounded bg-muted text-muted-foreground font-mono">
              {{ item.category || item.type }}
            </span>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { Search, X } from 'lucide-vue-next';

interface SuggestionItem {
  id: string;
  title: string;
  type: string;
  url: string | null;
  category?: string;
}

const props = defineProps<{
  widget?: Record<string, any>;
  title?: string;
  placeholder?: string;
}>();

const emit = defineEmits<{
  (e: 'search', query: string): void;
}>();

const { t, te } = useI18n();
const router = useRouter();
const route = useRoute();

const widgetTitle = computed(() => {
  if (props.title) return props.title;
  if (props.widget?.title) return props.widget.title;
  return te('layout.widgets.universal.search.title') 
    ? t('layout.widgets.universal.search.title') 
    : 'Cari Warta & Artikel';
});

const inputPlaceholder = computed(() => {
  if (props.placeholder) return props.placeholder;
  if (props.widget?.settings?.placeholder) return props.widget.settings.placeholder;
  return te('layout.widgets.universal.search.placeholder')
    ? t('layout.widgets.universal.search.placeholder')
    : 'Ketik kata kunci pencarian...';
});

const searchQuery = ref((route.query.q as string) || '');
const suggestions = ref<SuggestionItem[]>([]);
const showSuggestions = ref(false);
const selectedSuggestionIndex = ref(-1);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const onSearchInput = () => {
  if (debounceTimer) clearTimeout(debounceTimer);

  debounceTimer = setTimeout(async () => {
    const q = searchQuery.value.trim();
    if (!q || q.length < 2) {
      suggestions.value = [];
      showSuggestions.value = false;
      return;
    }

    try {
      const res = await api.get('/public/search/suggestions', {
        params: { q, limit: 5 }
      });
      const data = res.data as any;
      const rawList = Array.isArray(data?.suggestions) 
        ? data.suggestions 
        : (Array.isArray(data?.data) ? data.data : []);

      suggestions.value = rawList.map((item: any, idx: number) => ({
        id: item.id || `sugg-${idx}`,
        title: item.text || item.title || item.name || '',
        type: item.type || 'post',
        url: item.url || null,
        category: item.category || item.type || 'Warta'
      }));
      showSuggestions.value = suggestions.value.length > 0;
      selectedSuggestionIndex.value = -1;
    } catch {
      suggestions.value = [];
      showSuggestions.value = false;
    }
  }, 250);
};

const handleSearch = () => {
  const q = searchQuery.value.trim();
  showSuggestions.value = false;
  emit('search', q);

  if (route.name === 'blog' || route.path.startsWith('/blog')) {
    router.push({
      path: '/blog',
      query: { ...route.query, q: q || undefined }
    });
  } else {
    router.push({
      path: '/search',
      query: { q: q || undefined }
    });
  }
};

const selectSuggestion = (item: SuggestionItem) => {
  showSuggestions.value = false;
  if (item.url && item.url.startsWith('/')) {
    router.push(item.url);
  } else if (item.title) {
    searchQuery.value = item.title;
    handleSearch();
  }
};

const navigateSuggestion = (direction: number) => {
  if (!suggestions.value.length) return;
  const newIndex = selectedSuggestionIndex.value + direction;
  if (newIndex >= 0 && newIndex < suggestions.value.length) {
    selectedSuggestionIndex.value = newIndex;
    searchQuery.value = suggestions.value[newIndex]?.title || '';
  }
};

const clearSearch = () => {
  searchQuery.value = '';
  suggestions.value = [];
  showSuggestions.value = false;
  if (route.query.q) {
    router.push({
      path: route.path,
      query: { ...route.query, q: undefined }
    });
  }
};
</script>

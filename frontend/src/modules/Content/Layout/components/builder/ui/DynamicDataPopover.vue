<template>
  <div class="dynamic-data-popover flex flex-col h-full bg-body overflow-hidden">
    <!-- Close button if showClose is true (for mobile or specific modals) -->
    <div v-if="showClose" class="flex items-center justify-between px-4 py-3 border-b border-border">
      <h3 class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground">{{ $t('builder.dynamic.title', 'Dynamic Data') }}</h3>
      <button @click="$emit('close')" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-900 rounded-full transition-colors text-muted-foreground">
        <X :size="14" />
      </button>
    </div>

    <!-- Search Section -->
    <div class="p-3 border-b border-border/50">
      <div class="relative group">
        <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground group-focus-within:text-primary transition-colors" />
        <Input 
          v-model="searchQuery" 
          :placeholder="$t('builder.dynamic.search', 'Search dynamic data...')"
          class="pl-8 h-8 bg-slate-50 dark:bg-slate-900/50 border-none focus-visible:ring-1 focus-visible:ring-primary rounded-lg text-[13px]"
          autofocus
        />
      </div>
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto no-scrollbar custom-scrollbar pt-1 pb-4">
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center p-8 gap-3">
        <Loader2 class="w-6 h-6 text-primary animate-spin" />
        <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest animate-pulse">{{ $t('builder.dynamic.loading', 'Scanning...') }}</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="flex flex-col items-center justify-center p-8 text-center gap-3">
        <AlertCircle class="w-8 h-8 text-destructive opacity-50" />
        <p class="text-[10px] font-bold text-destructive/80 uppercase tracking-widest">{{ error }}</p>
        <Button variant="outline" size="sm" class="h-7 rounded-full text-[10px] px-3" @click="fetchDynamicSources">
          {{ $t('builder.common.retry', 'Retry') }}
        </Button>
      </div>

      <!-- Content -->
      <template v-else>
        <div v-for="(group, groupName) in filteredGroups" :key="groupName" class="mb-1 animate-in fade-in slide-in-from-bottom-1 duration-300">
          <div class="px-3 py-1">
            <h4 class="text-[8px] font-black uppercase tracking-[0.2em] text-primary/60 dark:text-primary/40">{{ group.label }}</h4>
          </div>
          
          <div class="flex flex-col">
            <button 
              v-for="item in group.items" 
              :key="item.id"
              class="group flex items-center justify-between px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-900 transition-[background-color,transform] duration-200 active:scale-[0.98] text-left"
              @click="selectItem(item)"
            >
              <div class="flex flex-col items-start gap-0 min-w-0 flex-1 mr-3">
                <span class="text-[12px] font-bold text-slate-700 dark:text-slate-200 group-hover:text-primary transition-colors tracking-tight leading-tight truncate w-full">{{ item.label }}</span>
                <span v-if="item.description" class="text-[8px] text-muted-foreground line-clamp-1 opacity-50 group-hover:opacity-100 transition-opacity truncate w-full">{{ item.description }}</span>
              </div>
              
              <Badge variant="outline" class="shrink-0 bg-slate-100/30 dark:bg-slate-800/20 border-slate-200/50 dark:border-slate-800/50 text-[8px] font-mono font-medium px-1 py-0 text-slate-500 dark:text-slate-400 group-hover:bg-primary/10 group-hover:text-primary group-hover:border-primary/20 transition-colors duration-200">
                {{ item.tag }}
              </Badge>
            </button>
          </div>
        </div>
        
        <!-- Empty State -->
        <div v-if="Object.keys(filteredGroups).length === 0" class="flex flex-col items-center justify-center p-10 text-center opacity-40 animate-in fade-in duration-300">
          <SearchX class="w-10 h-10 mb-3 text-muted-foreground" />
          <p class="text-[9px] font-black uppercase tracking-widest">{{ $t('builder.dynamic.noResults', 'None found') }}</p>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
  import { ref, computed, onMounted, inject } from 'vue';
  import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import SearchX from 'lucide-vue-next/dist/esm/icons/search-x.js';
  import { Input, Badge, Button } from '@/components/ui';
  import api from '@/services/api';
  import type { BuilderInstance } from '@/types/builder';

  interface Props {
    source?: string;
    contentId?: number | string | null;
    showClose?: boolean;
  }

  const props = withDefaults(defineProps<Props>(), {
    source: 'all',
    contentId: null,
    showClose: false
  });

  interface DynamicItem {
    id: string;
    label: string;
    tag: string;
    description: string;
  }

  interface DynamicGroup {
    label: string;
    items: DynamicItem[];
  }

  const emit = defineEmits<{
    (e: 'select', item: DynamicItem): void;
    (e: 'close'): void;
  }>();
  
  const builder = inject<BuilderInstance | null>('builder', null);

  const searchQuery = ref('');
  const dataGroups = ref<Record<string, DynamicGroup>>({});
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Fetch dynamic sources from backend
  const fetchDynamicSources = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const params = {
        context: props.source,
        content_id: props.contentId || builder?.content.value.id
      };
      
      const response = await api.get('/admin/ja/builder/dynamic-sources', { params });
      dataGroups.value = response.data?.data || response.data || {};
    } catch (err) {
      logger.error('Failed to fetch dynamic sources:', err);
      error.value = 'Failed to load dynamic data sources';
      // Fallback to default sources
      dataGroups.value = getDefaultSources();
    } finally {
      loading.value = false;
    }
  };

  // Default sources as fallback
  const getDefaultSources = (): Record<string, DynamicGroup> => ({
    page: {
      label: 'Page / Post',
      items: [
        { id: 'post_title', label: 'Post Title', tag: '{{post_title}}', description: 'Display the current post or page title' },
        { id: 'post_excerpt', label: 'Post Excerpt', tag: '{{post_excerpt}}', description: 'Display a short summary of the content' },
        { id: 'post_date', label: 'Post Date', tag: '{{post_date}}', description: 'Publication date of this content' },
        { id: 'post_author', label: 'Author Name', tag: '{{post_author}}', description: 'Display the name of the content creator' },
        { id: 'post_featured_image', label: 'Featured Image', tag: '{{post_featured_image}}', description: 'Primary image associated with this post' },
        { id: 'post_url', label: 'Post URL', tag: '{{post_url}}', description: 'Permalink to the current content' }
      ]
    },
    item: {
        label: 'Current Loop Item',
        items: [
            { id: 'loop_title', label: 'Item Title', tag: '{{loop_title}}', description: 'Title of the current item in a grid or list' },
            { id: 'loop_excerpt', label: 'Item Excerpt', tag: '{{loop_excerpt}}', description: 'Short description of the loop item' },
            { id: 'loop_date', label: 'Item Date', tag: '{{loop_date}}', description: 'Date associated with the current item' }
        ]
    },
    site: {
      label: 'Site Settings',
      items: [
        { id: 'site_title', label: 'Site Title', tag: '{{site_title}}', description: 'Global website title' },
        { id: 'site_tagline', label: 'Site Tagline', tag: '{{site_tagline}}', description: 'Website slogan or description' },
        { id: 'current_date', label: 'Current Date', tag: '{{current_date}}', description: 'Today\'s dynamic date' }
      ]
    }
  });

  const filteredGroups = computed(() => {
    const query = searchQuery.value.toLowerCase();
    const result: Record<string, DynamicGroup> = {};

    Object.entries(dataGroups.value).forEach(([key, group]) => {
      const items = group.items.filter(item => 
        item.label.toLowerCase().includes(query) || 
        item.tag.toLowerCase().includes(query) ||
        (item.description && item.description.toLowerCase().includes(query))
      );

      if (items.length > 0) {
        result[key] = {
          ...group,
          items
        };
      }
    });

    return result;
  });

  const selectItem = (item: DynamicItem) => {
    emit('select', item);
  };

  onMounted(() => {
    fetchDynamicSources();
  });
</script>

<style scoped>
.dynamic-data-popover {
  width: 280px;
  max-height: 320px; /* Limited height to prevent overflow as per user request */
}

/* Custom Scrollbar for a pro feel */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: hsl(var(--border));
  border-radius: 20px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: hsl(var(--primary) / 0.3);
}

.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>

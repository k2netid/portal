<template>
  <BaseModal
    :is-open="true"
    :title="$t('builder.fields.icon.pickIcon')"
    :width="900"
    draggable
    placement="center"
    @close="$emit('close')"
  >
    <div class="modal-search-wrapper">
      <div class="search-row">
        <BaseInput 
          v-model="searchQuery" 
          :placeholder="$t('builder.fields.icon.searchLarge')"
          autofocus
        >
          <template #prefix>
            <Search :size="18" />
          </template>
        </BaseInput>
        
        <BaseButton 
          variant="ghost" 
          @click="resetFilters"
          v-if="searchQuery || activeCategory !== 'all'"
        >
          {{ $t('common.actions.clear') }}
        </BaseButton>
      </div>
      
      <div class="modal-filters">
        <button 
          class="category-btn"
          :class="{ 'active': activeCategory === 'all' }"
          @click="activeCategory = 'all'"
        >
          All Icons
        </button>
        <button 
          v-for="cat in categories" 
          :key="cat.id"
          class="category-btn"
          :class="{ 'active': activeCategory === cat.id }"
          @click="activeCategory = cat.id"
        >
          {{ cat.label }}
        </button>
      </div>
    </div>

    <div class="modal-icon-grid">
      <button 
        v-for="iconName in displayedIcons" 
        :key="iconName"
        class="icon-card"
        :class="{ 'icon-card--active': value === iconName }"
        @click="selectIcon(iconName)"
        :title="iconName"
      >
        <div class="icon-preview">
          <LucideIcon :name="iconName" :size="32" />
        </div>
        <span class="icon-name">{{ iconName }}</span>
      </button>
    </div>

    <div v-if="displayedIcons.length === 0" class="no-results">
       {{ $t('builder.fields.icon.noResults') }}
    </div>
    
    <div v-if="hasMore" class="load-more">
       <BaseButton variant="secondary" size="sm" @click="pageSize += 100">
         Load More...
       </BaseButton>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import { BaseModal, BaseInput, BaseButton } from '@/components/builder/ui';
import { LucideIcon } from '@/components/ui';
import { allIcons, categories } from '@/shared/assets/icons';

interface Props {
  value?: string;
  onSelect: (icon: string) => void;
}

const props = withDefaults(defineProps<Props>(), {
  value: ''
});

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const searchQuery = ref('');
const activeCategory = ref('all');
const pageSize = ref(150);

const filteredIcons = computed(() => {
  let list = allIcons;
  
  if (activeCategory.value !== 'all') {
    const category = categories.find(c => c.id === activeCategory.value);
    if (category) {
      list = category.icons;
    }
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    list = list.filter(name => 
      name.toLowerCase().includes(query)
    );
  }

  return list;
});

const displayedIcons = computed(() => {
  return filteredIcons.value.slice(0, pageSize.value);
});

const hasMore = computed(() => {
  return filteredIcons.value.length > pageSize.value;
});

const selectIcon = (iconName: string) => {
  props.onSelect(iconName);
  emit('close');
};

const resetFilters = () => {
  searchQuery.value = '';
  activeCategory.value = 'all';
};

// Reset page size when filters change
watch([searchQuery, activeCategory], () => {
  pageSize.value = 150;
});
</script>

<style scoped>
.modal-search-wrapper {
  padding: 0 0 20px 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
  position: sticky;
  top: 0;
  background: var(--builder-bg-primary);
  z-index: 10;
}

.search-row {
  display: flex;
  gap: 8px;
}

.modal-filters {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.category-btn {
  padding: 6px 12px;
  border-radius: 4px;
  border: 1px solid var(--builder-border);
  background: var(--builder-bg-primary);
  color: var(--builder-text-secondary);
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}

.category-btn:hover {
  border-color: var(--builder-accent);
  color: var(--builder-accent);
}

.category-btn.active {
  background: var(--builder-accent);
  color: white;
  border-color: var(--builder-accent);
}

.modal-icon-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 12px;
}

.icon-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 16px 8px;
  background: transparent;
  border: 1px solid var(--builder-border);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.icon-card:hover {
  border-color: var(--builder-accent);
  background: var(--builder-bg-secondary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.icon-card--active {
  border-color: var(--builder-accent);
  background: var(--builder-accent-soft);
  color: var(--builder-accent);
  box-shadow: inset 0 0 0 1px var(--builder-accent);
}

.icon-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  color: inherit;
}

.icon-name {
  font-size: 11px;
  color: var(--builder-text-secondary);
  text-align: center;
  word-break: break-all;
  max-width: 100%;
}

.no-results {
  text-align: center;
  color: var(--builder-text-muted);
  padding: 60px 0;
}

.load-more {
  display: flex;
  justify-content: center;
  margin-top: 24px;
  padding-bottom: 24px;
}
</style>

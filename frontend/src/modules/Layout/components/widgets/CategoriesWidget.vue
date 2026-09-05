<template>
  <div class="universal-widget categories-widget rounded-2xl border border-border/70 bg-card p-5 shadow-sm space-y-4">
    <div class="flex items-center justify-between border-b border-border/60 pb-3">
      <div class="flex items-center gap-2">
        <span class="w-1 h-4 bg-primary rounded-full" />
        <h3 class="text-sm font-bold text-foreground font-heading tracking-tight">
          {{ widgetTitle }}
        </h3>
      </div>
      <Folder class="w-4 h-4 text-muted-foreground" />
    </div>

    <!-- Loading skeleton -->
    <div
      v-if="loading && categoryList.length === 0"
      class="space-y-2 animate-pulse py-2"
    >
      <div
        v-for="i in 4"
        :key="i"
        class="h-8 bg-muted rounded-lg w-full"
      />
    </div>

    <!-- Empty state -->
    <div
      v-else-if="categoryList.length === 0"
      class="text-xs text-muted-foreground py-3 text-center"
    >
      {{ emptyText }}
    </div>

    <!-- Categories List -->
    <ul
      v-else
      class="space-y-1.5 text-sm"
    >
      <!-- All Categories Option -->
      <li v-if="showAllOption">
        <button
          type="button"
          class="flex items-center justify-between w-full py-2 px-3 rounded-xl transition-all duration-200 text-left group"
          :class="!currentActiveCategory ? 'bg-primary/10 text-primary font-bold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
          @click="selectCategory('')"
        >
          <div class="flex items-center gap-2.5">
            <span
              class="w-1.5 h-1.5 rounded-full transition-colors"
              :class="!currentActiveCategory ? 'bg-primary' : 'bg-muted-foreground/40 group-hover:bg-primary/60'"
            />
            <span class="text-xs sm:text-sm">{{ allCategoriesLabel }}</span>
          </div>
          <span
            v-if="showCount"
            class="text-[11px] font-mono px-2 py-0.5 rounded-full"
            :class="!currentActiveCategory ? 'bg-primary/20 text-primary font-bold' : 'bg-muted text-muted-foreground'"
          >
            {{ totalContentCount }}
          </span>
        </button>
      </li>

      <!-- Category Items with Optional Nested Children -->
      <li
        v-for="cat in categoryList"
        :key="cat.slug || cat.id"
        class="space-y-1"
      >
        <div class="flex items-center gap-1">
          <button
            type="button"
            class="flex-1 flex items-center justify-between py-2 px-3 rounded-xl transition-all duration-200 text-left group"
            :class="currentActiveCategory === cat.slug ? 'bg-primary/10 text-primary font-bold shadow-xs' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
            @click="selectCategory(cat.slug)"
          >
            <div class="flex items-center gap-2.5 truncate pr-1">
              <span
                class="w-1.5 h-1.5 rounded-full shrink-0 transition-colors"
                :class="currentActiveCategory === cat.slug ? 'bg-primary' : 'bg-muted-foreground/40 group-hover:bg-primary/60'"
              />
              <span class="truncate text-xs sm:text-sm">{{ cat.name }}</span>
            </div>
            <span
              v-if="showCount && (cat.contents_count !== undefined || cat.count !== undefined)"
              class="shrink-0 text-[11px] font-mono px-2 py-0.5 rounded-full"
              :class="currentActiveCategory === cat.slug ? 'bg-primary/20 text-primary font-bold' : 'bg-muted text-muted-foreground'"
            >
              {{ cat.contents_count ?? cat.count ?? 0 }}
            </span>
          </button>

          <!-- Toggle Subcategories Chevron if has children -->
          <button
            v-if="cat.children && cat.children.length > 0"
            type="button"
            class="p-1.5 rounded-lg text-muted-foreground hover:text-primary hover:bg-muted/60 transition-colors"
            @click.stop="toggleExpand(cat.slug)"
          >
            <ChevronDown
              class="w-3.5 h-3.5 transition-transform duration-200"
              :class="{ 'rotate-180': expanded.has(cat.slug) }"
            />
          </button>
        </div>

        <!-- Sub-categories (Children) -->
        <ul
          v-if="cat.children && cat.children.length > 0 && expanded.has(cat.slug)"
          class="pl-4 space-y-1 border-l-2 border-border/50 ml-3.5 my-1"
        >
          <li
            v-for="sub in cat.children"
            :key="sub.slug || sub.id"
          >
            <button
              type="button"
              class="flex items-center justify-between w-full py-1.5 px-2.5 rounded-lg text-xs transition-all duration-200 text-left group"
              :class="currentActiveCategory === sub.slug ? 'bg-primary/10 text-primary font-bold' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
              @click="selectCategory(sub.slug)"
            >
              <span class="truncate">{{ sub.name }}</span>
              <span
                v-if="showCount && (sub.contents_count !== undefined || sub.count !== undefined)"
                class="text-[10px] font-mono opacity-80"
              >
                {{ sub.contents_count ?? sub.count ?? 0 }}
              </span>
            </button>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { Folder, ChevronDown } from 'lucide-vue-next';

interface CategoryItem {
  id?: string | number;
  name: string;
  slug: string;
  contents_count?: number;
  count?: number;
  children?: CategoryItem[];
}

const props = withDefaults(
  defineProps<{
    widget?: Record<string, any>;
    categories?: CategoryItem[];
    activeCategory?: string;
    title?: string;
    showCount?: boolean;
    showAllOption?: boolean;
  }>(),
  {
    widget: undefined,
    categories: undefined,
    activeCategory: undefined,
    title: undefined,
    showCount: true,
    showAllOption: true,
  }
);

const emit = defineEmits<{
  (e: 'selectCategory', slug: string): void;
}>();

const { t, te } = useI18n();
const router = useRouter();
const route = useRoute();

const widgetTitle = computed(() => {
  if (props.title) return props.title;
  if (props.widget?.title) return props.widget.title;
  return te('layout.widgets.universal.categories.title')
    ? t('layout.widgets.universal.categories.title')
    : 'Kategori Berita';
});

const allCategoriesLabel = computed(() => {
  return te('layout.widgets.universal.categories.all')
    ? t('layout.widgets.universal.categories.all')
    : 'Semua Kategori';
});

const emptyText = computed(() => {
  return te('layout.widgets.universal.categories.empty')
    ? t('layout.widgets.universal.categories.empty')
    : 'Belum ada kategori';
});

const currentActiveCategory = computed(() => {
  if (props.activeCategory !== undefined) return props.activeCategory;
  return (route.query.category as string) || '';
});

const categoryList = ref<CategoryItem[]>(props.categories || props.widget?.items || []);
const loading = ref(false);
const expanded = ref<Set<string>>(new Set());

const totalContentCount = computed(() => {
  return categoryList.value.reduce((acc, cat) => {
    const parentCount = cat.contents_count ?? cat.count ?? 0;
    const childrenCount = cat.children?.reduce((cAcc, child) => cAcc + (child.contents_count ?? child.count ?? 0), 0) || 0;
    return acc + parentCount + childrenCount;
  }, 0);
});

const fetchCategories = async () => {
  if (props.categories && props.categories.length > 0) {
    categoryList.value = props.categories;
    return;
  }
  if (props.widget?.items && Array.isArray(props.widget.items) && props.widget.items.length > 0) {
    categoryList.value = props.widget.items;
    return;
  }

  loading.value = true;
  try {
    const res = await api.get('/public/publishing/categories');
    const data = res.data as any;
    const list = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []);
    categoryList.value = list;
  } catch {
    categoryList.value = [];
  } finally {
    loading.value = false;
  }
};

const selectCategory = (slug: string) => {
  emit('selectCategory', slug);
  const nextQuery = { ...route.query };
  if (slug) {
    nextQuery.category = slug;
  } else {
    delete nextQuery.category;
  }

  if (route.name === 'blog' || route.path.startsWith('/blog')) {
    router.push({ path: '/blog', query: nextQuery });
  } else {
    router.push({ path: '/blog', query: slug ? { category: slug } : undefined });
  }
};

const toggleExpand = (slug: string) => {
  if (expanded.value.has(slug)) {
    expanded.value.delete(slug);
  } else {
    expanded.value.add(slug);
  }
};

watch(() => props.categories, (newVal) => {
  if (newVal && newVal.length > 0) {
    categoryList.value = newVal;
  }
}, { immediate: true });

onMounted(() => {
  if (categoryList.value.length === 0) {
    fetchCategories();
  }
});
</script>

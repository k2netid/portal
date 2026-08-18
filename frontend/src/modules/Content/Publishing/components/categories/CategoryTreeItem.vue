<template>
  <div>
    <div class="px-6 py-4 hover:bg-muted flex items-center justify-between group">
      <div class="flex items-center flex-1">
        <div class="flex-shrink-0 mr-4">
          <Button
            v-if="hasChildren"
            variant="ghost"
            size="icon"
            class="h-6 w-6 p-0"
            @click="expanded = !expanded"
          >
            <ChevronRight
              class="w-4 h-4 transition-transform"
              :class="{ 'rotate-90': expanded }"
            />
          </Button>
          <div
            v-else
            class="w-5"
          />
        </div>
        <div
          v-if="category.image"
          class="flex-shrink-0 h-10 w-10 mr-3"
        >
          <img
            :src="category.image"
            :alt="category.name"
            class="h-10 w-10 rounded-full object-cover"
          >
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center">
            <p class="text-sm font-medium text-foreground">
              {{ category.name }}
            </p>
            <Badge
              class="ml-2"
              :variant="category.is_active ? 'success' : 'secondary'"
            >
              {{ category.is_active ? 'Active' : 'Inactive' }}
            </Badge>
          </div>
          <p
            v-if="category.description"
            class="text-sm text-muted-foreground truncate"
          >
            {{ category.description }}
          </p>
          <p class="text-xs text-muted-foreground mt-1">
            {{ category.slug }}
          </p>
        </div>
      </div>
      <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
        <Button
          variant="ghost"
          size="sm"
          class="h-8 px-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-100"
          @click="$emit('edit', category)"
        >
          Edit
        </Button>
        <Button
          variant="ghost"
          size="sm"
          class="h-8 px-2 text-destructive hover:text-destructive hover:bg-destructive/10"
          @click="$emit('delete', category)"
        >
          Delete
        </Button>
      </div>
    </div>
    <div
      v-if="expanded && hasChildren"
      class="pl-12 bg-muted"
    >
      <CategoryTreeItem
        v-for="child in category.children"
        :key="child.id"
        :category="child"
        :all-categories="allCategories"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
        @move="$emit('move', $event)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import {
  ChevronRight,
} from 'lucide-vue-next';
import { Button, Badge } from '@/shared/components/ui';

interface Category {
    id: string | string;
    name: string;
    slug: string;
    image?: string;
    description?: string;
    is_active: boolean;
    parent_id?: string | null;
    children?: Category[];
    [key: string]: unknown;
}

const props = defineProps<{
    category: Category;
    allCategories?: Category[];
}>();

defineEmits<{
    (e: 'edit', category: Category): void;
    (e: 'delete', category: Category): void;
    (e: 'move', category: Category): void;
}>();

const expanded = ref(true);

const hasChildren = computed(() => {
    return props.category.children && props.category.children.length > 0;
});
</script>


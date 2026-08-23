<template>
  <div 
    class="border border-border rounded-lg bg-card relative shadow-sm transition-shadow"
    :class="[
      isSelected ? 'ring-2 ring-primary border-primary' : 'hover:shadow-md',
      typeColorClass
    ]"
  >
    <!-- Item Header -->
    <div 
      class="flex items-center justify-between p-3 rounded-lg transition-colors cursor-pointer"
      :class="isSelected ? 'bg-primary/5' : 'hover:bg-muted/30'"
      @click="handleSelect"
    >
      <div class="flex items-center gap-3 flex-1 overflow-hidden">
        <!-- Drag Handle -->
        <div class="drag-handle cursor-move p-1 rounded hover:bg-muted/50 transition-colors">
          <GripVertical class="w-4 h-4 text-muted-foreground" />
        </div>
                
        <!-- Icon -->
        <div class="p-1.5 bg-muted/50 rounded">
          <component
            :is="iconComponent"
            class="w-4 h-4"
            :class="iconColorClass"
          />
        </div>
                
        <!-- Title & Badges -->
        <div class="flex flex-col gap-0.5 overflow-hidden flex-1">
          <div class="font-medium text-sm truncate flex items-center gap-2">
            {{ item.title || 'Untitled' }}
            <Badge
              v-if="item.type !== 'custom'"
              variant="outline"
              class="text-[10px] px-1.5 py-0"
            >
              {{ typeLabel }}
            </Badge>
            <Badge
              v-if="item.badge"
              :variant="(item.badge_color as any) || 'default'"
              class="text-[10px]"
            >
              {{ item.badge }}
            </Badge>
          </div>
          <div
            v-if="item.url"
            class="text-[10px] text-muted-foreground truncate"
          >
            {{ item.url }}
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-1">
        <!-- Children Count -->
        <Badge 
          v-if="item.children && item.children.length > 0" 
          variant="secondary" 
          class="text-[10px] mr-1"
        >
          {{ item.children.length }}
        </Badge>

        <!-- Toggle Children -->
        <Button 
          v-if="item.children && item.children.length > 0"
          size="icon" 
          variant="ghost" 
          class="h-8 w-8 p-0"
          @click.stop="isExpanded = !isExpanded"
        >
          <ChevronDown 
            class="w-4 h-4 transition-transform" 
            :class="{ 'rotate-180': isExpanded }" 
          />
        </Button>

        <!-- Duplicate -->
        <Button 
          size="icon" 
          variant="ghost" 
          class="h-8 w-8 p-0"
          @click.stop="handleDuplicate"
        >
          <Copy class="w-4 h-4" />
        </Button>

        <!-- Delete -->
        <Button 
          size="icon" 
          variant="ghost" 
          class="h-8 w-8 p-0 text-destructive hover:text-destructive hover:bg-destructive/10"
          @click.stop="handleDelete"
        >
          <Trash2 class="w-4 h-4" />
        </Button>
      </div>
    </div>

    <!-- Nested Children (Drop Zone) -->
    <div 
      v-show="isExpanded" 
      class="ml-6 pl-2 pr-2 transition-shadow"
      :class="item.children && item.children.length > 0 ? 'border-t border-border/50 py-2 bg-muted/10' : 'py-1'"
    >
      <slot name="children" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useMenuContext } from '@/modules/Layout/composables/useMenu';
import { menuItemRegistry } from '../registry';
import type { MenuItem } from '@/modules/Layout/types/menu';

// UI Components
import Badge from '@/shared/components/ui/Badge.vue';
import Button from '@/shared/components/ui/Button.vue';

import {
  ChevronDown,
  Columns,
  Copy,
  File,
  FileText,
  GripVertical,
  LinkIcon,
  Tag,
  Trash2,
} from 'lucide-vue-next';

const props = defineProps<{
    item: MenuItem;
}>();

const { t } = useI18n();
const menuContext = useMenuContext();
const isExpanded = ref(true);

const itemId = computed(() => props.item.id || props.item._temp_id);
const isSelected = computed(() => menuContext.selectedItemId.value === itemId.value);

const iconComponent = computed(() => {
    switch (props.item.type) {
        case 'page': return FileText;
        case 'post': return File;
        case 'category': return Tag;
        case 'column_group': return Columns;
        default: return LinkIcon;
    }
});

const iconColorClass = computed(() => {
    const definition = menuItemRegistry.get(props.item.type || '');
    const color = definition?.color || 'gray';
    // Using simple color mapping, ensure these classes exist in your CSS/Tailwind
    return `text-${color}-500`;
});

const typeColorClass = computed(() => {
    switch (props.item.type) {
        case 'page': return 'border-l-4 border-l-blue-500';
        case 'post': return 'border-l-4 border-l-orange-500';
        case 'category': return 'border-l-4 border-l-purple-500';
        case 'column_group': return 'border-l-4 border-l-indigo-500';
        default: return 'border-l-4 border-l-emerald-500';
    }
});

const typeLabel = computed(() => {
    const type = props.item.type;
    
    // Handle known types with translations
    if (type === 'custom') return t('layout.menus.form.customLink');
    if (type === 'column_group') return t('layout.menus.form.types.column_group');
    if (['page', 'post', 'category'].includes(type || '')) return t(`layout.menus.form.types.${type}`);

    // Fallback to registry or raw type
    const definition = menuItemRegistry.get(type || '');
    return definition?.label || type;
});

const handleSelect = () => {
    menuContext.selectItem(itemId.value!);
};

const handleDuplicate = () => {
    menuContext.duplicateItem(itemId.value!);
};

const handleDelete = () => {
    menuContext.deleteItem(itemId.value!);
};
</script>

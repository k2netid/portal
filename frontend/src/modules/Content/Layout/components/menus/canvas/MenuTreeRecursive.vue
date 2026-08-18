```
<template>
  <draggable
    :list="items"
    :group="{ name: 'menu' }"
    :item-key="getItemKey"
    class="ml-6 pl-2 pr-2 transition-transform"
    :class="items.length === 0 ? 'min-h-[12px]' : 'space-y-2 min-h-[40px]'"
    handle=".drag-handle"
    ghost-class="ghost"
    @change="handleChange"
  >
    <template #item="{ element }">
      <MenuItemWrapper :item="element">
        <template #children>
          <MenuTreeRecursive 
            v-if="depth < maxDepth"
            :items="element.children || []" 
            :depth="depth + 1"
            :max-depth="maxDepth"
            @update="handleChildUpdate(element, $event)"
          />
        </template>
      </MenuItemWrapper>
    </template>
  </draggable>
</template>

<script setup lang="ts">
import draggable from 'vuedraggable';
import { useMenuContext } from '@/modules/Content/Layout/composables/useMenu';
import MenuItemWrapper from './MenuItemWrapper.vue';
import type { MenuItem } from '@/modules/Content/Layout/types/menu';

const menuContext = useMenuContext();

const props = withDefaults(defineProps<{
    items?: MenuItem[];
    depth?: number;
    maxDepth?: number;
}>(), {
    items: () => [],
    depth: 0,
    maxDepth: 5
});

const emit = defineEmits<{
    (e: 'update', items: MenuItem[]): void;
}>();

const getItemKey = (item: MenuItem) => item.id || item._temp_id || Math.random();

const handleChange = () => {
    emit('update', props.items);
    menuContext.takeSnapshot();
};

const handleChildUpdate = (parent: MenuItem, newChildren: MenuItem[]) => {
    parent.children = newChildren;
    menuContext.takeSnapshot();
};
</script>

<style scoped>
.ghost {
    opacity: 0.5;
    background: hsl(var(--primary) / 0.1);
    border: 2px dashed hsl(var(--primary));
}
</style>

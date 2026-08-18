<template>
  <div class="menu-tree pb-12">
    <MenuTreeRecursive 
      :items="items" 
      :depth="0"
    />
        
    <!-- Empty State Help (only if totally empty) -->
    <div 
      v-if="items.length === 0" 
      class="mt-4 p-8 text-center border-2 border-dashed rounded-lg bg-muted/10 text-muted-foreground"
    >
      <p class="text-sm">
        Drag menu items from the left panel here to start building your menu.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import MenuTreeRecursive from './MenuTreeRecursive.vue';
import type { MenuItem } from '@/modules/Content/Layout/types/menu';

defineProps<{
    items: MenuItem[];
}>();
</script>

<style scoped>
.menu-tree {
    min-height: 200px;
}
</style>
